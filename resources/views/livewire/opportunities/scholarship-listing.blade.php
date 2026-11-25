<div class="findursch-scholarships">
    <a class="rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

    <section class="phead">
        <div class="wrap">
            <div class="phead-copy">
                <nav class="crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Home</a>
                    <span aria-hidden="true">›</span>
                    <b>Scholarships &amp; bursaries</b>
                </nav>
                <p class="phead-eyebrow">Education funding in Uganda</p>
                <h1>Scholarships &amp; <em>Bursaries</em> in Uganda</h1>
                <p class="phead-intro">Explore school bursaries and published funding opportunities for Ugandan learners. <b>{{ number_format($openCount) }}</b> open now.</p>
            </div>
            <x-site-ad-banner placement="top" />
        </div>
    </section>

    <main class="wrap">
        <div class="pills" role="tablist" aria-label="Scholarship categories">
            <button type="button" class="{{ $category === 'all' ? 'on' : '' }}" wire:click="setCategory('all')" aria-selected="{{ $category === 'all' ? 'true' : 'false' }}">All scholarships</button>
            <button type="button" class="{{ $category === 'primary' ? 'on' : '' }}" wire:click="setCategory('primary')" aria-selected="{{ $category === 'primary' ? 'true' : 'false' }}">Primary</button>
            <button type="button" class="{{ $category === 'secondary' ? 'on' : '' }}" wire:click="setCategory('secondary')" aria-selected="{{ $category === 'secondary' ? 'true' : 'false' }}">Secondary</button>
            <button type="button" class="{{ $category === 'government' ? 'on' : '' }}" wire:click="setCategory('government')" aria-selected="{{ $category === 'government' ? 'true' : 'false' }}">Government</button>
            <button type="button" class="{{ $category === 'university' ? 'on' : '' }}" wire:click="setCategory('university')" aria-selected="{{ $category === 'university' ? 'true' : 'false' }}">University</button>
            <button type="button" class="{{ $category === 'international' ? 'on' : '' }}" wire:click="setCategory('international')" aria-selected="{{ $category === 'international' ? 'true' : 'false' }}">International</button>
            <button type="button" class="{{ $category === 'foundations' ? 'on' : '' }}" wire:click="setCategory('foundations')" aria-selected="{{ $category === 'foundations' ? 'true' : 'false' }}">Foundations</button>
            <button type="button" class="{{ $category === 'tvet' ? 'on' : '' }}" wire:click="setCategory('tvet')" aria-selected="{{ $category === 'tvet' ? 'true' : 'false' }}">TVET</button>
            <button type="button" class="{{ $category === 'other' ? 'on' : '' }}" wire:click="setCategory('other')" aria-selected="{{ $category === 'other' ? 'true' : 'false' }}">School bursaries</button>
            <button type="button" class="{{ $category === 'videos' ? 'on' : '' }}" wire:click="setCategory('videos')" aria-selected="{{ $category === 'videos' ? 'true' : 'false' }}">School videos</button>
        </div>

        <div class="bar">
            <input type="search" wire:model.live.debounce.350ms="search" placeholder="Search scholarships, schools or courses" aria-label="Search scholarships">
            <button class="btn btn-brand" type="button" wire:click="$refresh">Search</button>
            <select wire:model.live="category" aria-label="Category">
                <option value="all">All categories</option>
                <option value="primary">Primary school</option>
                <option value="secondary">Secondary school</option>
                <option value="government">Government</option>
                <option value="university">University</option>
                <option value="international">International</option>
                <option value="foundations">NGO &amp; foundations</option>
                <option value="tvet">TVET &amp; vocational</option>
                <option value="other">Other school bursaries</option>
                <option value="videos">School videos</option>
            </select>
            <select wire:model.live="deadlineFilter" aria-label="Deadline">
                <option value="all">Any deadline</option>
                <option value="open">Open now</option>
                <option value="closed">Closed</option>
            </select>
            <a class="btn btn-gold" href="{{ route('contact') }}">+ Post a scholarship</a>
        </div>

        <div class="note" role="note">
            <i>!</i>
            <div>
                <small>Scholarship safety notice</small>
                <b>Never pay anyone to receive or secure a scholarship.</b>
                <p>FindURSCH does not sell or guarantee scholarships. Be careful with anyone who asks for application, processing, registration or release fees. Check directly with the publishing school or provider before sending money.</p>
            </div>
        </div>

        @if($category === 'all')
            <div class="result-summary">
                <span>Published opportunities</span>
                <strong>{{ number_format($totalMatches) }} {{ \Illuminate\Support\Str::plural('opportunity', $totalMatches) }}</strong>
            </div>

            @foreach($groups as $group)
                <section class="sec category-section" id="category-{{ $group['key'] }}">
                    <div class="head cat">
                        <h2>{{ $group['heading'] }}</h2>
                        <span class="group-count">{{ number_format($group['count']) }} available</span>
                    </div>

                    @if($group['items']->isNotEmpty())
                        <div class="news">
                            @foreach($group['items'] as $item)
                                <a class="card sch {{ $loop->first ? 'lg' : '' }}" href="{{ route('scholarships.show', $item) }}">
                                    <div class="im">
                                        <div class="ph">
                                            @if($item->school?->getFirstMediaUrl('cover'))
                                                <img src="{{ $item->school->getFirstMediaUrl('cover') }}" alt="{{ $item->school->name }}" loading="lazy">
                                            @else
                                                <span class="ph-mark">{{ strtoupper(mb_substr($item->school?->name ?? 'FS', 0, 2)) }}</span>
                                            @endif
                                        </div>
                                        <span class="flag">School bursary</span>
                                    </div>
                                    <div class="b">
                                        <h3>{{ $item->title }}</h3>
                                        <p class="org">{{ $item->school?->name ?? 'Participating school' }}</p>
                                        <p class="lv">{{ $group['label'] }} · {{ $item->award ?: 'Award details available from the school' }}</p>
                                        <p class="dl {{ $item->deadline && $item->deadline->isBefore(today()) ? 'cl' : 'op' }}">
                                            @if($item->deadline)
                                                {{ $item->deadline->isBefore(today()) ? 'Deadline passed' : 'Deadline' }}: {{ $item->deadline->format('d F Y') }}
                                            @else
                                                Contact school for deadline details
                                            @endif
                                        </p>
                                        <p class="desc">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 150) }}</p>
                                    </div>
                                    <div class="f"><span>{{ $item->school?->district?->name ?? 'Uganda' }}</span><b>Details →</b></div>
                                </a>
                            @endforeach
                        </div>
                        @if($group['count'] > 3)
                            <div class="view-more-row">
                                <button type="button" class="more" wire:click="setCategory('{{ $group['key'] }}')">View all {{ strtolower($group['label']) }} opportunities →</button>
                            </div>
                        @endif
                    @else
                        <div class="category-empty">
                            <span class="empty-mark">✦</span>
                            <div>
                                <h3>No published opportunities in this category yet</h3>
                                <p>When a school or provider publishes an eligible opportunity, it will appear here. Always confirm details and deadlines with the publisher.</p>
                            </div>
                        </div>
                    @endif
                </section>

                @if($loop->iteration === 3)
                    <div class="offer">
                        <div class="ph offer-mark"><span>FS</span></div>
                        <div>
                            <h3>Offering a scholarship? Reach the right students</h3>
                            <p>Contact FindURSCH to discuss publishing your scholarship, bursary or sponsorship for students and families across Uganda.</p>
                            <a class="btn btn-brand" href="{{ route('contact') }}">Submit an opportunity</a>
                        </div>
                    </div>
                    <div class="strip">
                        <div>
                            <small>For school communities</small>
                            <h4>Is your school on FindURSCH?</h4>
                            <p>Help parents discover your school and its published opportunities.</p>
                        </div>
                        <a class="btn btn-gold" href="{{ route('register') }}">List your school</a>
                    </div>
                @endif

                @if($loop->iteration === 4)
                    <div class="duo">
                        <div class="band">
                            <h3>Stay informed about scholarship opportunities</h3>
                            <p>Ask FindURSCH about updates to published bursaries and scholarship listings.</p>
                            <a class="btn btn-gold" href="{{ route('contact') }}">Request updates</a>
                        </div>
                        <div class="band dark">
                            <h3>Offering a scholarship?</h3>
                            <p>Share the details with our team so we can advise on publishing and verification.</p>
                            <a class="btn btn-gold" href="{{ route('contact') }}">Contact FindURSCH</a>
                        </div>
                    </div>
                @endif

                @if($loop->iteration === 6)
                    <x-site-ad-banner placement="inlist" />

                    <section class="sec video-section">
                        <div class="head cat">
                            <h2>School videos</h2>
                            <span class="group-count">{{ number_format($videoCount) }} published</span>
                        </div>
                        @if($videos->isNotEmpty())
                            <div class="vids">
                                @foreach($videos as $video)
                                    <a class="card vc" href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer">
                                        <div class="ph vph">
                                            <img src="https://img.youtube.com/vi/{{ rawurlencode($video->youtube_video_id) }}/hqdefault.jpg" alt="" loading="lazy">
                                            <i class="play"></i>
                                        </div>
                                        <div class="b">
                                            <h3>{{ $video->title }}</h3>
                                            <small>{{ $video->school?->name ?? 'School community' }}</small>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="video-empty">
                                <span class="empty-mark">▶</span>
                                <div><h3>School videos will appear here</h3><p>Approved videos shared by participating schools will be featured in this section.</p></div>
                            </div>
                        @endif
                    </section>
                @endif
            @endforeach

            <div class="banner" aria-label="Sponsored banners">
                <x-site-ad-banner placement="foot1" />
                <x-site-ad-banner placement="foot2" />
            </div>
        @elseif($category === 'videos')
            <section class="sec video-section">
                <div class="head cat">
                    <h2>School videos</h2>
                    <span class="group-count">{{ number_format($videoCount) }} published</span>
                </div>
                @if($videos->isNotEmpty())
                    <div class="vids">
                        @foreach($videos as $video)
                            <a class="card vc" href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer">
                                <div class="ph vph">
                                    <img src="https://img.youtube.com/vi/{{ rawurlencode($video->youtube_video_id) }}/hqdefault.jpg" alt="" loading="lazy">
                                    <i class="play"></i>
                                </div>
                                <div class="b"><h3>{{ $video->title }}</h3><small>{{ $video->school?->name ?? 'School community' }}</small></div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="video-empty">
                        <span class="empty-mark">▶</span>
                        <div><h3>School videos will appear here</h3><p>Approved videos shared by participating schools will be featured in this section.</p></div>
                    </div>
                @endif
            </section>
        @else
            <section class="sec">
                <div class="head cat">
                    <h2>{{ $selectedCategory['heading'] }}</h2>
                    <span class="group-count">{{ number_format($selectedScholarships->total()) }} available</span>
                </div>
                @if($selectedScholarships->isNotEmpty())
                    <div class="news">
                        @foreach($selectedScholarships as $item)
                            <a class="card sch {{ $loop->first ? 'lg' : '' }}" href="{{ route('scholarships.show', $item) }}">
                                <div class="im">
                                    <div class="ph">
                                        @if($item->school?->getFirstMediaUrl('cover'))
                                            <img src="{{ $item->school->getFirstMediaUrl('cover') }}" alt="{{ $item->school->name }}" loading="lazy">
                                        @else
                                            <span class="ph-mark">{{ strtoupper(mb_substr($item->school?->name ?? 'FS', 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <span class="flag">School bursary</span>
                                </div>
                                <div class="b">
                                    <h3>{{ $item->title }}</h3>
                                    <p class="org">{{ $item->school?->name ?? 'Participating school' }}</p>
                                    <p class="lv">{{ $selectedCategory['label'] }} · {{ $item->award ?: 'Award details available from the school' }}</p>
                                    <p class="dl {{ $item->deadline && $item->deadline->isBefore(today()) ? 'cl' : 'op' }}">
                                        @if($item->deadline)
                                            {{ $item->deadline->isBefore(today()) ? 'Deadline passed' : 'Deadline' }}: {{ $item->deadline->format('d F Y') }}
                                        @else
                                            Contact school for deadline details
                                        @endif
                                    </p>
                                    <p class="desc">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 150) }}</p>
                                </div>
                                <div class="f"><span>{{ $item->school?->district?->name ?? 'Uganda' }}</span><b>Details →</b></div>
                            </a>
                        @endforeach
                    </div>
                    <div class="pagination">{{ $selectedScholarships->links() }}</div>
                @else
                    <div class="category-empty">
                        <span class="empty-mark">✦</span>
                        <div><h3>No published opportunities found</h3><p>Try another category or adjust your search and deadline filters. New school bursaries will appear here when published.</p></div>
                    </div>
                @endif
                <div class="view-more-row">
                    <button type="button" class="more" wire:click="setCategory('all')">← View all scholarship categories</button>
                </div>
            </section>
        @endif

        @if($search !== '' || $deadlineFilter !== 'all')
            <div class="clear-filters">
                <span>Showing results for your current filters.</span>
                <button type="button" wire:click="$set('search', '')" class="more">Clear search</button>
                <button type="button" wire:click="$set('deadlineFilter', 'all')" class="more">Any deadline</button>
            </div>
        @endif
    </main>
</div>
