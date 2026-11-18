<div class="directory-page">
    <section class="directory-heading">
        <div class="site-wrap directory-heading__inner">
            <div class="directory-heading__copy">
                <nav class="directory-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Home</a><span aria-hidden="true">›</span><span aria-current="page">Schools</span>
                </nav>
                <p class="directory-eyebrow">FindURSCH school directory</p>
                <h1>Find a school in Uganda</h1>
                <p>Discover <strong>{{ number_format($allSchoolCount) }} school listings</strong> across Uganda. Compare location, education level, curriculum, boarding arrangements and published tuition fees.</p>
            </div>
            <a href="{{ route('contact') }}" class="directory-ad directory-ad--top">
                <span>Promote your school</span>
                <strong>Reach more families on FindurSch</strong>
                <small>Contact us about school promotion</small>
            </a>
        </div>
    </section>

    <section class="directory-search-band">
        <div class="site-wrap">
            <form class="directory-search" wire:submit="applyFilters">
                <label class="directory-search__keyword">
                    <span class="sr-only">Search school or district</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
                    <input type="search" wire:model.live.debounce.350ms="search" placeholder="Search school name or district" autocomplete="off">
                </label>
                <label class="directory-search__select">
                    <span class="sr-only">District</span>
                    <select wire:model.live="district" aria-label="District">
                        <option value="">All districts</option>
                        @foreach($allDistricts as $districtOption)
                            <option value="{{ $districtOption->id }}">{{ $districtOption->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="directory-search__select">
                    <span class="sr-only">Region</span>
                    <select wire:model.live="region" aria-label="Region">
                        <option value="">All regions</option>
                        @foreach($regions as $regionOption)
                            <option value="{{ $regionOption->id }}">{{ $regionOption->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="directory-search__select">
                    <span class="sr-only">Curriculum</span>
                    <select wire:model.live="curriculum" aria-label="Curriculum">
                        <option value="">All curricula</option>
                        @foreach($curricula as $curriculumOption)
                            <option value="{{ $curriculumOption->id }}">{{ $curriculumOption->abbreviation }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="btn btn-search" wire:loading.attr="disabled" wire:target="applyFilters">
                    <span wire:loading.remove wire:target="applyFilters">Search <span aria-hidden="true">→</span></span>
                    <span wire:loading wire:target="applyFilters">Searching…</span>
                </button>
            </form>
        </div>
    </section>

    <main class="site-wrap directory-content">
        <div class="directory-layout">
            <aside class="directory-sidebar" aria-label="Filter school listings">
                <section class="directory-filter-card">
                    <div class="directory-filter-card__heading">
                        <h2>Category</h2>
                    </div>
                    <ul class="directory-category-list">
                        <li>
                            <button type="button" wire:click="$set('type', '')" class="{{ $type === '' ? 'is-active' : '' }}">
                                <i style="--category-color:#0c5a49"></i><span>All institutions</span><em>{{ number_format($allSchoolCount) }}</em>
                            </button>
                        </li>
                        @foreach($schoolTypes as $schoolType)
                            <li>
                                <button type="button" wire:click="selectType({{ $schoolType->id }})" class="{{ (string) $type === (string) $schoolType->id ? 'is-active' : '' }}">
                                    <i style="--category-color:{{ ['#2f6fd6','#2f9e5a','#e08a1e','#8a5bd6','#d6479a','#6b7f78','#c9a46a'][$loop->index % 7] }}"></i>
                                    <span>{{ $schoolType->name }}</span>
                                    <em>{{ number_format($schoolType->public_listing_count) }}</em>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="directory-filter-card">
                    <div class="directory-filter-card__heading"><h2>Popular districts</h2></div>
                    <div class="directory-district-list">
                        @forelse($districts as $districtOption)
                            <label class="{{ (string) $district === (string) $districtOption->id ? 'is-active' : '' }}">
                                <input type="checkbox" wire:click="selectDistrict({{ $districtOption->id }})" @checked((string) $district === (string) $districtOption->id)>
                                <span><strong>{{ $districtOption->name }}</strong><small>{{ $districtOption->region?->name }}</small></span>
                                <em>{{ number_format($districtOption->public_listing_count) }}</em>
                            </label>
                        @empty
                            <p class="directory-empty-note">District filters will appear when schools are listed.</p>
                        @endforelse
                    </div>
                    @if($district !== '')
                        <button type="button" wire:click="$set('district', '')" class="directory-filter-footer">Clear district filter</button>
                    @endif
                </section>

                <section class="directory-filter-card">
                    <div class="directory-filter-card__heading"><h2>School arrangement</h2></div>
                    <div class="directory-simple-filter">
                        <label for="directory-boarding">Boarding option</label>
                        <select id="directory-boarding" wire:model.live="boarding">
                            <option value="">Any arrangement</option>
                            <option value="day">Day school</option>
                            <option value="boarding">Boarding</option>
                            <option value="mixed">Day and boarding</option>
                        </select>
                        <label class="directory-verified-option">
                            <input type="checkbox" wire:model.live="verified_only">
                            <span>Verified schools only</span>
                        </label>
                    </div>
                </section>

                <a href="{{ route('contact') }}" class="directory-ad directory-ad--side">
                    <span>School promotion</span>
                    <strong>Put your school in front of parents</strong>
                    <small>Ask about featured placement</small>
                    <b class="btn btn-gold">Contact us</b>
                </a>

                <section class="directory-filter-card">
                    <div class="directory-filter-card__heading">
                        <h2>Tuition fee per term</h2>
                    </div>
                    <div class="directory-fee-filter">
                        <div class="directory-fee-filter__scale"><span>UGX 0</span><span>UGX 2,000,000+</span></div>
                        <input type="range" min="0" max="2000000" step="50000" wire:model.live="maxFee" aria-label="Maximum published tuition fee per term">
                        <p>Up to <strong>UGX {{ number_format((int) $maxFee) }}</strong></p>
                        <button type="button" wire:click="applyFeeFilter" class="btn btn-brand btn-block">Apply fee filter</button>
                        @if($feeFilterEnabled)
                            <button type="button" wire:click="clearFeeFilter" class="directory-filter-footer">Remove fee filter</button>
                        @else
                            <small>Only schools with published UGX tuition fees per term are included when applied.</small>
                        @endif
                    </div>
                </section>

                <section class="directory-promo-card">
                    <small>For school owners</small>
                    <h2>Help families discover your school</h2>
                    <p>Create a school profile and share your institution's location, programmes and contact information.</p>
                    <ul><li>School profile and photos</li><li>Curriculum and boarding details</li><li>Enquiries from families</li></ul>
                    <a href="{{ route('register') }}" class="btn btn-gold">List your school</a>
                </section>
            </aside>

            <section class="directory-results" aria-label="School search results">
                <div class="directory-toolbar">
                    <div>
                        <p class="directory-result-count">
                            Showing <strong>{{ number_format($schools->total()) }}</strong> {{ \Illuminate\Support\Str::plural('school', $schools->total()) }}
                        </p>
                        <p class="directory-result-subtitle">Use the filters to narrow your search.</p>
                    </div>
                    <div class="directory-toolbar__controls">
                        <label class="directory-sort">
                            <span>Sort:</span>
                            <select wire:model.live="sort" aria-label="Sort schools">
                                <option value="featured">Featured first</option>
                                <option value="newest">Newest first</option>
                                <option value="name">Name A–Z</option>
                                <option value="rating">Highest rated</option>
                                <option value="fee_low">Fees: low to high</option>
                                <option value="fee_high">Fees: high to low</option>
                            </select>
                        </label>
                        <div class="directory-view-toggle" aria-label="Change results layout">
                            <button type="button" wire:click="setViewMode('grid')" class="{{ $viewMode === 'grid' ? 'is-active' : '' }}" aria-label="Grid view" aria-pressed="{{ $viewMode === 'grid' ? 'true' : 'false' }}">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z"/></svg>
                            </button>
                            <button type="button" wire:click="setViewMode('list')" class="{{ $viewMode === 'list' ? 'is-active' : '' }}" aria-label="List view" aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 5h2v2H4zm4 0h12v2H8zM4 11h2v2H4zm4 0h12v2H8zM4 17h2v2H4zm4 0h12v2H8z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                @if($schools->count() > 0)
                    <div class="directory-school-grid {{ $viewMode === 'list' ? 'is-list' : '' }}" wire:loading.class="is-loading" wire:target="district,region,type,curriculum,boarding,verified_only,sort,search,maxFee,applyFeeFilter">
                        @foreach($schools as $school)
                            <article class="directory-school-card" wire:key="directory-school-{{ $school->id }}" data-reveal>
                                <a href="{{ route('schools.show', $school) }}" class="directory-school-card__image">
                                    @if($school->getFirstMediaUrl('cover'))
                                        <img src="{{ $school->getFirstMediaUrl('cover') }}" alt="{{ $school->name }}" loading="lazy">
                                    @else
                                        <span class="directory-school-placeholder">{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                                    @endif
                                    @if($school->is_featured)<b class="directory-image-badge">Featured</b>@endif
                                </a>
                                <div class="directory-school-card__content">
                                    <div class="directory-school-card__tags">
                                        <span class="directory-tag directory-tag--type">{{ $school->type?->name ?? 'School' }}</span>
                                        @if($school->is_demo)
                                            <span class="directory-tag directory-tag--sample">Sample listing</span>
                                        @elseif($school->verification_status === 'verified')
                                            <span class="directory-tag directory-tag--verified">✓ Verified</span>
                                        @endif
                                    </div>
                                    <h2><a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a></h2>
                                    @if($school->tagline)<p class="directory-school-tagline">{{ $school->tagline }}</p>@endif
                                    <p class="directory-location">{{ $school->district?->name ?? 'Uganda' }}@if($school->district?->region), {{ $school->district->region->name }}@endif</p>
                                    <div class="directory-school-facts">
                                        <span>{{ match($school->boarding_type) { 'boarding' => 'Boarding', 'mixed' => 'Day & boarding', default => 'Day school' } }}</span>
                                        @foreach($school->curricula->take(2) as $curriculumItem)
                                            <span>{{ $curriculumItem->abbreviation }}</span>
                                        @endforeach
                                        @if($school->lowest_term_tuition !== null)
                                            <strong>UGX {{ number_format((float) $school->lowest_term_tuition) }} / term</strong>
                                        @endif
                                    </div>
                                    <div class="directory-school-card__footer">
                                        <span class="directory-rating">
                                            @if((float) $school->rating_average > 0)
                                                <b>★</b> {{ number_format((float) $school->rating_average, 1) }} <small>({{ $school->reviews_count }} reviews)</small>
                                            @else
                                                <small>No reviews yet</small>
                                            @endif
                                        </span>
                                        <div class="directory-card-actions">
                                            <button type="button" class="directory-compare-button" x-data @click.stop.prevent="window.dispatchEvent(new CustomEvent('findursch:compare-add', { detail: @js($school->slug) }))">+ Compare</button>
                                            <a href="{{ route('schools.show', $school) }}" class="directory-profile-link">View profile →</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            @if($loop->iteration === 6)
                                <div class="directory-inline-promo">
                                    <div><small>For school owners</small><h3>Be easier for families to find</h3><p>List your institution and provide parents with useful school information.</p></div>
                                    <a href="{{ route('register') }}" class="btn btn-gold">List your school</a>
                                </div>
                            @elseif($loop->iteration === 12)
                                <div class="directory-inline-promo directory-inline-promo--alt">
                                    <div><small>Make an informed choice</small><h3>Compare schools before you decide</h3><p>Shortlist schools and review their published profile information side by side.</p></div>
                                    <a href="{{ route('compare') }}" class="btn btn-gold">Compare schools</a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="directory-pagination">
                        <div class="directory-pagination__summary">
                            Showing {{ number_format($schools->firstItem() ?? 0) }}–{{ number_format($schools->lastItem() ?? 0) }} of {{ number_format($schools->total()) }} schools
                        </div>
                        {{ $schools->onEachSide(1)->links() }}
                    </div>
                @else
                    <div class="directory-no-results">
                        <span aria-hidden="true">⌕</span>
                        <h2>No schools found</h2>
                        <p>Try changing your search or clearing one or more filters.</p>
                        <button type="button" wire:click="resetFilters" class="btn btn-brand">Clear all filters</button>
                    </div>
                @endif

                <div class="directory-bottom-banner">
                    <div><small>Explore more</small><h2>Find the school that fits your needs</h2><p>Browse by school type, compare your shortlist and review the information published on each school profile.</p></div>
                    <a href="{{ route('compare') }}" class="btn btn-gold">Compare schools →</a>
                </div>
            </section>
        </div>
    </main>

    <a href="{{ route('register') }}" class="directory-register-tab">Register your school</a>
</div>
