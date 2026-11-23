<?php

namespace App\Livewire\Districts;

use App\Models\District;
use App\Models\School;
use App\Models\SchoolFee;
use App\Models\SchoolType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DistrictSchools extends Component
{
    use WithPagination;

    public District $district;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'area', except: '')]
    public string $town = '';

    #[Url(as: 'level', except: '')]
    public string $schoolType = '';

    #[Url(as: 'boarding', except: '')]
    public string $boarding = '';

    #[Url(as: 'admissions', except: false)]
    public bool $admissionsOpen = false;

    #[Url(as: 'sort', except: 'rating')]
    public string $sort = 'rating';

    public function mount(District $district): void
    {
        abort_unless($district->is_active, 404);
        $this->district = $district->load('region');
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedTown(): void { $this->resetPage(); }
    public function updatedSchoolType(): void { $this->resetPage(); }
    public function updatedBoarding(): void { $this->resetPage(); }
    public function updatedAdmissionsOpen(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }

    public function setTown(string $town): void
    {
        $this->town = $town;
        $this->resetPage();
    }

    public function setSchoolType(string $typeId): void
    {
        $this->schoolType = $typeId;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'town', 'schoolType', 'boarding', 'admissionsOpen']);
        $this->sort = 'rating';
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    private function baseSchools(): Builder
    {
        $termTuition = fn (Builder $fees) => $fees
            ->where('category', 'tuition')
            ->where('is_active', true)
            ->where('currency', 'UGX')
            ->where('period', 'per_term');

        return School::query()
            ->publiclyVisible()
            ->where('district_id', $this->district->id)
            ->with([
                'district.region',
                'county',
                'subCounty',
                'type',
                'curricula',
                'media',
            ])
            ->withMin(['fees as lowest_term_tuition' => $termTuition], 'amount');
    }

    private function filteredSchools(): Builder
    {
        $query = $this->baseSchools();

        if ($this->town !== '') {
            $query->where('town', $this->town);
        }

        if ($this->schoolType !== '' && ctype_digit($this->schoolType)) {
            $query->where('type_id', (int) $this->schoolType);
        }

        if (in_array($this->boarding, ['day', 'boarding', 'mixed'], true)) {
            $query->where('boarding_type', $this->boarding);
        }

        if ($this->admissionsOpen) {
            $query->where('admissions_open', true);
        }

        $term = trim($this->search);
        if ($term !== '') {
            $pattern = '%'.mb_strtolower($term).'%';
            $query->where(function (Builder $search) use ($pattern) {
                $search->whereRaw('LOWER(schools.name) LIKE ?', [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(schools.tagline, '')) LIKE ?", [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(schools.description, '')) LIKE ?", [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(schools.town, '')) LIKE ?", [$pattern])
                    ->orWhereRaw("LOWER(COALESCE(schools.address, '')) LIKE ?", [$pattern])
                    ->orWhereHas('county', fn (Builder $county) => $county->whereRaw('LOWER(counties.name) LIKE ?', [$pattern]))
                    ->orWhereHas('subCounty', fn (Builder $subCounty) => $subCounty->whereRaw('LOWER(sub_counties.name) LIKE ?', [$pattern]));
            });
        }

        return $query;
    }

    private function orderedSchools(Builder $query): Builder
    {
        return match ($this->sort) {
            'fee_low' => $query
                ->orderByRaw('CASE WHEN lowest_term_tuition IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('lowest_term_tuition')
                ->orderBy('name'),
            'name' => $query->orderBy('name'),
            default => $query
                ->orderByDesc('rating_average')
                ->orderByDesc('reviews_count')
                ->orderByDesc('is_featured')
                ->orderBy('name'),
        };
    }

    public function render()
    {
        $base = $this->baseSchools();
        $filtered = $this->filteredSchools();

        $schoolTypes = SchoolType::query()
            ->where('is_active', true)
            ->withCount(['schools as public_listing_count' => fn (Builder $schools) => $schools
                ->publiclyVisible()
                ->where('district_id', $this->district->id)])
            ->orderBy('order')
            ->get()
            ->filter(fn (SchoolType $type) => (int) $type->public_listing_count > 0)
            ->values();

        $areas = School::query()
            ->publiclyVisible()
            ->where('district_id', $this->district->id)
            ->whereNotNull('town')
            ->whereRaw("BTRIM(town) <> ''")
            ->selectRaw('BTRIM(town) AS name, COUNT(*) AS total')
            ->groupByRaw('BTRIM(town)')
            ->orderBy('name')
            ->get();

        $publicSchoolIds = School::query()
            ->publiclyVisible()
            ->where('district_id', $this->district->id)
            ->select('id');

        $lowestTermFees = SchoolFee::query()
            ->selectRaw('school_id, MIN(amount) AS minimum_amount')
            ->where('category', 'tuition')
            ->where('is_active', true)
            ->where('currency', 'UGX')
            ->where('period', 'per_term')
            ->whereIn('school_id', $publicSchoolIds)
            ->groupBy('school_id');

        $averageTermFee = DB::query()
            ->fromSub($lowestTermFees, 'district_school_term_fees')
            ->avg('minimum_amount');

        $totalSchools = (clone $base)->count();
        $admissionsOpenCount = (clone $base)->where('admissions_open', true)->count();
        $boardingCount = (clone $base)->whereIn('boarding_type', ['boarding', 'mixed'])->count();

        $nearbyDistricts = District::query()
            ->where('is_active', true)
            ->where('id', '!=', $this->district->id)
            ->when($this->district->region_id, fn (Builder $districts) => $districts->where('region_id', $this->district->region_id))
            ->whereHas('schools', fn (Builder $schools) => $schools->publiclyVisible())
            ->withCount(['schools as public_listing_count' => fn (Builder $schools) => $schools->publiclyVisible()])
            ->orderByDesc('public_listing_count')
            ->orderBy('name')
            ->limit(5)
            ->get();

        $levelNames = $schoolTypes->pluck('name')->implode(', ');
        $faqs = [
            [
                'question' => 'How many schools are listed in '.$this->district->name.' District?',
                'answer' => 'FindURSCH currently has '.$totalSchools.' verified public '.\Illuminate\Support\Str::plural('school listing', $totalSchools).' for '.$this->district->name.' District. The directory only displays schools that are active and verified.',
            ],
            [
                'question' => 'Which school levels can I find in '.$this->district->name.'?',
                'answer' => $levelNames !== ''
                    ? 'The current public listings include: '.$levelNames.'. Use the school-level filter to narrow the results.'
                    : 'There are no public school listings with a school level recorded in this district yet.',
            ],
            [
                'question' => 'How can I confirm fees and admission requirements?',
                'answer' => 'Open a school profile to review the information it has published. Fees, available places and admission requirements can change, so contact the school directly to confirm the latest details.',
            ],
            [
                'question' => 'Can I compare schools in this district?',
                'answer' => 'Yes. Use the compare control on a school card to add schools to your comparison list, then open Compare schools to review your selections.',
            ],
        ];

        return view('livewire.districts.district-schools', [
            'schools' => $this->orderedSchools($filtered)->paginate(12)->withQueryString(),
            'filteredCount' => (clone $filtered)->count(),
            'totalSchools' => $totalSchools,
            'admissionsOpenCount' => $admissionsOpenCount,
            'boardingCount' => $boardingCount,
            'schoolTypes' => $schoolTypes,
            'areas' => $areas,
            'nearbyDistricts' => $nearbyDistricts,
            'averageTermFee' => $averageTermFee,
            'faqs' => $faqs,
        ])->layout('layouts.app', [
            'title' => 'Schools in '.$this->district->name.' District — FindURSCH',
            'description' => 'Discover verified nursery, primary, secondary, university and vocational school listings in '.$this->district->name.' District, '.$this->district->region?->name.' Region. Compare school profiles, facilities and published fees.',
        ]);
    }
}
