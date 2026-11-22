<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminLocationController extends Controller
{
    private function authorizeView(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasRole('admin') || $user->can('admin.locations.manage') || $user->can('admin.schools.view')),
            403,
            'Your administrator role cannot view location management.'
        );
    }

    private function authorizeManage(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasRole('admin') || $user->can('admin.locations.manage')),
            403,
            'Your administrator role cannot manage school locations.'
        );
    }

    private function cleanName(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    private function normalized(string $value): string
    {
        return mb_strtolower($this->cleanName($value));
    }

    private function tableFor(string $level): string
    {
        return match ($level) {
            'regions' => 'regions',
            'districts' => 'districts',
            'towns' => 'sub_counties',
            default => abort(404, 'Unknown location type.'),
        };
    }

    private function slugFor(string $table, string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'location';
        $candidate = $base;
        $suffix = 2;

        while (DB::table($table)
            ->where('slug', $candidate)
            ->when($exceptId, fn ($query) => $query->where('id', '<>', $exceptId))
            ->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    private function findByName(string $table, string $name, ?string $parentColumn = null, ?int $parentId = null): ?object
    {
        return DB::table($table)
            ->whereRaw('LOWER(name) = ?', [$this->normalized($name)])
            ->when($parentColumn && $parentId, fn ($query) => $query->where($parentColumn, $parentId))
            ->first();
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeView($request);

        $regions = DB::table('regions as r')
            ->orderBy('r.name')
            ->get([
                'r.id', 'r.name', 'r.slug', 'r.is_active',
            ])
            ->map(function ($region) {
                $region->districts_count = DB::table('districts')->where('region_id', $region->id)->count();
                $region->schools_count = DB::table('schools as s')
                    ->join('districts as d', 'd.id', '=', 's.district_id')
                    ->where('d.region_id', $region->id)
                    ->count();
                return $region;
            });

        $districts = DB::table('districts as d')
            ->join('regions as r', 'r.id', '=', 'd.region_id')
            ->orderBy('r.name')
            ->orderBy('d.name')
            ->get([
                'd.id', 'd.region_id', 'd.name', 'd.slug', 'd.school_count',
                'd.is_featured', 'd.is_active', 'r.name as region_name',
            ])
            ->map(function ($district) {
                $district->schools_count = DB::table('schools')->where('district_id', $district->id)->count();
                $district->towns_count = DB::table('sub_counties')->where('district_id', $district->id)->count();
                return $district;
            });

        $towns = DB::table('sub_counties as t')
            ->join('districts as d', 'd.id', '=', 't.district_id')
            ->join('regions as r', 'r.id', '=', 'd.region_id')
            ->orderBy('r.name')
            ->orderBy('d.name')
            ->orderBy('t.name')
            ->get([
                't.id', 't.district_id', 't.name', 't.slug', 't.is_active',
                'd.name as district_name', 'r.name as region_name',
            ])
            ->map(function ($town) {
                $town->schools_count = DB::table('schools')
                    ->where('district_id', $town->district_id)
                    ->where(function ($query) use ($town) {
                        $query->where('sub_county_id', $town->id)
                            ->orWhereRaw('LOWER(TRIM(town)) = ?', [mb_strtolower(trim($town->name))]);
                    })
                    ->count();
                return $town;
            });

        $suggestions = DB::table('location_suggestions as ls')
            ->join('districts as d', 'd.id', '=', 'ls.district_id')
            ->join('regions as r', 'r.id', '=', 'd.region_id')
            ->leftJoin('schools as s', 's.id', '=', 'ls.source_school_id')
            ->leftJoin('users as u', 'u.id', '=', 'ls.suggested_by')
            ->where('ls.status', 'pending')
            ->orderBy('ls.created_at')
            ->get([
                'ls.id', 'ls.district_id', 'ls.suggested_name', 'ls.school_count',
                'ls.source_school_id', 'ls.suggested_by', 'ls.created_at',
                'd.name as district_name', 'r.name as region_name',
                's.name as source_school_name', 'u.name as suggested_by_name',
            ]);

        return response()->json([
            'data' => [
                'regions' => $regions,
                'districts' => $districts,
                'towns' => $towns,
                'suggestions' => $suggestions,
                'settings' => [
                    'allow_custom_town' => filter_var(
                        Setting::get('school_locations_allow_custom_town', '1'),
                        FILTER_VALIDATE_BOOLEAN
                    ),
                ],
                'summary' => [
                    'regions' => $regions->count(),
                    'districts' => $districts->count(),
                    'towns' => $towns->count(),
                    'suggestions_waiting' => $suggestions->count(),
                    'schools_with_location' => DB::table('schools')->whereNotNull('district_id')->count(),
                ],
            ],
        ]);
    }

    public function store(Request $request, string $level): JsonResponse
    {
        $this->authorizeManage($request);
        $table = $this->tableFor($level);

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120'],
        ];
        if ($level === 'districts') {
            $rules['region_id'] = ['required', 'integer', 'exists:regions,id'];
        }
        if ($level === 'towns') {
            $rules['district_id'] = ['required', 'integer', 'exists:districts,id'];
        }
        $data = $request->validate($rules);
        $name = $this->cleanName($data['name']);

        $parentColumn = $level === 'districts' ? 'region_id' : ($level === 'towns' ? 'district_id' : null);
        $parentId = $level === 'districts' ? (int) $data['region_id'] : ($level === 'towns' ? (int) $data['district_id'] : null);

        if ($level === 'districts') {
            abort_unless(DB::table('regions')->where('id', $parentId)->where('is_active', true)->exists(), 422, 'Choose an active region first.');
        }
        if ($level === 'towns') {
            abort_unless(DB::table('districts')->where('id', $parentId)->where('is_active', true)->exists(), 422, 'Choose an active district first.');
        }

        if ($this->findByName($table, $name, $parentColumn, $parentId)) {
            throw ValidationException::withMessages(['name' => ['That name already exists in this location.']]);
        }

        $id = DB::table($table)->insertGetId([
            ...($parentColumn ? [$parentColumn => $parentId] : []),
            'name' => $name,
            'slug' => $this->slugFor($table, $name),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => ucfirst(rtrim($level, 's')).' added.',
            'data' => DB::table($table)->where('id', $id)->first(),
        ], 201);
    }

    public function update(Request $request, string $level, int $id): JsonResponse
    {
        $this->authorizeManage($request);
        $table = $this->tableFor($level);
        $record = DB::table($table)->where('id', $id)->first();
        abort_unless($record, 404, 'Location not found.');

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        abort_unless($data !== [], 422, 'There are no changes to save.');

        $changes = [];
        if (array_key_exists('name', $data)) {
            $name = $this->cleanName($data['name']);
            $parentColumn = $level === 'districts' ? 'region_id' : ($level === 'towns' ? 'district_id' : null);
            $parentId = $parentColumn ? (int) $record->{$parentColumn} : null;
            if ($this->findByName($table, $name, $parentColumn, $parentId) &&
                $this->normalized($record->name) !== $this->normalized($name)) {
                throw ValidationException::withMessages(['name' => ['That name already exists in this location.']]);
            }
            $changes['name'] = $name;
            $changes['slug'] = $this->slugFor($table, $name, $id);
        }
        if (array_key_exists('is_active', $data)) {
            $changes['is_active'] = (bool) $data['is_active'];
        }
        $changes['updated_at'] = now();

        DB::transaction(function () use ($table, $id, $changes, $level, $record) {
            DB::table($table)->where('id', $id)->update($changes);
            if ($level === 'towns' && array_key_exists('name', $changes)) {
                DB::table('schools')
                    ->where('sub_county_id', $id)
                    ->update(['town' => $changes['name'], 'updated_at' => now()]);
            }
        });

        return response()->json([
            'message' => 'Location updated.',
            'data' => DB::table($table)->where('id', $id)->first(),
        ]);
    }

    public function destroy(Request $request, string $level, int $id): JsonResponse
    {
        $this->authorizeManage($request);
        $table = $this->tableFor($level);
        $record = DB::table($table)->where('id', $id)->first();
        abort_unless($record, 404, 'Location not found.');

        if ($level === 'regions') {
            abort_if(DB::table('districts')->where('region_id', $id)->exists(), 409, 'This region still contains districts. Hide it instead of deleting it.');
        } elseif ($level === 'districts') {
            $hasSchools = DB::table('schools')->where('district_id', $id)->exists();
            $hasTowns = DB::table('sub_counties')->where('district_id', $id)->exists();
            $hasSuggestions = DB::table('location_suggestions')->where('district_id', $id)->exists();
            abort_if($hasSchools || $hasTowns || $hasSuggestions, 409, 'This district contains schools, towns or suggestions. Hide it instead of deleting it.');
        } else {
            $hasSchools = DB::table('schools')->where('sub_county_id', $id)
                ->orWhere(function ($query) use ($record) {
                    $query->where('district_id', $record->district_id)
                        ->whereRaw('LOWER(TRIM(town)) = ?', [mb_strtolower(trim($record->name))]);
                })->exists();
            abort_if($hasSchools, 409, 'Schools are using this town or area. Hide it instead of deleting it.');
        }

        DB::table($table)->where('id', $id)->delete();

        return response()->json(['message' => 'Location deleted.']);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate(['allow_custom_town' => ['required', 'boolean']]);

        Setting::updateOrCreate(
            ['key' => 'school_locations_allow_custom_town'],
            [
                'value' => $data['allow_custom_town'] ? '1' : '0',
                'type' => 'boolean',
                'group' => 'locations',
                'label' => 'Allow school owners to suggest unlisted towns or areas',
            ]
        );

        return response()->json(['message' => 'Location settings saved.']);
    }

    public function import(Request $request): JsonResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:2000'],
            'items.*.region' => ['required', 'string', 'min:2', 'max:120'],
            'items.*.district' => ['required', 'string', 'min:2', 'max:120'],
            'items.*.town' => ['nullable', 'string', 'max:120'],
        ]);

        $counts = [
            'regions_created' => 0, 'districts_created' => 0, 'towns_created' => 0,
            'regions_existing' => 0, 'districts_existing' => 0, 'towns_existing' => 0,
        ];

        DB::transaction(function () use ($data, &$counts) {
            $items = collect($data['items'])->map(fn (array $item) => [
                'region' => $this->cleanName($item['region']),
                'district' => $this->cleanName($item['district']),
                'town' => $this->cleanName((string) ($item['town'] ?? '')),
            ])->all();

            $makeSlug = static function (string $name, array &$usedSlugs): string {
                $base = Str::slug($name);
                $base = $base !== '' ? $base : 'location';
                $candidate = $base;
                $suffix = 2;

                while (isset($usedSlugs[$candidate])) {
                    $candidate = $base.'-'.$suffix++;
                }

                $usedSlugs[$candidate] = true;

                return $candidate;
            };

            $insertInChunks = static function (string $table, array $rows): void {
                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table($table)->insert($chunk);
                }
            };

            /*
             * Resolve and insert regions in batches. The map is updated as soon
             * as a new name is queued so repeated rows count as existing without
             * creating duplicate records.
             */
            $regions = DB::table('regions')->get(['id', 'name', 'slug']);
            $regionIdsByName = [];
            $usedRegionSlugs = [];

            foreach ($regions as $region) {
                $key = $this->normalized($region->name);
                if (! array_key_exists($key, $regionIdsByName)) {
                    $regionIdsByName[$key] = (int) $region->id;
                }
                $usedRegionSlugs[$region->slug] = true;
            }

            $newRegions = [];
            foreach ($items as $item) {
                $key = $this->normalized($item['region']);
                if (array_key_exists($key, $regionIdsByName)) {
                    $counts['regions_existing']++;
                    continue;
                }

                $regionIdsByName[$key] = null;
                $newRegions[] = [
                    'name' => $item['region'],
                    'slug' => $makeSlug($item['region'], $usedRegionSlugs),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $counts['regions_created']++;
            }

            if ($newRegions !== []) {
                $insertInChunks('regions', $newRegions);
                $regions = DB::table('regions')->get(['id', 'name', 'slug']);
                $regionIdsByName = [];
                foreach ($regions as $region) {
                    $key = $this->normalized($region->name);
                    if (! array_key_exists($key, $regionIdsByName)) {
                        $regionIdsByName[$key] = (int) $region->id;
                    }
                }
            }

            $regionIds = array_values(array_unique(array_map(
                fn (array $item) => $regionIdsByName[$this->normalized($item['region'])],
                $items
            )));

            /*
             * Resolve districts using their parent region, then insert only the
             * missing combinations. Slugs remain unique across the whole table.
             */
            $districts = DB::table('districts')
                ->whereIn('region_id', $regionIds)
                ->get(['id', 'region_id', 'name', 'slug']);
            $districtIdsByKey = [];
            // Slugs are unique across the full districts table, not only this import's regions.
            $usedDistrictSlugs = array_fill_keys(DB::table('districts')->pluck('slug')->all(), true);

            foreach ($districts as $district) {
                $key = (int) $district->region_id.'|'.$this->normalized($district->name);
                if (! array_key_exists($key, $districtIdsByKey)) {
                    $districtIdsByKey[$key] = (int) $district->id;
                }
                $usedDistrictSlugs[$district->slug] = true;
            }

            $newDistricts = [];
            foreach ($items as $item) {
                $regionId = $regionIdsByName[$this->normalized($item['region'])];
                $key = $regionId.'|'.$this->normalized($item['district']);

                if (array_key_exists($key, $districtIdsByKey)) {
                    $counts['districts_existing']++;
                    continue;
                }

                $districtIdsByKey[$key] = null;
                $newDistricts[] = [
                    'region_id' => $regionId,
                    'name' => $item['district'],
                    'slug' => $makeSlug($item['district'], $usedDistrictSlugs),
                    'school_count' => 0,
                    'is_featured' => false,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $counts['districts_created']++;
            }

            if ($newDistricts !== []) {
                $insertInChunks('districts', $newDistricts);
                $districts = DB::table('districts')
                    ->whereIn('region_id', $regionIds)
                    ->get(['id', 'region_id', 'name', 'slug']);
                $districtIdsByKey = [];
                foreach ($districts as $district) {
                    $key = (int) $district->region_id.'|'.$this->normalized($district->name);
                    if (! array_key_exists($key, $districtIdsByKey)) {
                        $districtIdsByKey[$key] = (int) $district->id;
                    }
                }
            }

            $districtIds = array_values(array_unique(array_map(
                fn (array $item) => $districtIdsByKey[
                    $regionIdsByName[$this->normalized($item['region'])].'|'.$this->normalized($item['district'])
                ],
                $items
            )));

            /*
             * Towns and areas are optional. Resolve them by district and insert
             * missing names in batches, keeping existing school assignments intact.
             */
            $towns = DB::table('sub_counties')
                ->whereIn('district_id', $districtIds)
                ->get(['id', 'district_id', 'name', 'slug']);
            $townExistsByKey = [];
            // Slugs are unique across the full towns table, not only this import's districts.
            $usedTownSlugs = array_fill_keys(DB::table('sub_counties')->pluck('slug')->all(), true);

            foreach ($towns as $town) {
                $key = (int) $town->district_id.'|'.$this->normalized($town->name);
                $townExistsByKey[$key] = true;
                $usedTownSlugs[$town->slug] = true;
            }

            $newTowns = [];
            foreach ($items as $item) {
                if ($item['town'] === '') {
                    continue;
                }

                $districtKey = $regionIdsByName[$this->normalized($item['region'])]
                    .'|'.$this->normalized($item['district']);
                $districtId = $districtIdsByKey[$districtKey];
                $key = $districtId.'|'.$this->normalized($item['town']);

                if (isset($townExistsByKey[$key])) {
                    $counts['towns_existing']++;
                    continue;
                }

                $townExistsByKey[$key] = true;
                $newTowns[] = [
                    'district_id' => $districtId,
                    'name' => $item['town'],
                    'slug' => $makeSlug($item['town'], $usedTownSlugs),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $counts['towns_created']++;
            }

            if ($newTowns !== []) {
                $insertInChunks('sub_counties', $newTowns);
            }
        });

        return response()->json([
            'message' => 'Location import completed.',
            'data' => $counts,
        ]);
    }

    public function reviewSuggestion(Request $request, int $suggestionId): JsonResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'action' => ['required', 'in:approve,merge,reject'],
            'target_town_id' => ['required_if:action,merge', 'nullable', 'integer'],
        ]);

        $suggestion = DB::table('location_suggestions')->where('id', $suggestionId)->first();
        abort_unless($suggestion && $suggestion->status === 'pending', 404, 'Pending suggestion not found.');

        if ($data['action'] === 'reject') {
            DB::table('location_suggestions')->where('id', $suggestionId)->update([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Town suggestion rejected.']);
        }

        if ($data['action'] === 'approve') {
            $town = $this->findByName('sub_counties', $suggestion->suggested_name, 'district_id', (int) $suggestion->district_id);
            if (! $town) {
                $id = DB::table('sub_counties')->insertGetId([
                    'district_id' => $suggestion->district_id,
                    'name' => $suggestion->suggested_name,
                    'slug' => $this->slugFor('sub_counties', $suggestion->suggested_name),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $town = DB::table('sub_counties')->where('id', $id)->first();
            } else {
                DB::table('sub_counties')->where('id', $town->id)->update(['is_active' => true, 'updated_at' => now()]);
            }
        } else {
            $town = DB::table('sub_counties')
                ->where('id', $data['target_town_id'])
                ->where('district_id', $suggestion->district_id)
                ->first();
            abort_unless($town, 422, 'Choose a town or area in the same district.');
        }

        DB::transaction(function () use ($suggestion, $town, $request, $suggestionId, $data) {
            DB::table('schools')
                ->where('district_id', $suggestion->district_id)
                ->whereRaw('LOWER(TRIM(town)) = ?', [$suggestion->normalized_name])
                ->update([
                    'sub_county_id' => $town->id,
                    'town' => $town->name,
                    'updated_at' => now(),
                ]);

            DB::table('location_suggestions')->where('id', $suggestionId)->update([
                'status' => $data['action'] === 'approve' ? 'approved' : 'merged',
                'target_sub_county_id' => $town->id,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json([
            'message' => $data['action'] === 'approve' ? 'Town added to the approved list.' : 'School locations merged into the selected town.',
        ]);
    }
}
