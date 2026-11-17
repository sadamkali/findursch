<?php

namespace App\Livewire\Owner;

use App\Models\District;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\SubCounty;
use App\Services\SchoolService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class SchoolEditor extends Component
{
    #[Locked]
    public ?int $schoolId = null;

    public string $name = '';
    public $tagline = '';
    public $description = '';
    public $type_id = '';
    public $district_id = '';
    public $sub_county_id = '';
    public $address = '';
    public $phone = '';
    public $email = '';
    public $website = '';
    public $whatsapp = '';
    public string $boarding_type = 'day';
    public string $gender_policy = 'mixed';
    public $founded_year = '';
    public $registration_number = '';
    public $student_count_approx = '';

    public function mount(?int $school = null): void
    {
        abort_unless(auth()->user()?->hasRole('school_owner'), 403);

        if ($school === null) {
            return;
        }

        $record = School::query()
            ->where('owner_id', auth()->id())
            ->findOrFail($school);

        $this->schoolId = $record->id;
        $this->name = $record->name;
        $this->tagline = (string) ($record->tagline ?? '');
        $this->description = (string) ($record->description ?? '');
        $this->type_id = (string) $record->type_id;
        $this->district_id = (string) $record->district_id;
        $this->sub_county_id = (string) ($record->sub_county_id ?? '');
        $this->address = (string) ($record->address ?? '');
        $this->phone = (string) ($record->phone ?? '');
        $this->email = (string) ($record->email ?? '');
        $this->website = (string) ($record->website ?? '');
        $this->whatsapp = (string) ($record->whatsapp ?? '');
        $this->boarding_type = $record->boarding_type;
        $this->gender_policy = $record->gender_policy;
        $this->founded_year = (string) ($record->founded_year ?? '');
        $this->registration_number = (string) ($record->registration_number ?? '');
        $this->student_count_approx = (string) ($record->student_count_approx ?? '');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type_id' => ['required', 'integer', Rule::exists('school_types', 'id')->where('is_active', true)],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'sub_county_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_counties', 'id')->where(fn ($query) => $query->where('district_id', $this->district_id)),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'boarding_type' => ['required', Rule::in(['boarding', 'day', 'mixed'])],
            'gender_policy' => ['required', Rule::in(['boys', 'girls', 'mixed'])],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'registration_number' => ['nullable', 'string', 'max:60'],
            'student_count_approx' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function updatedDistrictId(): void
    {
        if ($this->sub_county_id !== '' && ! SubCounty::query()
            ->whereKey($this->sub_county_id)
            ->where('district_id', $this->district_id)
            ->exists()) {
            $this->sub_county_id = '';
        }
    }

    public function saveDraft()
    {
        $this->persistSchool();

        session()->flash('message', 'Your school details have been saved as a draft.');

        return redirect()->route('owner.dashboard');
    }

    public function submitForReview()
    {
        $school = $this->persistSchool();

        app(SchoolService::class)->submit($school);

        session()->flash('message', 'Your school has been submitted for admin review.');

        return redirect()->route('owner.dashboard');
    }

    private function persistSchool(): School
    {
        foreach ([
            'tagline',
            'description',
            'sub_county_id',
            'address',
            'phone',
            'email',
            'website',
            'whatsapp',
            'founded_year',
            'registration_number',
            'student_count_approx',
        ] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

        $data = $this->validate();

        if ($this->schoolId === null) {
            $school = app(SchoolService::class)->createDraft(auth()->user(), $data);
            $this->schoolId = $school->id;
        } else {
            $school = School::query()
                ->where('owner_id', auth()->id())
                ->findOrFail($this->schoolId);

            $school->update([
                ...$data,
                'status' => 'draft',
                'verification_status' => 'unverified',
                'verified_at' => null,
                'verified_by' => null,
                'submitted_at' => null,
                'rejection_reason' => null,
            ]);
        }

        if (! $submit) {
            return $school->refresh();
        }

        return $school->refresh();
    }

    public function render()
    {
        $districts = District::query()->with('region')->orderBy('name')->get();
        $subCounties = $this->district_id
            ? SubCounty::query()->where('district_id', $this->district_id)->orderBy('name')->get()
            : collect();

        return view('livewire.owner.school-editor', [
            'schoolTypes' => SchoolType::query()->where('is_active', true)->orderBy('order')->get(),
            'districts' => $districts,
            'subCounties' => $subCounties,
            'editing' => $this->schoolId !== null,
        ]);
    }
}
