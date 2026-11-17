<div>
    {{-- SCHOOL DISCOVERY INTRO + SEARCH --}}
    <section class="border-b border-gray-100 bg-gradient-to-b from-primary-50/70 to-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-primary">Uganda school directory</p>
                <h1 class="text-4xl font-semibold leading-tight tracking-tight text-gray-950 sm:text-5xl" style="font-family: 'DM Serif Display', serif;">
                    Discover schools.<br class="hidden sm:block"> Find the right fit.
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg">
                    Explore school profiles across Uganda, compare your options and find the information you need in one place.
                </p>
            </div>

            <form action="{{ route('schools.index') }}" method="GET" class="mt-8 grid gap-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-card sm:grid-cols-2 lg:grid-cols-[minmax(0,1.6fr)_minmax(150px,1fr)_minmax(150px,1fr)_auto]">
                <label class="block sm:col-span-2 lg:col-span-1">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">School or keyword</span>
                    <input type="search" name="search" placeholder="Search by school name..." class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-3 text-sm text-gray-900 outline-none transition focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/15">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">School type</span>
                    <select name="type" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-3 text-sm text-gray-700 outline-none focus:border-primary focus:bg-white">
                        <option value="">All types</option>
                        @foreach($schoolTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">District</span>
                    <select name="district" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-3 text-sm text-gray-700 outline-none focus:border-primary focus:bg-white">
                        <option value="">All districts</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full justify-center px-6 py-3 lg:w-auto">Find schools</button>
                </div>
            </form>

            <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-500">
                <span><strong class="font-semibold text-gray-900">{{ number_format($stats['schools']) }}</strong> public listings</span>
                <span><strong class="font-semibold text-gray-900">{{ number_format($stats['districts']) }}</strong> districts</span>
                <span><strong class="font-semibold text-gray-900">{{ number_format($stats['reviews']) }}</strong> approved reviews</span>
            </div>
        </div>
    </section>

    {{-- SCHOOL DISCOVERY GRID --}}
    @if($featuredSchools->count() > 0)
    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-primary">Start exploring</p>
                    <h2 class="text-3xl font-semibold tracking-tight text-gray-900" style="font-family: 'DM Serif Display', serif;">Schools to discover</h2>
                    <p class="mt-2 text-sm text-gray-600">A selection of public school profiles from across Uganda.</p>
                </div>
                <a href="{{ route('schools.index') }}" class="text-sm font-semibold text-primary hover:underline">Browse all schools <span aria-hidden="true">→</span></a>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($featuredSchools as $school)
                    @include('components.school-card', ['school' => $school])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- BROWSE BY SCHOOL TYPE --}}
    <section class="border-y border-gray-100 bg-gray-50 py-12 sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-7">
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-primary">Browse by category</p>
                <h2 class="text-2xl font-semibold tracking-tight text-gray-900" style="font-family: 'DM Serif Display', serif;">Find schools by education level</h2>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
                @foreach($schoolTypes as $type)
                    <a href="{{ route('schools.index', ['type' => $type->id]) }}" class="group flex min-h-20 items-center justify-between gap-3 border border-gray-200 bg-white px-4 py-4 transition hover:border-primary hover:shadow-card">
                        <span class="text-sm font-semibold text-gray-800 group-hover:text-primary">{{ $type->name }}</span>
                        <span class="text-lg text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-primary" aria-hidden="true">→</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- BROWSE BY DISTRICT --}}
    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-7 flex items-end justify-between gap-4">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-primary">Explore locations</p>
                    <h2 class="text-2xl font-semibold tracking-tight text-gray-900" style="font-family: 'DM Serif Display', serif;">Browse by district</h2>
                    <p class="mt-2 text-sm text-gray-600">Discover school listings in different parts of Uganda.</p>
                </div>
                <a href="{{ route('schools.index') }}" class="hidden text-sm font-semibold text-primary hover:underline sm:inline">All districts →</a>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach($districts as $district)
                    <a href="{{ route('district.show', $district) }}" class="border border-gray-200 bg-white p-4 transition hover:border-primary hover:shadow-card">
                        <h3 class="font-semibold text-gray-900">{{ $district->name }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ $district->region?->name }} Region</p>
                        <p class="mt-3 text-xs text-gray-500">{{ number_format($district->school_count) }} {{ Str::plural('school listing', $district->school_count) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ARTICLES --}}
    @if($articles->count() > 0)
    <section class="border-t border-gray-100 bg-gray-50 py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-7 flex items-end justify-between gap-4">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-primary">Helpful reading</p>
                    <h2 class="text-2xl font-semibold tracking-tight text-gray-900" style="font-family: 'DM Serif Display', serif;">Education news and guides</h2>
                    <p class="mt-2 text-sm text-gray-600">Useful information for parents, guardians and school communities.</p>
                </div>
                <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-primary hover:underline">All articles →</a>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                @foreach($articles as $article)
                    <article class="overflow-hidden border border-gray-200 bg-white transition hover:shadow-card">
                        <a href="{{ route('articles.show', $article) }}" class="block">
                            <div class="aspect-[16/9] bg-gradient-to-br from-primary-100 to-accent-50"></div>
                            <div class="p-5">
                                <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $article->category?->name }}</span>
                                <h3 class="mt-2 line-clamp-2 font-semibold text-gray-900">{{ $article->title }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-gray-600">{{ $article->excerpt }}</p>
                                <p class="mt-4 text-xs text-gray-400">{{ $article->read_time_minutes }} min read</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- LIST YOUR SCHOOL --}}
    <section class="bg-primary py-12 text-white sm:py-14">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-white/70">For school communities</p>
                <h2 class="mt-2 text-2xl font-semibold" style="font-family: 'DM Serif Display', serif;">Help families discover your school</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">Create a school profile and share the information families need to learn about your institution.</p>
            </div>
            <a href="{{ route('register') }}" class="inline-flex shrink-0 items-center justify-center border border-white/50 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white hover:text-primary">List your school <span class="ml-3" aria-hidden="true">→</span></a>
        </div>
    </section>
</div>
