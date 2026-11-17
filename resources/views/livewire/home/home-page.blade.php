<div>
    {{-- HERO --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-accent-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="inline-block bg-primary-50 text-primary-700 rounded-badge px-3 py-1 text-xs font-bold uppercase tracking-wider mb-5">Uganda's #1 School Platform</span>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-normal leading-tight mb-6" style="font-family: 'DM Serif Display', serif;">
                        Find the <em class="text-primary">perfect school</em> for your child
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 max-w-xl">
                        Discover, compare, and connect with the best nurseries, primary, secondary, and international schools across Uganda.
                    </p>
                    <form action="{{ route('search') }}" method="GET" class="flex gap-2 max-w-xl">
                        <input type="text" name="q" placeholder="Search schools, districts, curricula..." class="flex-1 rounded-input border border-gray-300 px-4 py-3 focus:border-primary focus:ring-primary" />
                        <button type="submit" class="btn-primary whitespace-nowrap">Search</button>
                    </form>
                    <div class="mt-8 flex flex-wrap gap-6 text-sm text-gray-600">
                        <div><span class="font-bold text-2xl text-gray-900 block">{{ $stats['schools'] }}</span> schools</div>
                        <div><span class="font-bold text-2xl text-gray-900 block">{{ $stats['districts'] }}</span> districts</div>
                        <div><span class="font-bold text-2xl text-gray-900 block">{{ $stats['reviews'] }}</span> reviews</div>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="relative">
                        <div class="absolute -top-8 -left-8 w-72 h-72 bg-primary-100 rounded-full blur-3xl opacity-60"></div>
                        <div class="absolute -bottom-8 -right-8 w-72 h-72 bg-accent-100 rounded-full blur-3xl opacity-60"></div>
                        <div class="relative bg-white rounded-card shadow-card p-8 border border-gray-100">
                            <h3 class="font-semibold mb-4">Why FindURSCH?</h3>
                            <ul class="space-y-3 text-sm text-gray-700">
                                <li class="flex items-start gap-3"><span class="w-5 h-5 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs">✓</span> Verified schools only</li>
                                <li class="flex items-start gap-3"><span class="w-5 h-5 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs">✓</span> Real parent reviews</li>
                                <li class="flex items-start gap-3"><span class="w-5 h-5 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs">✓</span> Side-by-side comparison</li>
                                <li class="flex items-start gap-3"><span class="w-5 h-5 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs">✓</span> Direct inquiry to schools</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SCHOOL TYPES --}}
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-normal mb-3" style="font-family: 'DM Serif Display', serif;">Browse by School Type</h2>
                <p class="text-gray-600">Find the right level of education for your child</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                @foreach($schoolTypes as $type)
                    <a href="{{ route('schools.index', ['type' => $type->id]) }}" class="group text-center p-5 rounded-card bg-white border border-gray-100 hover:border-primary hover:shadow-card transition">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-primary-50 flex items-center justify-center text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 10 3 12 0v-5"/></svg>
                        </div>
                        <h3 class="font-semibold text-sm group-hover:text-primary">{{ $type->name }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FEATURED SCHOOLS --}}
    @if($featuredSchools->count() > 0)
    <section class="py-16 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-3xl font-normal mb-3" style="font-family: 'DM Serif Display', serif;">Featured Schools</h2>
                    <p class="text-gray-600">Explore school profiles across Uganda, including clearly labelled sample listings</p>
                </div>
                <a href="{{ route('schools.index') }}" class="text-primary font-semibold hover:underline">View all →</a>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredSchools as $school)
                    @include('components.school-card', ['school' => $school])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- DISTRICTS --}}
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-normal mb-3" style="font-family: 'DM Serif Display', serif;">Browse by District</h2>
                <p class="text-gray-600">Find schools in your area</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($districts as $district)
                    <a href="{{ route('district.show', $district) }}" class="group p-5 rounded-card bg-white border border-gray-100 hover:border-primary hover:shadow-card transition">
                        <h3 class="font-semibold group-hover:text-primary">{{ $district->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $district->region?->name }} Region</p>
                        <p class="text-xs text-gray-400 mt-2">{{ $district->school_count }} {{ Str::plural('school', $district->school_count) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ARTICLES --}}
    @if($articles->count() > 0)
    <section class="py-16 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-3xl font-normal mb-3" style="font-family: 'DM Serif Display', serif;">Latest Articles</h2>
                    <p class="text-gray-600">Education insights, tips, and news</p>
                </div>
                <a href="{{ route('articles.index') }}" class="text-primary font-semibold hover:underline">View all →</a>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach($articles as $article)
                    <article class="bg-white rounded-card border border-gray-100 overflow-hidden hover:shadow-card transition">
                        <a href="{{ route('articles.show', $article) }}" class="block">
                            <div class="aspect-video bg-gradient-to-br from-primary-100 to-accent-50"></div>
                            <div class="p-5">
                                <span class="text-xs font-semibold text-primary uppercase tracking-wider">{{ $article->category?->name }}</span>
                                <h3 class="font-semibold mt-2 line-clamp-2">{{ $article->title }}</h3>
                                <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $article->excerpt }}</p>
                                <p class="text-xs text-gray-400 mt-3">{{ $article->read_time_minutes }} min read</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section class="py-16 bg-gradient-to-br from-primary to-primary-700 text-white">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-normal mb-4" style="font-family: 'DM Serif Display', serif;">Own a school? Get listed today</h2>
            <p class="text-lg opacity-90 mb-8">Reach thousands of Ugandan parents actively searching for schools</p>
            <a href="{{ route('register') }}" class="inline-block bg-accent text-white rounded-btn px-8 py-3 font-semibold hover:bg-accent-600 transition">List Your School</a>
        </div>
    </section>
</div>
