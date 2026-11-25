<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SchoolDirectoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'integer', 'exists:school_types,id'],
            'district' => ['nullable', 'array'],
            'district.*' => ['integer', 'exists:districts,id'],
            'region' => ['nullable', 'integer', 'exists:regions,id'],
            'fee_min' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'fee_max' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'curriculum' => ['nullable', 'array'],
            'curriculum.*' => ['integer', 'exists:curricula,id'],
            'boarding' => ['nullable', 'array'],
            'boarding.*' => ['string', 'max:40'],
            'admissions_open' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:newest,featured,name,rating,fee_low,fee_high'],
            'view' => ['nullable', 'in:list,grid'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));
        $typeId = $filters['type'] ?? null;
        $districtIds = array_values(array_unique(array_map('intval', $filters['district'] ?? [])));
        $curriculumIds = array_values(array_unique(array_map('intval', $filters['curriculum'] ?? [])));
        $boardingTypes = array_values(array_unique($filters['boarding'] ?? []));
        $regionId = $filters['region'] ?? null;
        $feeMin = isset($filters['fee_min']) ? (float) $filters['fee_min'] : null;
        $feeMax = isset($filters['fee_max']) ? (float) $filters['fee_max'] : null;
        $admissionsOpen = (bool) ($filters['admissions_open'] ?? false);
        $sort = $filters['sort'] ?? 'newest';
        $viewMode = $filters['view'] ?? 'grid';

        $query = School::query()
            ->publiclyVisible()
            ->with([
                'district.region',
                'county',
                'subCounty',
                'parish',
                'type',
                'curricula',
                'media',
            ]);

        if ($search !== '') {
            $term = '%'.mb_strtolower($search).'%';
            $query->where(function (Builder $searchQuery) use ($term) {
                $searchQuery
                    ->whereRaw('LOWER(schools.name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(schools.tagline, \'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(schools.description, \'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(schools.town, \'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(schools.address, \'\')) LIKE ?', [$term])
                    ->orWhereHas('district', fn ($districtQuery) => $districtQuery->whereRaw('LOWER(name) LIKE ?', [$term]))
                    ->orWhereHas('parish', fn ($parishQuery) => $parishQuery->whereRaw('LOWER(name) LIKE ?', [$term]))
                    ->orWhereHas('subCounty', fn ($subCountyQuery) => $subCountyQuery->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($typeId) {
            $query->where('type_id', (int) $typeId);
        }
        if ($districtIds !== []) {
            $query->whereIn('district_id', $districtIds);
        }
        if ($regionId) {
            $query->whereHas('district', fn ($districtQuery) => $districtQuery->where('region_id', (int) $regionId));
        }
        if ($curriculumIds !== []) {
            $query->whereHas('curricula', fn ($curriculumQuery) => $curriculumQuery->whereIn('curricula.id', $curriculumIds));
        }
        if ($boardingTypes !== []) {
            $query->whereIn('boarding_type', $boardingTypes);
        }
        if ($admissionsOpen) {
            $query->where('admissions_open', true);
        }
        if ($feeMin !== null || $feeMax !== null) {
            $query->whereNotNull('schools.fees_from')
                ->whereNotNull('schools.fees_to')
                ->where('schools.fees_from', '>', 0)
                ->whereColumn('schools.fees_to', '>', 'schools.fees_from');

            if ($feeMin !== null) {
                $query->where('schools.fees_to', '>=', $feeMin);
            }

            if ($feeMax !== null) {
                $query->where('schools.fees_from', '<=', $feeMax);
            }
        }

        match ($sort) {
            'featured' => $query->orderByDesc('is_featured')->latest('created_at'),
            'name' => $query->orderBy('name'),
            'rating' => $query->orderByDesc('rating_average')->latest('created_at'),
            'fee_low' => $query->orderByRaw('CASE WHEN schools.fees_from IS NULL OR schools.fees_to IS NULL OR schools.fees_from <= 0 OR schools.fees_to <= schools.fees_from THEN 1 ELSE 0 END')->orderBy('schools.fees_from'),
            'fee_high' => $query->orderByRaw('CASE WHEN schools.fees_from IS NULL OR schools.fees_to IS NULL OR schools.fees_from <= 0 OR schools.fees_to <= schools.fees_from THEN 1 ELSE 0 END')->orderByDesc('schools.fees_from'),
            default => $query->latest('created_at'),
        };

        $schools = $query->paginate(12)->appends($request->except('page'));

        $schoolTypes = SchoolType::query()
            ->where('is_active', true)
            ->withCount(['schools as public_listing_count' => fn ($schoolsQuery) => $schoolsQuery->publiclyVisible()])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $districts = District::query()
            ->where('is_active', true)
            ->with('region')
            ->withCount(['schools as public_listing_count' => fn ($schoolsQuery) => $schoolsQuery->publiclyVisible()])
            ->orderByDesc('public_listing_count')
            ->orderBy('name')
            ->get();

        $regions = Region::query()
            ->where('is_active', true)
            ->withCount(['districts as public_listing_count' => fn ($districtQuery) => $districtQuery->whereHas('schools', fn ($schoolsQuery) => $schoolsQuery->publiclyVisible())])
            ->orderBy('name')
            ->get();

        $curricula = Curriculum::query()
            ->where('is_active', true)
            ->withCount(['schools as public_listing_count' => fn ($schoolsQuery) => $schoolsQuery->publiclyVisible()])
            ->orderBy('name')
            ->get();

        $boardingOptions = School::query()
            ->publiclyVisible()
            ->whereNotNull('boarding_type')
            ->where('boarding_type', '<>', '')
            ->distinct()
            ->orderBy('boarding_type')
            ->pluck('boarding_type');

        $savedSchoolIds = [];
        $savedSchoolCount = 0;
        if ($request->user()) {
            $savedSchoolCount = $request->user()->savedSchools()->count();
            $pageSchoolIds = $schools->getCollection()->pluck('id');
            $savedSchoolIds = $request->user()->savedSchools()
                ->whereIn('schools.id', $pageSchoolIds)
                ->pluck('schools.id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $brandSettings = Setting::query()
            ->whereIn('key', ['site_logo', 'site_horizontal_logo', 'site_mobile_logo'])
            ->pluck('value', 'key');
        $defaultHeaderLogo = 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/062d6754-5266-46a5-9f83-1a97bc393a19.png';
        $headerLogoUrl = $brandSettings['site_horizontal_logo'] ?: $defaultHeaderLogo;
        $mobileLogoUrl = $brandSettings['site_mobile_logo'] ?: $headerLogoUrl;

        return response()->view('livewire.schools.school-listing', [
            'schools' => $schools,
            'schoolTypes' => $schoolTypes,
            'districts' => $districts,
            'regions' => $regions,
            'curricula' => $curricula,
            'boardingOptions' => $boardingOptions,
            'allSchoolCount' => School::query()->publiclyVisible()->count(),
            'savedSchoolIds' => $savedSchoolIds,
            'savedSchoolCount' => $savedSchoolCount,
            'headerLogoUrl' => $headerLogoUrl,
            'mobileLogoUrl' => $mobileLogoUrl,
            'selectedDistrictIds' => $districtIds,
            'selectedCurriculumIds' => $curriculumIds,
            'selectedBoardingTypes' => $boardingTypes,
            'search' => $search,
            'typeId' => $typeId,
            'regionId' => $regionId,
            'feeMin' => $feeMin,
            'feeMax' => $feeMax,
            'admissionsOpen' => $admissionsOpen,
            'sort' => $sort,
            'viewMode' => $viewMode,
        ])->header('Cache-Control', 'private, no-store');
    }
}
