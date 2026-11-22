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
            'counties' => 'counties',
            'towns', 'sub_counties' => 'sub_counties',
            'parishes' => 'parishes',
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

        // Aggregate counts before joining so the manager loads with a fixed
        // number of database queries instead of issuing count queries per row.
        $regionDistrictCounts = DB::table('districts')
            ->select('region_id')
            ->selectRaw('COUNT(*)::integer as districts_count')
            ->groupBy('region_id');

        $regionSchoolCounts = DB::table('schools as s')
            ->join('districts as d', 'd.id', '=', 's.district_id')
            ->select('d.region_id')
            ->selectRaw('COUNT(*)::integer as schools_count')
            ->groupBy('d.region_id');

        $regions = DB::table('regions as r')
            ->leftJoinSub($regionDistrictCounts, 'district_totals', function ($join) {
                $join->on('district_totals.region_id', '=', 'r.id');
            })
            ->leftJoinSub($regionSchoolCounts, 'school_totals', function ($join) {
                $join->on('school_totals.region_id', '=', 'r.id');
            })
            ->orderBy('r.name')
            ->select([
                'r.id', 'r.name', 'r.slug', 'r.is_active',
            ])
            ->selectRaw('COALESCE(district_totals.districts_count, 0)::integer as districts_count')
            ->selectRaw('COALESCE(school_totals.schools_count, 0)::integer as schools_count')
            ->get()
            ->map(function ($region) {
                $region->districts_count = (int) ($region->districts_count ?? 0);
                $region->schools_count = (int) ($region->schools_count ?? 0);
                return $region;
            });

        $districtSchoolCounts = DB::table('schools')
            ->select('district_id')
            ->selectRaw('COUNT(*)::integer as schools_count')
            ->whereNotNull('district_id')
            ->groupBy('district_id');

        $districtTownCounts = DB::table('sub_counties')
            ->select('district_id')
            ->selectRaw('COUNT(*)::integer as towns_count')
            ->groupBy('district_id');

        $districts = DB::table('districts as d')
            ->join('regions as r', 'r.id', '=', 'd.region_id')
            ->leftJoinSub($districtSchoolCounts, 'school_totals', function ($join) {
                $join->on('school_totals.district_id', '=', 'd.id');
            })
            ->leftJoinSub($districtTownCounts, 'town_totals', function ($join) {
                $join->on('town_totals.district_id', '=', 'd.id');
            })
            ->orderBy('r.name')
            ->orderBy('d.name')
            ->select([
                'd.id', 'd.region_id', 'd.name', 'd.slug', 'd.school_count',
                'd.is_featured', 'd.is_active', 'r.name as region_name',
            ])
            ->selectRaw('COALESCE(school_totals.schools_count, 0)::integer as schools_count')
            ->selectRaw('COALESCE(town_totals.towns_count, 0)::integer as towns_count')
            ->get()
            ->map(function ($district) {
                $district->schools_count = (int) ($district->schools_count ?? 0);
                $district->towns_count = (int) ($district->towns_count ?? 0);
                return $district;
            });

        $towns = DB::table('sub_counties as t')
            ->join('districts as d', 'd.id', '=', 't.district_id')
            ->join('regions as r', 'r.id', '=', 'd.region_id')
            ->leftJoin('schools as s', function ($join) {
                $join->on('s.district_id', '=', 't.district_id')
                    ->where(function ($query) {
                        $query->whereColumn('s.sub_county_id', 't.id')
                            ->orWhereRaw('LOWER(TRIM(s.town)) = LOWER(TRIM(t.name))');
                    });
            })
            ->select([
                't.id', 't.district_id', 't.name', 't.slug', 't.is_active',
                'd.name as district_name', 'r.name as region_name',
            ])
            ->selectRaw('COUNT(DISTINCT s.id)::integer as schools_count')
            ->groupBy(
                't.id', 't.district_id', 't.name', 't.slug', 't.is_active',
                'd.name', 'r.name'
            )
            ->orderBy('r.name')
            ->orderBy('d.name')
            ->orderBy('t.name')
            ->get();

        $counties = DB::table('counties as c')
            ->join('districts as d', 'd.id', '=', 'c.district_id')
            ->join('regions as r', 'r.id', '=', 'd.region_id')
            ->leftJoin('sub_counties as sc', 'sc.county_id', '=', 'c.id')
            ->leftJoin('schools as s', 's.county_id', '=', 'c.id')
            ->select(['c.id', 'c.district_id', 'c.name', 'c.slug', 'c.is_active',
                'd.name as district_name', 'r.name as region_name'])
            ->selectRaw('COUNT(DISTINCT sc.id)::integer as sub_counties_count')
            ->selectRaw('COUNT(DISTINCT s.id)::integer as schools_count')
            ->groupBy('c.id', 'c.district_id', 'c.name', 'c.slug', 'c.is_active', 'd.name', 'r.name')
            ->orderBy('r.name')->orderBy('d.name')->orderBy('c.name')->get();

        $parishes = DB::table('parishes as p')
            ->join('sub_counties as sc', 'sc.id', '=', 'p.sub_county_id')
            ->join('districts as d', 'd.id', '=', 'sc.district_id')
            ->join('regions as r', 'r.id', '=', 'd.region_id')
            ->leftJoin('schools as s', 's.parish_id', '=', 'p.id')
            ->select(['p.id', 'p.sub_county_id', 'p.name', 'p.slug', 'p.is_active',
                'sc.name as sub_county_name', 'sc.district_id', 'd.name as district_name', 'r.name as region_name'])
            ->selectRaw('COUNT(DISTINCT s.id)::integer as schools_count')
            ->groupBy('p.id', 'p.sub_county_id', 'p.name', 'p.slug', 'p.is_active',
                'sc.name', 'sc.district_id', 'd.name', 'r.name')
            ->orderBy('r.name')->orderBy('d.name')->orderBy('sc.name')->orderBy('p.name')->get();

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
                'counties' => $counties,
                'towns' => $towns,
                'sub_counties' => $towns,
                'parishes' => $parishes,
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
                    'counties' => $counties->count(),
                    'towns' => $towns->count(),
                    'sub_counties' => $towns->count(),
                    'parishes' => $parishes->count(),
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
        $rules = ['name' => ['required', 'string', 'min:2', 'max:120']];
        if ($level === 'districts') $rules['region_id'] = ['required', 'integer', 'exists:regions,id'];
        if ($level === 'counties') $rules['district_id'] = ['required', 'integer', 'exists:districts,id'];
        if ($level === 'sub_counties') $rules['county_id'] = ['required', 'integer', 'exists:counties,id'];
        if ($level === 'towns') $rules['district_id'] = ['required', 'integer', 'exists:districts,id'];
        if ($level === 'parishes') $rules['sub_county_id'] = ['required', 'integer', 'exists:sub_counties,id'];

        $data = $request->validate($rules);
        $name = $this->cleanName($data['name']);
        $parentColumn = match ($level) {
            'districts' => 'region_id', 'counties' => 'district_id',
            'sub_counties' => 'county_id', 'towns' => 'district_id',
            'parishes' => 'sub_county_id', default => null,
        };
        $parentId = $parentColumn ? (int) $data[$parentColumn] : null;
        $parentTable = match ($level) {
            'districts' => 'regions', 'counties', 'towns' => 'districts',
            'sub_counties' => 'counties', 'parishes' => 'sub_counties', default => null,
        };
        if ($parentTable && ! DB::table($parentTable)->where('id', $parentId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['name' => ['Choose an active parent location first.']]);
        }
        if ($this->findByName($table, $name, $parentColumn, $parentId)) {
            throw ValidationException::withMessages(['name' => ['That name already exists in this location.']]);
        }

        $extra = [];
        if ($level === 'sub_counties') {
            $extra['district_id'] = (int) DB::table('counties')->where('id', $parentId)->value('district_id');
        } elseif ($level === 'towns') {
            $extra['county_id'] = null;
        }
        $id = DB::table($table)->insertGetId([
            ...($parentColumn ? [$parentColumn => $parentId] : []), ...$extra,
            'name' => $name, 'slug' => $this->slugFor($table, $name),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
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
            $parentColumn = match ($level) {
                'districts' => 'region_id', 'counties' => 'district_id',
                'sub_counties' => 'county_id', 'towns' => 'district_id',
                'parishes' => 'sub_county_id', default => null,
            };
            $parentId = $parentColumn ? (int) $record->{$parentColumn} : null;
            if ($this->findByName($table, $name, $parentColumn, $parentId) &&
                $this->normalized($record->name) !== $this->normalized($name)) {
                throw ValidationException::withMessages(['name' => ['That name already exists in this location.']]);
            }
            $changes['name'] = $name;
            $changes['slug'] = $this->slugFor($table, $name, $id);
        }
        if (array_key_exists('is_active', $data)) $changes['is_active'] = (bool) $data['is_active'];
        $changes['updated_at'] = now();
        DB::transaction(function () use ($table, $id, $changes, $level) {
            DB::table($table)->where('id', $id)->update($changes);
            if (in_array($level, ['towns', 'sub_counties'], true) && array_key_exists('name', $changes)) {
                DB::table('schools')->where('sub_county_id', $id)
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
            $hasChildren = DB::table('counties')->where('district_id', $id)->exists()
                || DB::table('sub_counties')->where('district_id', $id)->exists();
            $hasSuggestions = DB::table('location_suggestions')->where('district_id', $id)->exists();
            abort_if($hasSchools || $hasChildren || $hasSuggestions, 409, 'This district contains child locations, schools or suggestions. Hide it instead.');
        } elseif ($level === 'counties') {
            abort_if(DB::table('sub_counties')->where('county_id', $id)->exists()
                || DB::table('schools')->where('county_id', $id)->exists(), 409, 'Schools or subcounties use this county. Hide it instead.');
        } elseif (in_array($level, ['towns', 'sub_counties'], true)) {
            abort_if(DB::table('parishes')->where('sub_county_id', $id)->exists()
                || DB::table('schools')->where('sub_county_id', $id)->exists(), 409, 'Schools or parishes use this subcounty. Hide it instead.');
        } elseif ($level === 'parishes') {
            abort_if(DB::table('schools')->where('parish_id', $id)->exists(), 409, 'Schools use this parish. Hide it instead.');
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

    public function importSchoolLocations(Request $request): JsonResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:2000'],
            'items.*.school_name' => ['required', 'string', 'min:2', 'max:255'],
            'items.*.district' => ['required', 'string', 'min:2', 'max:120'],
            'items.*.county' => ['required', 'string', 'min:2', 'max:120'],
            'items.*.subcounty' => ['required', 'string', 'min:2', 'max:120'],
            'items.*.parish' => ['nullable', 'string', 'max:120'],
        ]);

        $resolved = [];
        $errors = [];
        foreach ($data['items'] as $index => $item) {
            $districts = DB::table('districts')
                ->whereRaw('LOWER(TRIM(name)) = ?', [$this->normalized($item['district'])])
                ->get(['id', 'name']);
            if ($districts->count() !== 1) {
                $errors[] = 'Row '.($index + 2).': district "'.$item['district'].'" '.($districts->isEmpty() ? 'does not exist.' : 'is ambiguous across regions.');
                continue;
            }
            $district = $districts->first();
            $school = DB::table('schools')->where('district_id', $district->id)
                ->whereRaw('LOWER(TRIM(name)) = ?', [$this->normalized($item['school_name'])])
                ->first(['id']);
            if (! $school) {
                $errors[] = 'Row '.($index + 2).': school "'.$item['school_name'].'" was not found in '.$district->name.'.';
                continue;
            }
            $resolved[] = [
                'school_id' => (int) $school->id, 'district_id' => (int) $district->id,
                'county' => $this->cleanName($item['county']),
                'subcounty' => $this->cleanName($item['subcounty']),
                'parish' => $this->cleanName((string) ($item['parish'] ?? '')),
            ];
        }
        if ($errors !== []) throw ValidationException::withMessages(['file' => $errors]);

        $counts = ['schools_updated' => 0, 'counties_created' => 0, 'sub_counties_created' => 0, 'parishes_created' => 0];
        DB::transaction(function () use ($resolved, &$counts) {
            foreach ($resolved as $item) {
                $county = DB::table('counties')->where('district_id', $item['district_id'])
                    ->whereRaw('LOWER(TRIM(name)) = ?', [$this->normalized($item['county'])])->first();
                if (! $county) {
                    $id = DB::table('counties')->insertGetId([
                        'district_id' => $item['district_id'], 'name' => $item['county'],
                        'slug' => $this->slugFor('counties', $item['county']),
                        'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $county = DB::table('counties')->where('id', $id)->first();
                    $counts['counties_created']++;
                }

                $sub = DB::table('sub_counties')->where('district_id', $item['district_id'])
                    ->whereRaw('LOWER(TRIM(name)) = ?', [$this->normalized($item['subcounty'])])
                    ->where(fn ($q) => $q->whereNull('county_id')->orWhere('county_id', $county->id))->first();
                if (! $sub) {
                    $id = DB::table('sub_counties')->insertGetId([
                        'district_id' => $item['district_id'], 'county_id' => $county->id,
                        'name' => $item['subcounty'], 'slug' => $this->slugFor('sub_counties', $item['subcounty']),
                        'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $sub = DB::table('sub_counties')->where('id', $id)->first();
                    $counts['sub_counties_created']++;
                } elseif (! $sub->county_id) {
                    DB::table('sub_counties')->where('id', $sub->id)->update(['county_id' => $county->id, 'updated_at' => now()]);
                }

                $parishId = null;
                if ($item['parish'] !== '') {
                    $parish = DB::table('parishes')->where('sub_county_id', $sub->id)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [$this->normalized($item['parish'])])->first();
                    if (! $parish) {
                        $parishId = DB::table('parishes')->insertGetId([
                            'sub_county_id' => $sub->id, 'name' => $item['parish'],
                            'slug' => $this->slugFor('parishes', $item['parish']),
                            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                        $counts['parishes_created']++;
                    } else $parishId = (int) $parish->id;
                }

                DB::table('schools')->where('id', $item['school_id'])->update([
                    'county_id' => $county->id, 'sub_county_id' => $sub->id,
                    'parish_id' => $parishId, 'town' => $sub->name, 'updated_at' => now(),
                ]);
                $counts['schools_updated']++;
            }
        });
        return response()->json(['message' => $counts['schools_updated'].' school location record(s) updated.', 'data' => $counts]);
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
