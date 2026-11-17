<div>
    {{-- DIRECTORY HEADER --}}
    <section class="border-b border-gray-100 bg-gray-50 py-9 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-primary">FindURSCH directory</p>
            <h1 class="text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl" style="font-family: 'DM Serif Display', serif;">Find a school in Uganda</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600">Search school profiles and narrow your results by district, education level, curriculum and boarding options.</p>
        </div>
    </section>

    {{-- FILTER + RESULTS --}}
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <div class="grid items-start gap-7 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="lg:sticky lg:top-24">
                <div class="border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <h2 class="font-semibold text-gray-900">Filter schools</h2>
                        <button type="button" wire:click="resetFilters" class="text-xs font-semibold text-primary hover:underline">Clear all</button>
                    </div>

                    <div class="space-y-5 pt-5">
                        <div>
                            <label for="school-search" class="mb-2 block text-sm font-semibold text-gray-800">School name or keyword</label>
                            <input id="school-search" wire:model.live.debounce.300ms="search" type="search" placeholder="e.g. school name" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15">
                        </div>

                        <div>
                            <label for="school-district" class="mb-2 block text-sm font-semibold text-gray-800">District</label>
                            <select id="school-district" wire:model.live="district" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-primary">
                                <option value="">All districts</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="school-type" class="mb-2 block text-sm font-semibold text-gray-800">School type</label>
                            <select id="school-type" wire:model.live="type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-primary">
                                <option value="">All school types</option>
                                @foreach($schoolTypes as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="school-curriculum" class="mb-2 block text-sm font-semibold text-gray-800">Curriculum</label>
                            <select id="school-curriculum" wire:model.live="curriculum" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-primary">
                                <option value="">All curricula</option>
                                @foreach($curricula as $c)
                                    <option value="{{ $c->id }}">{{ $c->abbreviation }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="school-boarding" class="mb-2 block text-sm font-semibold text-gray-800">Boarding option</label>
                            <select id="school-boarding" wire:model.live="boarding" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-primary">
                                <option value="">Any arrangement</option>
                                <option value="boarding">Boarding</option>
                                <option value="day">Day</option>
                                <option value="mixed">Day and boarding</option>
                            </select>
                        </div>

                        <label class="flex cursor-pointer items-center gap-3 border-t border-gray-100 pt-4 text-sm text-gray-700">
                            <input type="checkbox" wire:model.live="verified_only" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <span>Show verified schools only</span>
                        </label>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <div class="mb-5 flex flex-col gap-3 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ number_format($schools->total()) }} {{ Str::plural('school listing', $schools->total()) }}</p>
                        <p class="mt-1 text-xs text-gray-500">Use the filters to narrow your search.</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="shrink-0">Sort by</span>
                        <select wire:model.live="sort" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 outline-none focus:border-primary">
                            <option value="featured">Featured first</option>
                            <option value="rating">Highest rated</option>
                            <option value="newest">Recently updated</option>
                            <option value="name">Name (A–Z)</option>
                        </select>
                    </label>
                </div>

                @if($schools->count() > 0)
                    <div class="grid gap-5 sm:grid-cols-2 2xl:grid-cols-3">
                        @foreach($schools as $school)
                            @include('components.school-card', ['school' => $school])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $schools->links() }}</div>
                @else
                    <div class="border border-dashed border-gray-300 bg-gray-50 px-6 py-16 text-center">
                        <h2 class="text-lg font-semibold text-gray-900">No schools found</h2>
                        <p class="mt-2 text-sm text-gray-600">Try changing or clearing one or more filters.</p>
                        <button type="button" wire:click="resetFilters" class="btn-primary mt-5">Clear filters</button>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
