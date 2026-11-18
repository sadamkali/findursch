<?php

namespace App\Livewire\Owner;

use App\Models\Curriculum;
use App\Models\School;
use App\Models\SchoolContact;
use App\Models\SchoolFee;
use App\Models\SchoolProgram;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class SchoolDetails extends Component
{
    #[Locked]
    public int $schoolId;

    public string $programName = '';
    public string $programLevel = '';
    public string $programDuration = '';
    public string $programDescription = '';
    public $curriculumId = '';

    public string $feeCategory = 'tuition';
    public string $feeLabel = '';
    public string $feeAmount = '';
    public string $feePeriod = 'per_term';
    public string $feeAcademicYear = '';
    public string $feeNotes = '';
    public $feeProgramId = '';

    public string $contactType = 'phone';
    public string $contactValue = '';
    public string $contactLabel = '';
    public bool $contactPrimary = false;

    public function mount(int $school): void
    {
        abort_unless(auth()->user()?->hasRole('school_owner'), 403);

        $this->schoolId = School::query()
            ->where('owner_id', auth()->id())
            ->findOrFail($school)
            ->id;
    }

    private function ownedSchool(): School
    {
        return School::query()
            ->where('owner_id', auth()->id())
            ->findOrFail($this->schoolId);
    }

    public function addProgram(): void
    {
        $validated = Validator::make([
            'name' => $this->programName,
            'level' => $this->programLevel,
            'duration' => $this->programDuration,
            'description' => $this->programDescription,
            'curriculum_id' => $this->curriculumId ?: null,
        ], [
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'level' => ['nullable', 'string', 'max:60'],
            'duration' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:5000'],
            'curriculum_id' => ['nullable', 'integer', 'exists:curricula,id'],
        ])->validate();

        $this->ownedSchool()->programs()->create([
            'name' => $validated['name'],
            'level' => $validated['level'],
            'duration' => $validated['duration'],
            'description' => $validated['description'],
            'curriculum_id' => $validated['curriculum_id'],
            'is_active' => true,
        ]);

        $this->reset('programName', 'programLevel', 'programDuration', 'programDescription', 'curriculumId');
        session()->flash('message', 'Programme added.');
    }

    public function deleteProgram(int $programId): void
    {
        $this->ownedSchool()->programs()->whereKey($programId)->firstOrFail()->delete();
        session()->flash('message', 'Programme removed.');
    }

    public function addFee(): void
    {
        $validated = Validator::make([
            'program_id' => $this->feeProgramId ?: null,
            'category' => $this->feeCategory,
            'label' => $this->feeLabel,
            'amount' => $this->feeAmount,
            'period' => $this->feePeriod,
            'academic_year' => $this->feeAcademicYear,
            'notes' => $this->feeNotes,
        ], [
            'program_id' => [
                'nullable',
                'integer',
                Rule::exists('school_programs', 'id')->where(fn ($query) => $query->where('school_id', $this->schoolId)),
            ],
            'category' => ['required', Rule::in(['tuition', 'boarding', 'uniform', 'meals', 'activities', 'other'])],
            'label' => ['required', 'string', 'max:180'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'period' => ['required', Rule::in(['per_term', 'per_year', 'once_off', 'per_month'])],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        $this->ownedSchool()->fees()->create([
            ...$validated,
            'currency' => 'UGX',
            'is_active' => true,
        ]);

        $this->reset('feeProgramId', 'feeLabel', 'feeAmount', 'feeAcademicYear', 'feeNotes');
        $this->feeCategory = 'tuition';
        $this->feePeriod = 'per_term';
        session()->flash('message', 'Fee information added.');
    }

    public function deleteFee(int $feeId): void
    {
        $this->ownedSchool()->fees()->whereKey($feeId)->firstOrFail()->delete();
        session()->flash('message', 'Fee information removed.');
    }

    public function addContact(): void
    {
        $validated = Validator::make([
            'type' => $this->contactType,
            'value' => $this->contactValue,
            'label' => $this->contactLabel,
            'is_primary' => $this->contactPrimary,
        ], [
            'type' => ['required', Rule::in(['phone', 'email', 'whatsapp', 'facebook', 'twitter', 'instagram', 'website', 'other'])],
            'value' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:120'],
            'is_primary' => ['boolean'],
        ])->validate();

        $school = $this->ownedSchool();

        if ($validated['is_primary']) {
            $school->contacts()->update(['is_primary' => false]);
        }

        $school->contacts()->create($validated);

        $this->reset('contactValue', 'contactLabel', 'contactPrimary');
        $this->contactType = 'phone';
        session()->flash('message', 'Contact method added.');
    }

    public function deleteContact(int $contactId): void
    {
        $this->ownedSchool()->contacts()->whereKey($contactId)->firstOrFail()->delete();
        session()->flash('message', 'Contact method removed.');
    }

    public function render()
    {
        $school = $this->ownedSchool()->load(['type', 'district', 'programs.curriculum', 'fees.program', 'contacts']);

        return view('livewire.owner.school-details', [
            'school' => $school,
            'curricula' => Curriculum::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
