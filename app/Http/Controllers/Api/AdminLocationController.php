<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            ->leftJoin('parishes as p', 'p.sub_county_id', '=', 't.id')
            ->select([
                't.id', 't.district_id', 't.county_id', 't.name', 't.slug', 't.is_active',
                'd.name as district_name', 'r.name as region_name',
            ])
            ->selectRaw('COUNT(DISTINCT s.id)::integer as schools_count')
            ->selectRaw('COUNT(DISTINCT p.id)::integer as parishes_count')
            ->groupBy(
                't.id', 't.district_id', 't.county_id', 't.name', 't.slug', 't.is_active',
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
            'items.*.region' => ['nullable', 'string', 'max:120'],
            'items.*.district' => ['required', 'string', 'min:2', 'max:120'],
            'items.*.county' => ['required', 'string', 'min:2', 'max:120'],
            'items.*.subcounty' => ['required', 'string', 'min:2', 'max:120'],
            'items.*.parish' => ['nullable', 'string', 'max:120'],
        ]);

        /*
         * Region and District are lookup parents only. This import never creates
         * or changes either level, and it never writes to the schools table.
         * Rows that cannot be matched to one existing District are skipped.
         */
        $resolved = [];
        $skippedRows = [];

        foreach ($data['items'] as $index => $item) {
            $region = $this->cleanName((string) ($item['region'] ?? ''));
            $districtName = $this->cleanName($item['district']);
            $countyName = $this->cleanName($item['county']);
            $subCountyName = $this->cleanName($item['subcounty']);
            $parishName = $this->cleanName((string) ($item['parish'] ?? ''));

            $districtQuery = DB::table('districts as d')
                ->join('regions as r', 'r.id', '=', 'd.region_id')
                ->whereRaw('LOWER(TRIM(d.name)) = ?', [$this->normalized($districtName)]);

            if ($region !== '') {
                $districtQuery->whereRaw('LOWER(TRIM(r.name)) = ?', [$this->normalized($region)]);
            }

            $districts = $districtQuery->get([
                'd.id',
                'd.name',
                'r.name as region_name',
            ]);

            if ($districts->count() !== 1) {
                $scope = $region !== '' ? ' in region "'.$region.'"' : '';
                $reason = $districts->isEmpty()
                    ? 'does not match an existing District'
                    : 'matches more than one District';
                $skippedRows[] = 'Row '.($index + 2).': District "'.$districtName.'"'.$scope.' '.$reason.'.';
                continue;
            }

            $district = $districts->first();
            $resolved[] = [
                'district_id' => (int) $district->id,
                'county' => $countyName,
                'subcounty' => $subCountyName,
                'parish' => $parishName,
            ];
        }

        $counts = [
            'rows_received' => count($data['items']),
            'rows_processed' => count($resolved),
            'rows_skipped' => count($skippedRows),
            'counties_created' => 0,
            'counties_matched' => 0,
            'sub_counties_created' => 0,
            'sub_counties_matched' => 0,
            'parishes_created' => 0,
            'parishes_matched' => 0,
            'skipped_rows' => array_slice($skippedRows, 0, 30),
        ];

        if ($resolved !== []) {
            DB::transaction(function () use ($resolved, &$counts) {
                $districtIds = array_values(array_unique(array_column($resolved, 'district_id')));

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

                // Match County records within each District and insert only missing combinations.
                $countyIdsByKey = [];
                foreach (DB::table('counties')->whereIn('district_id', $districtIds)->get(['id', 'district_id', 'name']) as $county) {
                    $key = (int) $county->district_id.'|'.$this->normalized($county->name);
                    if (! array_key_exists($key, $countyIdsByKey)) {
                        $countyIdsByKey[$key] = (int) $county->id;
                    }
                }

                $usedCountySlugs = array_fill_keys(DB::table('counties')->pluck('slug')->all(), true);
                $newCountyRows = [];
                foreach ($resolved as $item) {
                    $key = $item['district_id'].'|'.$this->normalized($item['county']);
                    if (array_key_exists($key, $countyIdsByKey) || array_key_exists($key, $newCountyRows)) {
                        continue;
                    }

                    $newCountyRows[$key] = [
                        'district_id' => $item['district_id'],
                        'name' => $item['county'],
                        'slug' => $makeSlug($item['county'], $usedCountySlugs),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($newCountyRows !== []) {
                    $insertInChunks('counties', array_values($newCountyRows));
                    foreach (DB::table('counties')->whereIn('district_id', $districtIds)->get(['id', 'district_id', 'name']) as $county) {
                        $key = (int) $county->district_id.'|'.$this->normalized($county->name);
                        if (! array_key_exists($key, $countyIdsByKey)) {
                            $countyIdsByKey[$key] = (int) $county->id;
                        }
                    }
                }

                $requestedCountyKeys = [];
                foreach ($resolved as $item) {
                    $requestedCountyKeys[$item['district_id'].'|'.$this->normalized($item['county'])] = true;
                }
                $counts['counties_created'] = count($newCountyRows);
                $counts['counties_matched'] = max(0, count($requestedCountyKeys) - count($newCountyRows));

                // Match Subcounties by District + County, reusing a legacy unassigned
                // Subcounty only when its name maps to one County in this import.
                $subCountyIdsByKey = [];
                $unassignedSubCountyIdsByName = [];
                foreach (DB::table('sub_counties')->whereIn('district_id', $districtIds)->get(['id', 'district_id', 'county_id', 'name']) as $subCounty) {
                    $districtNameKey = (int) $subCounty->district_id.'|'.$this->normalized($subCounty->name);
                    if ($subCounty->county_id === null) {
                        $unassignedSubCountyIdsByName[$districtNameKey][] = (int) $subCounty->id;
                        continue;
                    }

                    $key = (int) $subCounty->district_id.'|'.(int) $subCounty->county_id.'|'.$this->normalized($subCounty->name);
                    if (! array_key_exists($key, $subCountyIdsByKey)) {
                        $subCountyIdsByKey[$key] = (int) $subCounty->id;
                    }
                }

                $targetCountiesBySubCountyName = [];
                foreach ($resolved as $item) {
                    $countyKey = $item['district_id'].'|'.$this->normalized($item['county']);
                    $countyId = $countyIdsByKey[$countyKey];
                    $nameKey = $item['district_id'].'|'.$this->normalized($item['subcounty']);
                    $targetCountiesBySubCountyName[$nameKey][$countyId] = true;
                }

                $usedSubCountySlugs = array_fill_keys(DB::table('sub_counties')->pluck('slug')->all(), true);
                $newSubCountyRows = [];
                foreach ($resolved as $item) {
                    $countyKey = $item['district_id'].'|'.$this->normalized($item['county']);
                    $countyId = $countyIdsByKey[$countyKey];
                    $name = $this->normalized($item['subcounty']);
                    $key = $item['district_id'].'|'.$countyId.'|'.$name;

                    if (array_key_exists($key, $subCountyIdsByKey) || array_key_exists($key, $newSubCountyRows)) {
                        continue;
                    }

                    $legacyKey = $item['district_id'].'|'.$name;
                    $legacyCandidates = $unassignedSubCountyIdsByName[$legacyKey] ?? [];
                    $targetCounties = array_keys($targetCountiesBySubCountyName[$legacyKey] ?? []);

                    if (count($legacyCandidates) === 1 && count($targetCounties) === 1) {
                        $legacyId = $legacyCandidates[0];
                        DB::table('sub_counties')->where('id', $legacyId)->whereNull('county_id')->update([
                            'county_id' => $countyId,
                            'updated_at' => now(),
                        ]);
                        $subCountyIdsByKey[$key] = $legacyId;
                        unset($unassignedSubCountyIdsByName[$legacyKey]);
                        continue;
                    }

                    $newSubCountyRows[$key] = [
                        'district_id' => $item['district_id'],
                        'county_id' => $countyId,
                        'name' => $this->cleanName($item['subcounty']),
                        'slug' => $makeSlug($item['subcounty'], $usedSubCountySlugs),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($newSubCountyRows !== []) {
                    $insertInChunks('sub_counties', array_values($newSubCountyRows));
                }

                // Reload newly inserted Subcounty IDs for the Parish matching step.
                $subCountyIdsByKey = [];
                foreach (DB::table('sub_counties')->whereIn('district_id', $districtIds)->get(['id', 'district_id', 'county_id', 'name']) as $subCounty) {
                    if ($subCounty->county_id === null) {
                        continue;
                    }
                    $key = (int) $subCounty->district_id.'|'.(int) $subCounty->county_id.'|'.$this->normalized($subCounty->name);
                    if (! array_key_exists($key, $subCountyIdsByKey)) {
                        $subCountyIdsByKey[$key] = (int) $subCounty->id;
                    }
                }

                $requestedSubCountyKeys = [];
                foreach ($resolved as $item) {
                    $countyKey = $item['district_id'].'|'.$this->normalized($item['county']);
                    $key = $item['district_id'].'|'.$countyIdsByKey[$countyKey].'|'.$this->normalized($item['subcounty']);
                    $requestedSubCountyKeys[$key] = true;
                }
                $counts['sub_counties_created'] = count($newSubCountyRows);
                $counts['sub_counties_matched'] = max(0, count($requestedSubCountyKeys) - count($newSubCountyRows));

                // Match Parish records within the resolved Subcounty.
                $subCountyIds = array_values(array_unique(array_map(
                    fn (array $item) => $subCountyIdsByKey[
                        $item['district_id'].'|'.$countyIdsByKey[$item['district_id'].'|'.$this->normalized($item['county'])].'|'.$this->normalized($item['subcounty'])
                    ],
                    $resolved
                )));

                $parishIdsByKey = [];
                foreach (DB::table('parishes')->whereIn('sub_county_id', $subCountyIds)->get(['id', 'sub_county_id', 'name']) as $parish) {
                    $key = (int) $parish->sub_county_id.'|'.$this->normalized($parish->name);
                    if (! array_key_exists($key, $parishIdsByKey)) {
                        $parishIdsByKey[$key] = (int) $parish->id;
                    }
                }

                $usedParishSlugs = array_fill_keys(DB::table('parishes')->pluck('slug')->all(), true);
                $newParishRows = [];
                $requestedParishKeys = [];
                foreach ($resolved as $item) {
                    if ($item['parish'] === '') {
                        continue;
                    }

                    $countyKey = $item['district_id'].'|'.$this->normalized($item['county']);
                    $subCountyKey = $item['district_id'].'|'.$countyIdsByKey[$countyKey].'|'.$this->normalized($item['subcounty']);
                    $subCountyId = $subCountyIdsByKey[$subCountyKey];
                    $key = $subCountyId.'|'.$this->normalized($item['parish']);
                    $requestedParishKeys[$key] = true;

                    if (array_key_exists($key, $parishIdsByKey) || array_key_exists($key, $newParishRows)) {
                        continue;
                    }

                    $newParishRows[$key] = [
                        'sub_county_id' => $subCountyId,
                        'name' => $item['parish'],
                        'slug' => $makeSlug($item['parish'], $usedParishSlugs),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($newParishRows !== []) {
                    $insertInChunks('parishes', array_values($newParishRows));
                }

                $counts['parishes_created'] = count($newParishRows);
                $counts['parishes_matched'] = max(0, count($requestedParishKeys) - count($newParishRows));
            });
        }

        return response()->json([
            'message' => 'Location import completed.',
            'data' => $counts,
        ]);
    }

    public function reviewSuggestion(Request $request, int $suggestionId): JsonResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate([
            'action' => ['required', 'in:merge,reject'],
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

        $town = DB::table('sub_counties')
            ->where('id', $data['target_town_id'])
            ->where('district_id', $suggestion->district_id)
            ->whereNotNull('county_id')
            ->first();
        abort_unless($town, 422, 'Choose an approved subcounty under a county in the same district.');

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
                'status' => 'merged',
                'target_sub_county_id' => $town->id,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'School locations merged into the selected subcounty.',
        ]);
    }
}
