<div class="school-directory-reference">
    <a class="rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

    <section class="phead">
        <div class="wrap">
            <div>
                <nav class="crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Home</a><span aria-hidden="true">›</span>Schools
                </nav>
                <h1>Find a school in Uganda</h1>
                <p>Discover <b>{{ number_format($allSchoolCount) }}</b> verified nursery, primary, secondary, vocational and international schools across all districts of Uganda. Compare by location, curriculum and fees.</p>
            </div>
            <a href="{{ route('contact') }}" class="ad top">
                Your advert here
                <small>728 × 90</small>
            </a>
        </div>
    </section>

    <section class="sband">
        <div class="wrap">
            <form class="sform" wire:submit="applyFilters">
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="Search school name" aria-label="Search school name">
                <select wire:model.live="district" aria-label="District">
                    <option value="">All districts</option>
                    @foreach($allDistricts as $districtOption)
                        <option value="{{ $districtOption->id }}">{{ $districtOption->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="region" aria-label="Region">
                    <option value="">All regions</option>
                    @foreach($regions as $regionOption)
                        <option value="{{ $regionOption->id }}">{{ $regionOption->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="curriculum" aria-label="Curriculum">
                    <option value="">All curricula</option>
                    @foreach($curricula as $curriculumOption)
                        <option value="{{ $curriculumOption->id }}">{{ $curriculumOption->abbreviation }}</option>
                    @endforeach
                </select>
                <button class="go" type="submit" wire:loading.attr="disabled" wire:target="applyFilters">
                    <span wire:loading.remove wire:target="applyFilters">Search</span>
                    <span wire:loading wire:target="applyFilters">Searching…</span>
                </button>
            </form>
        </div>
    </section>

    <main class="wrap">
        <div class="cols" x-data="{ filtersOpen: false }">
            <aside id="school-filters" x-ref="schoolFilters" :class="{ 'open': filtersOpen }" aria-label="Filter schools">
                <section class="card box mf">
                    <h5>More filters</h5>
                    <div class="mfb">
                        <label>Region
                            <select wire:model.live="region" aria-label="Filter by region">
                                <option value="">All regions</option>
                                @foreach($regions as $regionOption)
                                    <option value="{{ $regionOption->id }}">{{ $regionOption->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Curriculum
                            <select wire:model.live="curriculum" aria-label="Filter by curriculum">
                                <option value="">Any curriculum</option>
                                @foreach($curricula as $curriculumOption)
                                    <option value="{{ $curriculumOption->id }}">{{ $curriculumOption->abbreviation }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </section>
                <section class="card box">
                    <h5>Category</h5>
                    <ul class="cats">
                        <li>
                            <button type="button" wire:click="$set('type', '')" class="{{ $type === '' ? 'on' : '' }}">
                                <i style="--c:#0c5a49"></i><span>All institutions</span><em>{{ number_format($allSchoolCount) }}</em>
                            </button>
                        </li>
                        @foreach($schoolTypes as $schoolType)
                            <li>
                                <button type="button" wire:click="selectType({{ $schoolType->id }})" class="{{ (string) $type === (string) $schoolType->id ? 'on' : '' }}">
                                    <i style="--c:{{ ['#2f6fd6','#2f9e5a','#e08a1e','#8a5bd6','#d6479a','#6b7f78','#c9a46a'][$loop->index % 7] }}"></i>
                                    <span>{{ $schoolType->name }}</span>
                                    <em>{{ number_format($schoolType->public_listing_count) }}</em>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="card box">
                    <h5>District</h5>
                    <div class="dist">
                        @forelse($districts as $districtOption)
                            <label>
                                <input type="checkbox" wire:click="selectDistrict({{ $districtOption->id }})" @checked((string) $district === (string) $districtOption->id)>
                                <span>{{ $districtOption->name }}</span>
                                <em>{{ number_format($districtOption->public_listing_count) }}</em>
                            </label>
                        @empty
                            <p class="empty-note">District filters appear as schools are listed.</p>
                        @endforelse
                    </div>
                    @if($district !== '')
                        <button type="button" class="clear-filter" wire:click="$set('district', '')">Clear district filter</button>
                    @endif
                </section>

                <a class="ad side" href="{{ route('contact') }}">
                    Your advert here
                    <small>300 × 250</small>
                </a>

                <section class="card box">
                    <h5>Fee range (UGX)</h5>
                    <div class="fee">
                        <div class="mm"><span>0</span><span>2,000,000+</span></div>
                        <input type="range" min="0" max="2000000" step="50000" wire:model.live="maxFee" aria-label="Maximum fee per term">
                        <p>Up to UGX {{ number_format((int) $maxFee) }}</p>
                        <button type="button" class="btn btn-brand btn-block" wire:click="applyFeeFilter">Apply</button>
                        @if($feeFilterEnabled)
                            <button type="button" class="clear-filter" wire:click="clearFeeFilter">Remove fee filter</button>
                        @else
                            <small>Only schools with published UGX tuition fees per term are included when applied.</small>
                        @endif
                    </div>
                </section>

                <section class="promo2">
                    <small>Advertise</small>
                    <h3>Put your brand in front of parents and students</h3>
                    <p>Reach families across Uganda every month.</p>
                    <ul><li>Banner placements</li><li>School spotlights</li><li>Featured school videos</li></ul>
                    <a class="btn btn-gold" href="{{ route('contact') }}">Advertise with us</a>
                </section>
            </aside>

            <section class="results" aria-label="School results">
                <div class="tool">
                    <span>Showing <b>{{ number_format($schools->total()) }}</b> results · page {{ $schools->currentPage() }} of {{ $schools->lastPage() }}</span>
                    <div class="r">
                        <button type="button" class="fbtn"
                            @click="filtersOpen = !filtersOpen; if (filtersOpen) $nextTick(() => $refs.schoolFilters.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
                            :aria-expanded="filtersOpen.toString()" aria-controls="school-filters">Filters</button>
                        <label class="sort-label"><span>Sort:</span>
                            <select wire:model.live="sort" aria-label="Sort schools">
                                <option value="newest">Newest first</option>
                                <option value="featured">Featured first</option>
                                <option value="name">Name A–Z</option>
                                <option value="fee_low">Fees: low to high</option>
                                <option value="fee_high">Fees: high to low</option>
                                <option value="rating">Highest rated</option>
                            </select>
                        </label>
                        <div class="view-toggle" aria-label="Results layout">
                            <button type="button" wire:click="setViewMode('grid')" class="{{ $viewMode === 'grid' ? 'on' : '' }}" aria-label="Grid view" aria-pressed="{{ $viewMode === 'grid' ? 'true' : 'false' }}">▦</button>
                            <button type="button" wire:click="setViewMode('list')" class="{{ $viewMode === 'list' ? 'on' : '' }}" aria-label="List view" aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}">☰</button>
                        </div>
                    </div>
                </div>

                @if($schools->isNotEmpty())
                    <div class="grid {{ $viewMode === 'list' ? 'list' : '' }}" wire:loading.class="is-loading" wire:target="district,region,type,curriculum,sort,search,maxFee,applyFeeFilter">
                        @foreach($schools as $school)
                            <article class="sc" wire:key="school-result-{{ $school->id }}">
                                <a class="ph {{ $school->getFirstMediaUrl('cover') ? '' : 'nop' }}" href="{{ route('schools.show', $school) }}">
                                    @if($school->getFirstMediaUrl('cover'))
                                        <img src="{{ $school->getFirstMediaUrl('cover') }}" alt="{{ $school->name }}" loading="lazy">
                                    @else
                                        <span class="placeholder-mark">{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                                    @endif
                                    @if($school->is_featured)<span class="pop">Featured</span>@endif
                                </a>
                                <div class="in">
                                    <div class="b">
                                        <div class="t">
                                            <span class="pill">{{ $school->type?->name ?? 'School' }}</span>
                                            @foreach($school->curricula->take(1) as $curriculumItem)
                                                <span class="pill g">{{ $curriculumItem->abbreviation }}</span>
                                            @endforeach
                                            @if($school->verification_status === 'verified')
                                                <span class="pill verified">Verified</span>
                                            @endif
                                        </div>
                                        <h3><a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a></h3>
                                        <p class="loc">
                                            {{ $school->district?->name ?? 'Uganda' }}@if($school->district?->region) · {{ $school->district->region->name }}@endif
                                        </p>
                                    </div>
                                    <div class="f">
                                        <div class="chips">
                                            <span>{{ match($school->boarding_type) { 'boarding' => 'Boarding', 'mixed' => 'Day & boarding', default => 'Day school' } }}</span>
                                            @if($school->created_at?->greaterThan(now()->subDays(14)))<em>New</em>@endif
                                        </div>
                                        @if($school->lowest_term_tuition !== null)
                                            <b>UGX {{ number_format((float) $school->lowest_term_tuition) }} / term</b>
                                        @endif
                                        <a class="vp" href="{{ route('schools.show', $school) }}">View profile</a>
                                    </div>
                                </div>
                            </article>
                            @if($loop->iteration === 6)
                                <div class="promo span">
                                    <div><small>Premium schools</small><h3>Be the first school parents see</h3><p>Top of search, homepage feature and spotlight adverts.</p></div>
                                    <a class="btn btn-brand" href="{{ route('contact') }}">See school plans</a>
                                </div>
                            @endif
                            @if($loop->iteration === 12)
                                <div class="promo span alt-promo">
                                    <div><small>For students</small><h3>UNEB past papers with answers</h3><p>PLE, UCE and UACE papers to download and revise.</p></div>
                                    <a class="btn btn-gold" href="{{ route('contact') }}">Get past papers</a>
                                </div>
                            @endif
                            @if($loop->iteration === 15)
                                <div class="big span">
                                    <div><small>Opportunities</small><h3>Scholarships and bursaries open now</h3><p>Funding for primary, secondary, university and TVET.</p></div>
                                    <a class="btn btn-gold" href="{{ route('scholarships.index') }}">Find scholarships</a>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="pagination pg">
                        {{ $schools->onEachSide(1)->links() }}
                    </div>
                @else
                    <div class="no-results">
                        <span>⌕</span><h2>No schools found</h2>
                        <p>Try changing your search or clearing one or more filters.</p>
                        <button type="button" class="btn btn-brand" wire:click="resetFilters">Clear all filters</button>
                    </div>
                @endif
            </section>
        </div>
    </main>
</div>
