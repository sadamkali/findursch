<div class="district-directory-reference">
    <a class="district-rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>
    <x-site-page-hero
        class="district-page-hero"
        eyebrow="{{ $district->region?->name }} Region · School directory"
        :title="'Schools in '.$district->name.' District'"
        :description="'Discover active, verified schools in '.$district->name.' District. Compare school levels, published fees and admission information, then visit each profile to contact the school.'"
        :meta="number_format($totalSchools).' verified schools in '.$district->name"
        :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Schools', 'url' => route('schools.index')], ['label' => $district->name.' District']]"
    >
            <div class="district-stats">
                <div><b>{{ number_format($totalSchools) }}</b><span>Verified schools</span></div>
                <div><b>{{ number_format($admissionsOpenCount) }}</b><span>Admissions open</span></div>
                <div><b>{{ number_format($boardingCount) }}</b><span>Boarding options</span></div>
                <div>
                    <b>{{ $averageTermFee !== null ? 'UGX '.number_format((float) $averageTermFee) : '—' }}</b>
                    <span>Average published starting tuition / term</span>
                </div>
            </div>

            <div class="district-areas">
                <span class="district-area-label">BROWSE AREAS</span>
                <div class="district-area-list" role="group" aria-label="Filter schools by town or area">
                    <button type="button" class="{{ $town === '' ? 'is-active' : '' }}" wire:click="setTown('')">
                        All areas <em>{{ number_format($totalSchools) }}</em>
                    </button>
                    @foreach($areas as $area)
                        <button type="button" class="{{ $town === $area->name ? 'is-active' : '' }}" wire:click="setTown(@js($area->name))">
                            {{ $area->name }} <em>{{ number_format($area->total) }}</em>
                        </button>
                    @endforeach
                    @if($areas->isEmpty())
                        <span class="district-no-areas">Town and area details have not yet been added to public school profiles.</span>
                    @endif
                </div>
            </div>
    </x-site-page-hero>

    <main class="district-wrap district-main">
        <div class="district-layout">
            <section class="district-results" aria-label="Schools in {{ $district->name }} District">
                <form class="district-filters" wire:submit="applyFilters">
                    <label class="district-search">
                        <span aria-hidden="true">⌕</span>
                        <input type="search" wire:model.live.debounce.350ms="search" placeholder="Search school, town or area" aria-label="Search schools in this district">
                    </label>

                    <label class="district-select">
                        <span class="sr-only">School level</span>
                        <select wire:model.live="schoolType" aria-label="Filter by school level">
                            <option value="">All levels</option>
                            @foreach($schoolTypes as $schoolTypeOption)
                                <option value="{{ $schoolTypeOption->id }}">{{ $schoolTypeOption->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="district-select">
                        <span class="sr-only">Day or boarding</span>
                        <select wire:model.live="boarding" aria-label="Filter by day or boarding">
                            <option value="">Day or boarding</option>
                            <option value="day">Day</option>
                            <option value="boarding">Boarding</option>
                            <option value="mixed">Day &amp; boarding</option>
                        </select>
                    </label>

                    <button class="district-btn district-btn-brand" type="submit" wire:loading.attr="disabled" wire:target="applyFilters">
                        <span wire:loading.remove wire:target="applyFilters">Search</span>
                        <span wire:loading wire:target="applyFilters">Searching…</span>
                    </button>
                </form>

                <div class="district-filter-bottom">
                    <label class="district-check">
                        <input type="checkbox" wire:model.live="admissionsOpen">
                        <span>Admissions open</span>
                    </label>
                    @if($search !== '' || $town !== '' || $schoolType !== '' || $boarding !== '' || $admissionsOpen)
                        <button type="button" class="district-clear" wire:click="clearFilters">Clear all filters</button>
                    @endif
                </div>

                <div class="district-toolbar">
                    <div>
                        <small>PUBLIC SCHOOL LISTINGS</small>
                        <strong>
                            @if($filteredCount === 0)
                                No matching schools
                            @else
                                Showing {{ number_format($schools->firstItem()) }}–{{ number_format($schools->lastItem()) }} of {{ number_format($filteredCount) }} schools
                            @endif
                        </strong>
                    </div>
                    <label class="district-sort">
                        <span>Sort by</span>
                        <select wire:model.live="sort" aria-label="Sort schools">
                            <option value="rating">Top rated</option>
                            <option value="fee_low">Fees: low to high</option>
                            <option value="name">Name A to Z</option>
                        </select>
                    </label>
                </div>

                @if($schools->isNotEmpty())
                    <div class="district-school-grid">
                        @foreach($schools as $school)
                            <article class="district-school-card" wire:key="district-school-{{ $school->id }}">
                                @if($school->getFirstMediaUrl('cover'))
                                    <a class="district-school-cover" href="{{ route('schools.show', $school) }}">
                                        <img src="{{ $school->getFirstMediaUrl('cover') }}" alt="{{ $school->name }}" loading="lazy" decoding="async">
                                    </a>
                                @endif

                                <div class="district-school-body">
                                    <div class="district-school-tags">
                                        @if($school->type?->name)
                                            <span class="district-tag district-tag-type">{{ $school->type->name }}</span>
                                        @endif
                                        <span class="district-tag district-tag-verified">✓ Verified</span>
                                        @if($school->is_featured)
                                            <span class="district-tag district-tag-featured">Featured</span>
                                        @endif
                                    </div>

                                    <h2><a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a></h2>
                                    @if($school->tagline)
                                        <p class="district-school-tagline">{{ $school->tagline }}</p>
                                    @endif

                                    <div class="district-school-location">
                                        <span aria-hidden="true">⌖</span>
                                        {{ collect([$school->town, $school->subCounty?->name, $school->county?->name])->filter()->unique()->implode(' · ') ?: $district->name }}
                                    </div>

                                    <div class="district-school-facts">
                                        <span>{{ match($school->boarding_type) { 'boarding' => 'Boarding', 'mixed' => 'Day & boarding', default => 'Day' } }}</span>
                                        @if($school->gender_policy)
                                            <span>{{ ucfirst($school->gender_policy) }}</span>
                                        @endif
                                        @if($school->admissions_open)
                                            <span class="district-admissions-open">Admissions open</span>
                                        @endif
                                    </div>

                                    @if($school->curricula->isNotEmpty())
                                        <div class="district-curricula">
                                            @foreach($school->curricula->take(3) as $curriculum)
                                                <span>{{ $curriculum->abbreviation ?: $curriculum->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="district-school-meta">
                                        <div class="district-rating">
                                            @if((float) $school->rating_average > 0)
                                                <span aria-hidden="true">★</span>
                                                <b>{{ number_format((float) $school->rating_average, 1) }}</b>
                                                <small>({{ number_format((int) $school->reviews_count) }} reviews)</small>
                                            @else
                                                <small>No reviews yet</small>
                                            @endif
                                        </div>
                                        <div class="district-fee">
                                            @if($school->lowest_term_tuition !== null)
                                                <b>UGX {{ number_format((float) $school->lowest_term_tuition) }}</b>
                                                <small>starting tuition / term</small>
                                            @elseif($school->fees_from !== null)
                                                <b>UGX {{ number_format((float) $school->fees_from) }}</b>
                                                <small>published starting fee</small>
                                            @else
                                                <small>Fees not published</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="district-school-footer">
                                    <a class="district-profile-link" href="{{ route('schools.show', $school) }}">View school profile <span aria-hidden="true">→</span></a>
                                    <button type="button" x-data @click.stop.prevent="window.dispatchEvent(new CustomEvent('findursch:compare-add', { detail: @js($school->slug) }))">
                                        Add to compare
                                    </button>
                                </div>
                            </article>

                            @if($loop->iteration === 6)
                                <div class="district-list-ad"><x-site-ad-banner placement="inlist" /></div>
                            @endif
                        @endforeach
                    </div>

                    <div class="district-pagination">{{ $schools->onEachSide(1)->links() }}</div>
                @else
                    <div class="district-empty">
                        <span aria-hidden="true">⌕</span>
                        <small>NO MATCHING LISTINGS</small>
                        <h2>No schools match these filters</h2>
                        <p>Try another search term or area, or clear your filters to see all public school listings in {{ $district->name }} District.</p>
                        <button type="button" class="district-btn district-btn-brand" wire:click="clearFilters">Clear filters</button>
                    </div>
                @endif

                <section class="district-faq">
                    <div class="district-section-heading">
                        <small>LOCAL SCHOOL GUIDE</small>
                        <h2>Questions about schools in {{ $district->name }}</h2>
                    </div>
                    @foreach($faqs as $faq)
                        <details class="district-faq-item">
                            <summary>{{ $faq['question'] }}</summary>
                            <p>{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </section>
            </section>

            <aside class="district-sidebar" aria-label="Explore more schools">
                <section class="district-side-card">
                    <h3>Nearby districts</h3>
                    @if($nearbyDistricts->isNotEmpty())
                        <ul>
                            @foreach($nearbyDistricts as $nearby)
                                <li>
                                    <a href="{{ route('district.show', $nearby) }}">
                                        <span>{{ $nearby->name }}</span>
                                        <em>{{ number_format($nearby->public_listing_count) }}</em>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="district-side-empty">There are no other districts with public school listings in this region yet.</p>
                    @endif
                </section>

                <section class="district-side-card">
                    <h3>Browse by school level</h3>
                    <ul>
                        @foreach($schoolTypes as $schoolTypeOption)
                            <li>
                                <button type="button" class="{{ $schoolType === (string) $schoolTypeOption->id ? 'is-active' : '' }}" wire:click="setSchoolType('{{ $schoolTypeOption->id }}')">
                                    <span>{{ $schoolTypeOption->name }}</span>
                                    <em>{{ number_format($schoolTypeOption->public_listing_count) }}</em>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    @if($schoolType !== '')
                        <button type="button" class="district-side-clear" wire:click="setSchoolType('')">Show all school levels</button>
                    @endif
                </section>

                <x-site-ad-banner placement="side" />

                <section class="district-promo">
                    <small>FOR SCHOOL OWNERS</small>
                    <h3>Is your school in {{ $district->name }}?</h3>
                    <p>Register your school on FindURSCH so parents can discover its profile and published information.</p>
                    <a class="district-btn district-btn-gold" href="{{ route('register') }}">List your school</a>
                </section>

                <section class="district-side-help">
                    <h3>Need help finding a school?</h3>
                    <p>Explore the full school directory or contact the FindURSCH team for assistance.</p>
                    <a href="{{ route('schools.index') }}">Browse all schools →</a>
                    <a href="{{ route('contact') }}">Contact FindURSCH →</a>
                </section>
            </aside>
        </div>
    </main>
</div>
