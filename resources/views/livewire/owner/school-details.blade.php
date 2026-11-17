<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-primary hover:underline">← Back to your listings</a>
        <p class="mt-5 text-sm font-semibold uppercase tracking-wider text-primary">School owner area</p>
        <h1 class="mt-2 text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">{{ $school->name }}</h1>
        <p class="mt-2 text-sm text-gray-600">Manage academic programmes, fee information and contact methods.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('owner.schools.edit', $school->id) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Edit school profile</a>
            <a href="{{ route('owner.inquiries') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">School inquiries</a>
            <a href="{{ route('owner.reviews') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">School reviews</a>
        </div>
    </div>

    @if (session('message'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('message') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-900">Academic programmes</h2>
            <p class="mt-1 text-sm text-gray-500">Add levels or programmes offered by your school.</p>

            <form wire:submit="addProgram" class="mt-5 space-y-4">
                <div>
                    <label for="programName" class="mb-1 block text-sm font-medium text-gray-700">Programme name *</label>
                    <input id="programName" wire:model="programName" required maxlength="180" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="programLevel" class="mb-1 block text-sm font-medium text-gray-700">Level</label>
                        <input id="programLevel" wire:model="programLevel" maxlength="60" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm" placeholder="e.g. Primary">
                    </div>
                    <div>
                        <label for="programDuration" class="mb-1 block text-sm font-medium text-gray-700">Duration</label>
                        <input id="programDuration" wire:model="programDuration" maxlength="60" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm" placeholder="e.g. 7 years">
                    </div>
                </div>
                <div>
                    <label for="curriculumId" class="mb-1 block text-sm font-medium text-gray-700">Curriculum</label>
                    <select id="curriculumId" wire:model="curriculumId" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="">Not specified</option>
                        @foreach ($curricula as $curriculum)
                            <option value="{{ $curriculum->id }}">{{ $curriculum->name }}</option>
                        @endforeach
                    </select>
                    @error('curriculum_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="programDescription" class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="programDescription" wire:model="programDescription" rows="3" maxlength="5000" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"></textarea>
                </div>
                <button type="submit" wire:loading.attr="disabled" class="btn-primary">Add programme</button>
            </form>

            <div class="mt-7 divide-y divide-gray-100 border-t border-gray-100">
                @forelse ($school->programs as $program)
                    <div class="flex items-start justify-between gap-3 py-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $program->name }}</p>
                            <p class="mt-1 text-sm text-gray-500">{{ $program->level }} @if($program->duration) · {{ $program->duration }} @endif @if($program->curriculum) · {{ $program->curriculum->name }} @endif</p>
                            @if ($program->description)
                                <p class="mt-2 text-sm text-gray-600">{{ $program->description }}</p>
                            @endif
                        </div>
                        <button type="button" wire:click="deleteProgram({{ $program->id }})" wire:confirm="Remove this programme?" class="shrink-0 text-sm font-medium text-red-600 hover:underline">Remove</button>
                    </div>
                @empty
                    <p class="py-5 text-sm text-gray-500">No programmes added yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-900">School fees</h2>
            <p class="mt-1 text-sm text-gray-500">Enter accurate charges in Ugandan shillings (UGX).</p>

            <form wire:submit="addFee" class="mt-5 space-y-4">
                <div>
                    <label for="feeProgramId" class="mb-1 block text-sm font-medium text-gray-700">Related programme</label>
                    <select id="feeProgramId" wire:model="feeProgramId" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="">General school fee</option>
                        @foreach ($school->programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                    @error('program_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="feeCategory" class="mb-1 block text-sm font-medium text-gray-700">Fee category *</label>
                        <select id="feeCategory" wire:model="feeCategory" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                            <option value="tuition">Tuition</option>
                            <option value="boarding">Boarding</option>
                            <option value="uniform">Uniform</option>
                            <option value="meals">Meals</option>
                            <option value="activities">Activities</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="feePeriod" class="mb-1 block text-sm font-medium text-gray-700">Payment period *</label>
                        <select id="feePeriod" wire:model="feePeriod" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                            <option value="per_term">Per term</option>
                            <option value="per_year">Per year</option>
                            <option value="per_month">Per month</option>
                            <option value="once_off">Once-off</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="feeLabel" class="mb-1 block text-sm font-medium text-gray-700">Fee description *</label>
                    <input id="feeLabel" wire:model="feeLabel" required maxlength="180" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm" placeholder="e.g. P1 tuition">
                    @error('label') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="feeAmount" class="mb-1 block text-sm font-medium text-gray-700">Amount (UGX) *</label>
                        <input id="feeAmount" type="number" min="0" step="0.01" wire:model="feeAmount" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="feeAcademicYear" class="mb-1 block text-sm font-medium text-gray-700">Academic year</label>
                        <input id="feeAcademicYear" wire:model="feeAcademicYear" maxlength="20" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm" placeholder="e.g. 2026">
                    </div>
                </div>
                <div>
                    <label for="feeNotes" class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="feeNotes" wire:model="feeNotes" rows="2" maxlength="2000" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm"></textarea>
                </div>
                <button type="submit" wire:loading.attr="disabled" class="btn-primary">Add fee information</button>
            </form>

            <div class="mt-7 divide-y divide-gray-100 border-t border-gray-100">
                @forelse ($school->fees as $fee)
                    <div class="flex items-start justify-between gap-3 py-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $fee->label }}</p>
                            <p class="mt-1 text-sm text-gray-500">{{ ucfirst($fee->category) }} · {{ str_replace('_', ' ', $fee->period) }} @if($fee->program) · {{ $fee->program->name }} @endif</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">UGX {{ number_format((float) $fee->amount) }}</p>
                        </div>
                        <button type="button" wire:click="deleteFee({{ $fee->id }})" wire:confirm="Remove this fee entry?" class="shrink-0 text-sm font-medium text-red-600 hover:underline">Remove</button>
                    </div>
                @empty
                    <p class="py-5 text-sm text-gray-500">No fee information added yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 xl:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900">Contact methods</h2>
            <p class="mt-1 text-sm text-gray-500">Provide the channels parents can use to contact the school.</p>

            <form wire:submit="addContact" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="contactType" class="mb-1 block text-sm font-medium text-gray-700">Type *</label>
                    <select id="contactType" wire:model="contactType" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="phone">Phone</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="website">Website</option>
                        <option value="facebook">Facebook</option>
                        <option value="twitter">X / Twitter</option>
                        <option value="instagram">Instagram</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="contactValue" class="mb-1 block text-sm font-medium text-gray-700">Contact detail *</label>
                    <input id="contactValue" wire:model="contactValue" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">
                    @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contactLabel" class="mb-1 block text-sm font-medium text-gray-700">Label</label>
                    <input id="contactLabel" wire:model="contactLabel" maxlength="120" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm" placeholder="Admissions office">
                </div>
                <div class="flex items-end gap-3">
                    <label class="flex flex-1 items-center gap-2 pb-3 text-sm text-gray-600">
                        <input type="checkbox" wire:model="contactPrimary" class="rounded border-gray-300 text-primary focus:ring-primary">
                        Primary
                    </label>
                    <button type="submit" wire:loading.attr="disabled" class="btn-primary">Add contact</button>
                </div>
            </form>

            <div class="mt-7 grid grid-cols-1 gap-3 md:grid-cols-2">
                @forelse ($school->contacts as $contact)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $contact->label ?: ucfirst($contact->type) }}</p>
                            <p class="mt-1 break-all text-sm text-gray-600">{{ $contact->value }}</p>
                            @if ($contact->is_primary)
                                <span class="mt-2 inline-block rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">Primary contact</span>
                            @endif
                        </div>
                        <button type="button" wire:click="deleteContact({{ $contact->id }})" wire:confirm="Remove this contact method?" class="text-sm font-medium text-red-600 hover:underline">Remove</button>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No contact methods added yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</section>
