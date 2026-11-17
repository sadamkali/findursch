<div>
    <div class="bg-gradient-to-br from-primary-50 to-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-normal mb-3" style="font-family: 'DM Serif Display', serif;">Discover Schools</h1>
            <p class="text-gray-600">Browse {{ $schools->total() }} school listings across Uganda</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid lg:grid-cols-[280px_1fr] gap-8">
            {{-- FILTERS --}}
            <aside class="space-y-6">
                <div class="bg-white rounded-card border border-gray-100 p-5">
                    <h3 class="font-semibold mb-3">Search</h3>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="School name..." class="w-full rounded-input border border-gray-300 px-3 py-2 text-sm" />
                </div>

                <div class="bg-white rounded-card border border-gray-100 p-5">
                    <h3 class="font-semibold mb-3">District</h3>
                    <select wire:model.live="district" class="w-full rounded-input border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All districts</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-white rounded-card border border-gray-100 p-5">
                    <h3 class="font-semibold mb-3">School Type</h3>
                    <select wire:model.live="type" class="w-full rounded-input border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All types</option>
                        @foreach($schoolTypes as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-white rounded-card border border-gray-100 p-5">
                    <h3 class="font-semibold mb-3">Curriculum</h3>
                    <select wire:model.live="curriculum" class="w-full rounded-input border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All curricula</option>
                        @foreach($curricula as $c)
                            <option value="{{ $c->id }}">{{ $c->abbreviation }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-white rounded-card border border-gray-100 p-5">
                    <h3 class="font-semibold mb-3">Boarding</h3>
                    <select wire:model.live="boarding" class="w-full rounded-input border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Any</option>
                        <option value="boarding">Boarding</option>
                        <option value="day">Day only</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>

                <div class="bg-white rounded-card border border-gray-100 p-5">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.live="verified_only" class="rounded" />
                        <span>Verified only</span>
                    </label>
                </div>
            </aside>

            {{-- SCHOOLS --}}
            <div>
                <div class="flex items-center justify-between mb-5">
                    <p class="text-sm text-gray-600">{{ $schools->total() }} schools</p>
                    <select wire:model.live="sort" class="rounded-input border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="featured">Featured first</option>
                        <option value="rating">Highest rated</option>
                        <option value="newest">Newest</option>
                        <option value="name">Name (A-Z)</option>
                    </select>
                </div>

                @if($schools->count() > 0)
                    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($schools as $school)
                            @include('components.school-card', ['school' => $school])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $schools->links() }}</div>
                @else
                    <div class="text-center py-20 bg-gray-50 rounded-card">
                        <p class="text-gray-600">No schools match your filters.</p>
                        <button wire:click="resetFilters" class="mt-3 btn-secondary text-sm">Clear filters</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
