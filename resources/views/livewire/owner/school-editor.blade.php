<section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-primary hover:underline">← Back to your listings</a>
        <p class="mt-5 text-sm font-semibold uppercase tracking-wider text-primary">School owner area</p>
        <h1 class="mt-2 text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">
            {{ $editing ? 'Edit school information' : 'Add your school' }}
        </h1>
        <p class="mt-2 text-sm text-gray-600">Enter accurate school details. An admin must review new or changed information before the listing is published.</p>
    </div>

    <form wire:submit.prevent="saveDraft" class="space-y-8">
        <div class="rounded-xl border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-900">School details</h2>
            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">School name *</label>
                    <input id="name" wire:model="name" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tagline" class="mb-1.5 block text-sm font-medium text-gray-700">Short tagline</label>
                    <input id="tagline" wire:model="tagline" maxlength="255" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('tagline') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="type_id" class="mb-1.5 block text-sm font-medium text-gray-700">School type *</label>
                    <select id="type_id" wire:model="type_id" required class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Choose school type</option>
                        @foreach ($schoolTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('type_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="district_id" class="mb-1.5 block text-sm font-medium text-gray-700">District *</label>
                    <select id="district_id" wire:model.live="district_id" required class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Choose district</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }} — {{ $district->region?->name }}</option>
                        @endforeach
                    </select>
                    @error('district_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sub_county_id" class="mb-1.5 block text-sm font-medium text-gray-700">Sub-county</label>
                    <select id="sub_county_id" wire:model="sub_county_id" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Choose sub-county (optional)</option>
                        @foreach ($subCounties as $subCounty)
                            <option value="{{ $subCounty->id }}">{{ $subCounty->name }}</option>
                        @endforeach
                    </select>
                    @error('sub_county_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">School description</label>
                    <textarea id="description" wire:model="description" rows="5" maxlength="10000" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-900">Contact and location</h2>
            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700">Physical address</label>
                    <input id="address" wire:model="address" maxlength="255" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700">Phone number</label>
                    <input id="phone" wire:model="phone" maxlength="30" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">School email</label>
                    <input id="email" type="email" wire:model="email" maxlength="255" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="website" class="mb-1.5 block text-sm font-medium text-gray-700">Website</label>
                    <input id="website" type="url" wire:model="website" maxlength="255" placeholder="https://" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('website') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="whatsapp" class="mb-1.5 block text-sm font-medium text-gray-700">WhatsApp number</label>
                    <input id="whatsapp" wire:model="whatsapp" maxlength="30" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    @error('whatsapp') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-900">School profile</h2>
            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="boarding_type" class="mb-1.5 block text-sm font-medium text-gray-700">Boarding arrangement *</label>
                    <select id="boarding_type" wire:model="boarding_type" required class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm">
                        <option value="day">Day</option>
                        <option value="boarding">Boarding</option>
                        <option value="mixed">Day and boarding</option>
                    </select>
                    @error('boarding_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="gender_policy" class="mb-1.5 block text-sm font-medium text-gray-700">Gender policy *</label>
                    <select id="gender_policy" wire:model="gender_policy" required class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm">
                        <option value="mixed">Mixed</option>
                        <option value="boys">Boys only</option>
                        <option value="girls">Girls only</option>
                    </select>
                    @error('gender_policy') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="founded_year" class="mb-1.5 block text-sm font-medium text-gray-700">Year founded</label>
                    <input id="founded_year" type="number" wire:model="founded_year" min="1800" max="{{ date('Y') }}" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm">
                    @error('founded_year') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="registration_number" class="mb-1.5 block text-sm font-medium text-gray-700">Registration number</label>
                    <input id="registration_number" wire:model="registration_number" maxlength="60" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm">
                    @error('registration_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="student_count_approx" class="mb-1.5 block text-sm font-medium text-gray-700">Approximate student count</label>
                    <input id="student_count_approx" type="number" wire:model="student_count_approx" min="0" max="100000" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm">
                    @error('student_count_approx') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <p class="max-w-xl text-xs leading-5 text-gray-500">
                Saving a change to an already approved listing temporarily removes it from public search until an admin reviews the updated details. School photographs and payment features will be enabled in later stages.
            </p>
            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="submit" wire:loading.attr="disabled" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 hover:border-primary hover:text-primary">
                    <span wire:loading.remove>Save draft</span>
                    <span wire:loading>Saving…</span>
                </button>
                <button type="button" wire:click="submitForReview" wire:loading.attr="disabled" class="btn-primary justify-center">
                    <span wire:loading.remove>Submit for review</span>
                    <span wire:loading>Submitting…</span>
                </button>
            </div>
        </div>
    </form>
</section>
