<div>
    <section class="bg-gray-50 py-12"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><p class="text-sm font-semibold uppercase tracking-wider text-primary">{{ $district->region?->name }} Region</p><h1 class="mt-2 text-3xl font-semibold text-gray-900 sm:text-4xl" style="font-family: 'DM Serif Display', serif;">Schools in {{ $district->name }} District</h1><p class="mt-3 text-sm text-gray-600">{{ $schools->total() }} school listings currently available in this district.</p></div></section>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if($schools->count())<div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">@foreach($schools as $school)@include('components.school-card', ['school' => $school])@endforeach</div><div class="mt-8">{{ $schools->links() }}</div>
        @else<div class="rounded-xl bg-gray-50 px-6 py-16 text-center"><h2 class="font-semibold text-gray-900">No school listings yet</h2><p class="mt-2 text-sm text-gray-500">There are currently no public school listings for {{ $district->name }}.</p><a href="{{ route('schools.index') }}" class="btn-primary mt-5 inline-flex">Browse all schools</a></div>@endif
    </section>
</div>
