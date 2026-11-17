<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-primary">School owner area</p>
            <h1 class="mt-2 text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Your school listings</h1>
            <p class="mt-2 max-w-2xl text-sm text-gray-600">Manage your school information and follow the admin verification process.</p>
        </div>
        <a href="{{ route('owner.schools.create') }}" class="btn-primary inline-flex justify-center">Add a school</a>
    </div>

    @if (session('message'))
        <div class="mt-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('message') }}</div>
    @endif

    <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Total listings</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalSchools }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Active</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $activeSchools }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Pending review</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $pendingSchools }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-sm text-gray-500">Drafts</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $draftSchools }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-blue-200 bg-blue-50 p-5">
        <h2 class="font-semibold text-blue-950">Listing plans</h2>
        <p class="mt-1 text-sm leading-6 text-blue-900">
            Free school listings are available during this development phase. Standard and Premium plan information may be displayed on the public site, but subscriptions and online payments are not active yet.
        </p>
    </div>

    <div class="mt-8 overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">Your schools</h2>
        </div>

        @forelse ($schools as $school)
            <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-5 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-semibold text-gray-900">{{ $school->name }}</h3>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium capitalize text-gray-700">{{ str_replace('_', ' ', $school->status) }}</span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium capitalize text-gray-700">{{ str_replace('_', ' ', $school->verification_status) }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $school->type?->name ?? 'School type not selected' }}
                        @if ($school->district)
                            <span aria-hidden="true">·</span> {{ $school->district->name }}
                        @endif
                    </p>
                    @if ($school->rejection_reason)
                        <p class="mt-2 text-sm text-red-700">Admin note: {{ $school->rejection_reason }}</p>
                    @endif
                    <p class="mt-2 text-xs text-gray-500">{{ $school->programs_count }} programmes · {{ $school->inquiries_count }} inquiries</p>
                </div>
                <a href="{{ route('owner.schools.edit', $school->id) }}" class="inline-flex shrink-0 justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary">Manage listing</a>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <h3 class="font-semibold text-gray-900">No school listings yet</h3>
                <p class="mt-2 text-sm text-gray-500">Add your first school and save it as a draft or submit it for admin review.</p>
                <a href="{{ route('owner.schools.create') }}" class="btn-primary mt-5 inline-flex">Add your school</a>
            </div>
        @endforelse
    </div>
</section>
