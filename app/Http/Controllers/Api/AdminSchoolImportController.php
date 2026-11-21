<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminSchoolImportController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $this->authorizeImport($request);
        $file = $this->validatedFile($request);
        $rows = $this->prepareRows($file);

        return response()->json(['data' => $this->summarise($rows)]);
    }

    public function import(Request $request): JsonResponse
    {
        $this->authorizeImport($request);
        $file = $this->validatedFile($request);
        $rows = $this->prepareRows($file);
        $summary = $this->summarise($rows);

        if ($summary['total_rows'] === 0) {
            return response()->json(['message' => 'No school records were found below the column headings.'], 422);
        }

        if ($summary['error_rows'] > 0) {
            return response()->json([
                'message' => 'Some rows need attention. Correct the errors and upload the file again.',
                'data' => $summary,
            ], 422);
        }

        $created = DB::transaction(function () use ($rows): int {
            $count = 0;

            foreach ($rows as $row) {
                if (! $row['ready']) {
                    continue;
                }

                $school = School::query()->create($row['data']);

                if ($row['curriculum_ids'] !== []) {
                    $school->curricula()->sync($row['curriculum_ids']);
                }

                $count++;
            }

            return $count;
        });

        return response()->json([
            'message' => $created.' school record'.($created === 1 ? '' : 's').' imported successfully. They have been sent for review.',
            'data' => [
                'imported_rows' => $created,
                'file_name' => $file->getClientOriginalName(),
            ],
        ]);
    }

    private function authorizeImport(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->hasRole('admin') || $user->can('admin.schools.edit_any')),
            403,
            'Your administrator role does not have permission to import school records.'
        );
    }

    private function validatedFile(Request $request): UploadedFile
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:csv,txt,xlsx,xls'],
        ]);

        return $validated['file'];
    }

    /**
     * Read a branded CSV or Excel template, locate its actual heading row,
     * resolve names against the existing reference lists and validate every
     * record before anything is written to the database.
     *
     * @return array<int, array{row_number:int, school_name:string, region:string, district:string, school_type:string, errors:array<int,string>, ready:bool, data:array<string,mixed>, curriculum_ids:array<int,int>}>
     */
    private function prepareRows(UploadedFile $file): array
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $readerType = match ($extension) {
                'csv', 'txt' => 'Csv',
                'xls' => 'Xls',
                'xlsx' => 'Xlsx',
                default => throw new \\UnexpectedValueException('Unsupported import file type.'),
            };
            $reader = IOFactory::createReader($readerType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheetRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $spreadsheet->disconnectWorksheets();
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'file' => 'This file could not be read. Upload a valid CSV or Excel workbook.',
            ]);
        }

        $aliases = [
            'school' => 'school_name',
            'name' => 'school_name',
            'school_type_name' => 'school_type',
            'type' => 'school_type',
            'region_name' => 'region',
            'district_name' => 'district',
            'town_area' => 'town',
            'approx_learners' => 'student_count_approx',
            'approximate_learners' => 'student_count_approx',
            'approximate_students' => 'student_count_approx',
            'curriculum' => 'curricula',
            'curriculums' => 'curricula',
            'curriculum_s' => 'curricula',
            'phone_number' => 'phone',
            'email_address' => 'email',
            'whatsapp_number' => 'whatsapp',
            'founded' => 'founded_year',
            'registration_no' => 'registration_number',
        ];

        $headerIndex = null;
        $headerMap = [];

        foreach (array_slice($sheetRows, 0, 30, true) as $index => $cells) {
            $candidate = [];

            foreach ($cells as $column => $cell) {
                $header = Str::slug(trim((string) $cell), '_');

                if ($header === '') {
                    continue;
                }

                $header = $aliases[$header] ?? $header;
                $candidate[$header] = (int) $column;
            }

            if (count(array_intersect(['school_name', 'school_type', 'region', 'district'], array_keys($candidate))) === 4) {
                $headerIndex = (int) $index;
                $headerMap = $candidate;
                break;
            }
        }

        if ($headerIndex === null) {
            throw ValidationException::withMessages([
                'file' => 'The template headings were not found. Use the FindUrSch school import template and keep the required headings unchanged.',
            ]);
        }

        $prepared = [];
        $seenInFile = [];

        foreach (array_slice($sheetRows, $headerIndex + 1, null, true) as $index => $cells) {
            $values = [];

            foreach ($headerMap as $key => $column) {
                $value = $cells[$column] ?? null;
                $values[$key] = is_scalar($value) ? trim((string) $value) : '';
            }

            $name = trim((string) ($values['school_name'] ?? ''));

            // The branded template includes one clearly labelled example row.
            if (Str::startsWith(Str::lower($name), 'example row -')) {
                continue;
            }

            if (count(array_filter($values, fn ($value) => $value !== '')) === 0) {
                continue;
            }

            $rowNumber = (int) $index + 1;
            $errors = [];
            $regionName = trim((string) ($values['region'] ?? ''));
            $districtName = trim((string) ($values['district'] ?? ''));
            $typeName = trim((string) ($values['school_type'] ?? ''));

            if ($name === '') {
                $errors[] = 'School Name is required.';
            }

            $region = $regionName !== ''
                ? Region::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($regionName)])->first()
                : null;
            if ($regionName === '' || ! $region) {
                $errors[] = $regionName === '' ? 'Region is required.' : 'Region "'.$regionName.'" does not match an existing region.';
            }

            $district = $region && $districtName !== ''
                ? District::query()
                    ->where('region_id', $region->id)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($districtName)])
                    ->first()
                : null;
            if ($districtName === '' || ! $district) {
                $errors[] = $districtName === ''
                    ? 'District is required.'
                    : 'District "'.$districtName.'" was not found under the selected region.';
            }

            $schoolType = $typeName !== ''
                ? SchoolType::query()
                    ->where(fn ($query) => $query
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($typeName)])
                        ->orWhereRaw('LOWER(slug) = ?', [Str::slug($typeName)]))
                    ->first()
                : null;
            if ($typeName === '' || ! $schoolType) {
                $errors[] = $typeName === ''
                    ? 'School Type is required.'
                    : 'School Type "'.$typeName.'" does not match an existing school type.';
            }

            $boarding = Str::lower(trim((string) ($values['boarding_type'] ?? 'day'))) ?: 'day';
            $boarding = str_replace([' ', '-'], '_', $boarding);
            $gender = Str::lower(trim((string) ($values['gender_policy'] ?? 'mixed'))) ?: 'mixed';
            $gender = str_replace([' ', '-'], '_', $gender);

            $optional = function (string $key) use ($values): ?string {
                $value = trim((string) ($values[$key] ?? ''));

                return $value === '' ? null : $value;
            };

            $data = [
                'owner_id' => null,
                'type_id' => $schoolType?->id,
                'district_id' => $district?->id,
                'name' => $name,
                'tagline' => $optional('tagline'),
                'description' => $optional('description'),
                'founded_year' => $optional('founded_year'),
                'registration_number' => $optional('registration_number'),
                'town' => $optional('town'),
                'address' => $optional('address'),
                'phone' => $optional('phone'),
                'email' => $optional('email'),
                'website' => $optional('website'),
                'whatsapp' => $optional('whatsapp'),
                'boarding_type' => $boarding,
                'gender_policy' => $gender,
                'student_count_approx' => $optional('student_count_approx'),
                // Bulk-imported listings are always held for administrator review.
                'status' => 'pending',
                'verification_status' => 'pending',
                'is_featured' => false,
                'submitted_at' => now(),
            ];

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'tagline' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:10000'],
                'founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.now()->year],
                'registration_number' => ['nullable', 'string', 'max:60'],
                'town' => ['nullable', 'string', 'max:120'],
                'address' => ['nullable', 'string', 'max:10000'],
                'phone' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'website' => ['nullable', 'url', 'max:255'],
                'whatsapp' => ['nullable', 'string', 'max:30'],
                'boarding_type' => ['required', 'in:boarding,day,mixed'],
                'gender_policy' => ['required', 'in:boys,girls,mixed'],
                'student_count_approx' => ['nullable', 'integer', 'min:0'],
            ]);

            if ($validator->fails()) {
                array_push($errors, ...$validator->errors()->all());
            }

            $curriculumIds = [];
            $curriculumNames = array_filter(array_map('trim', preg_split('/[|;]/', (string) ($values['curricula'] ?? '')) ?: []));

            foreach ($curriculumNames as $curriculumName) {
                $curriculum = Curriculum::query()
                    ->where(fn ($query) => $query
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($curriculumName)])
                        ->orWhereRaw('LOWER(abbreviation) = ?', [mb_strtolower($curriculumName)]))
                    ->first();

                if (! $curriculum) {
                    $errors[] = 'Curriculum "'.$curriculumName.'" does not match an existing curriculum.';
                    continue;
                }

                $curriculumIds[] = (int) $curriculum->id;
            }

            if ($district && $name !== '') {
                $duplicateKey = mb_strtolower($name).'|'.$district->id;
                if (isset($seenInFile[$duplicateKey]) || School::query()
                    ->where('district_id', $district->id)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                    ->exists()) {
                    $errors[] = 'A school with this name already exists in the selected district.';
                }
                $seenInFile[$duplicateKey] = true;
            }

            $prepared[] = [
                'row_number' => $rowNumber,
                'school_name' => $name,
                'region' => $regionName,
                'district' => $districtName,
                'school_type' => $typeName,
                'errors' => array_values(array_unique($errors)),
                'ready' => $errors === [],
                'data' => $data,
                'curriculum_ids' => array_values(array_unique($curriculumIds)),
            ];
        }

        if ($prepared === []) {
            throw ValidationException::withMessages([
                'file' => 'No school records were found below the column headings. Add one school per row and try again.',
            ]);
        }

        return $prepared;
    }

    /**
     * @param  array<int, array{row_number:int, school_name:string, region:string, district:string, school_type:string, errors:array<int,string>, ready:bool}>  $rows
     * @return array<string, mixed>
     */
    private function summarise(array $rows): array
    {
        $ready = count(array_filter($rows, fn ($row) => $row['ready']));
        $errors = count($rows) - $ready;

        return [
            'total_rows' => count($rows),
            'ready_rows' => $ready,
            'error_rows' => $errors,
            'rows' => array_map(fn ($row) => [
                'row_number' => $row['row_number'],
                'school_name' => $row['school_name'],
                'region' => $row['region'],
                'district' => $row['district'],
                'school_type' => $row['school_type'],
                'errors' => $row['errors'],
                'ready' => $row['ready'],
            ], array_slice($rows, 0, 40)),
            'more_rows' => max(0, count($rows) - 40),
        ];
    }
}
