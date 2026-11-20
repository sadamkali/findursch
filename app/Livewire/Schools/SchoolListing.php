<?php

namespace App\Livewire\Schools;

use App\Models\Curriculum;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolFee;
use App\Models\SchoolType;
use App\Models\SchoolAdvertisement;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SchoolListing extends Component
{
    use WithPagination;

    #[Url]
    public $district = '';

    #[Url]
    public $region = '';

    #[Url]
    public $type = '';

    #[Url]
    public $curriculum = '';

    #[Url]
    public $boarding = '';

    #[Url]
    public $verified_only = false;

    #[Url(except: 'newest')]
    public $sort = 'newest';

    #[Url]
    public $search = '';

    #[Url]
    public $maxFee = 2000000;

    #[Url]
    public $feeFilterEnabled = false;

    public $viewMode = 'grid';

    public function resetFilters(): void
    {
        $this->reset([
            'district',
            'region',
            'type',
            'curriculum',
            'boarding',
            'verified_only',
            'search',
            'maxFee',
            'feeFilterEnabled',
        ]);

        $this->sort = 'newest';
        $this->maxFee = 2000000;
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    public function applyFeeFilter(): void
    {
        $this->feeFilterEnabled = true;
        $this->resetPage();
    }

    public function clearFeeFilter(): void
    {
        $this->feeFilterEnabled = false;
        $this->maxFee = 2000000;
        $this->resetPage();
    }

    public function selectType(int $typeId): void
    {
        $this->type = (string) $typeId === (string) $this->type ? '' : (string) $typeId;
        $this->resetPage();
    }

    public function selectDistrict(int $districtId): void
    {
        $this->district = (string) $districtId === (string) $this->district ? '' : (string) $districtId;
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'list'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function updated($property): void
    {
        if (in_array($property, [
            'district',
            'region',
            'type',
            'curriculum',
            'boarding',
            'verified_only',
            'sort',
            'search',
            'maxFee',
        ], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $termTuition = fn ($fees) => $fees
            ->where('category', 'tuition')
            ->where('is_active', true)
            ->where('currency', 'UGX')
            ->where('period', 'per_term');

        $query = School::query()
            ->publiclyVisible()
            ->with([
                'district.region',
                'type',
                'curricula',
                'media',
            ])
            ->withMin(['fees as lowest_term_tuition' => $termTuition], 'amount');

        if ($this->district !== '') {
            $query->inDistrict((int) $this->district);
        }

        if ($this->region !== '') {
            $query->whereHas('district', fn ($districtQuery) => $districtQuery->where('region_id', (int) $this->region));
        }

        if ($this->type !== '') {
            $query->byType((int) $this->type);
        }

        if ($this->curriculum !== '') {
            $query->byCurriculum((int) $this->curriculum);
        }

        if ($this->boarding !== '') {
            $query->where('boarding_type', $this->boarding);
        }

        if ($this->verified_only) {
            $query->verified();
        }

        if (trim((string) $this->search) !== '') {
            $term = '%'.mb_strtolower(trim((string) $this->search)).'%';

            $query->where(function ($searchQuery) use ($term) {
                $searchQuery
                    ->whereRaw('LOWER(schools.name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(schools.tagline, \'\')) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(schools.description, \'\')) LIKE ?', [$term])
                    ->orWhereHas('district', fn ($districtQuery) => $districtQuery->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        if ($this->feeFilterEnabled) {
            $query->whereHas('fees', fn ($fees) => $termTuition($fees)
                ->where('amount', '<=', max(0, (int) $this->maxFee)));
        }

        match ($this->sort) {
            'rating' => $query->orderByDesc('rating_average')->latest('created_at'),
            'featured' => $query->orderByDesc('is_featured')->latest('created_at'),
            'name' => $query->orderBy('name'),
            'fee_low' => $this->sortByTermFee($query, $termTuition, 'asc'),
            'fee_high' => $this->sortByTermFee($query, $termTuition, 'desc'),
            default => $query->latest('created_at'),
        };

        $schoolTypes = SchoolType::query()
            ->where('is_active', true)
            ->withCount(['schools as public_listing_count' => fn ($schools) => $schools->publiclyVisible()])
            ->orderBy('order')
            ->get();

        $districts = District::query()
            ->with('region')
            ->withCount(['schools as public_listing_count' => fn ($schools) => $schools->publiclyVisible()])
            ->orderByDesc('public_listing_count')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $regions = Region::query()
            ->whereHas('districts.schools', fn ($schools) => $schools->publiclyVisible())
            ->orderBy('name')
            ->get();

        $searchFeatureAd = SchoolAdvertisement::query()
            ->where('placement', 'search_feature')
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($query) => $query->where('is_platform_ad', true)
                ->orWhereHas('school', fn ($school) => $school->publiclyVisible()))
            ->orderByDesc('is_platform_ad')
            ->latest('created_at')
            ->first();

        return view('livewire.schools.school-listing', [
            'schools' => $query->paginate(15)->withQueryString(),
            'schoolTypes' => $schoolTypes,
            'districts' => $districts,
            'allDistricts' => District::query()->orderBy('name')->get(['id', 'name']),
            'regions' => $regions,
            'curricula' => Curriculum::query()->where('is_active', true)->orderBy('name')->get(),
            'allSchoolCount' => School::query()->publiclyVisible()->count(),
            'searchFeatureAd' => $searchFeatureAd,
        ])->layout('layouts.app', [
            'title' => 'Find a school in Uganda — FindURSCH',
            'description' => 'Find verified nursery, primary, secondary, vocational and international schools across Uganda. Compare districts, curricula and published tuition fees.',
        ]);
    }

    private function sortByTermFee(Builder $query, callable $termTuition, string $direction): Builder
    {
        $lowestFee = SchoolFee::query()
            ->select('amount')
            ->whereColumn('school_fees.school_id', 'schools.id');

        $termTuition($lowestFee)->orderBy('amount');

        return $query
            ->orderByRaw('CASE WHEN ('.$lowestFee->toSql().') IS NULL THEN 1 ELSE 0 END ASC', $lowestFee->getBindings())
            ->orderBy($lowestFee, $direction);
    }
}
