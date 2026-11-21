<div class="news-page">
    <a class="news-register-tab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

    <x-site-ad-banner placement="top" />

    <section class="news-heading">
        <h1>School <em>News &amp; Updates</em> in Uganda</h1>
        <p>Stay informed with the latest education news, school announcements, policy updates and stories from school communities across Uganda.</p>
    </section>

    <main class="news-wrap">
        <div class="news-category-row">
            <nav class="news-pills" aria-label="News categories">
                <a class="{{ empty($category) && $view !== 'videos' ? 'is-active' : '' }}" href="{{ route('news.index', array_filter(['q' => $search ?: null, 'sort' => $sort !== 'latest' ? $sort : null])) }}">All news</a>
                @foreach($categories as $item)
                    <a class="{{ (string) $category === (string) $item->id ? 'is-active' : '' }}" href="{{ route('news.index', array_filter(['category' => $item->id, 'q' => $search ?: null, 'sort' => $sort !== 'latest' ? $sort : null])) }}">{{ $item->name }}</a>
                @endforeach
                @if($videoCount > 0)
                    <a class="{{ $view === 'videos' ? 'is-active' : '' }}" href="{{ route('news.index', array_filter(['view' => 'videos', 'q' => $search ?: null])) }}">School videos</a>
                @endif
            </nav>
            <a class="news-digest" href="{{ route('contact') }}">Share a story</a>
        </div>

        @if($view !== 'videos')
            <form class="news-toolbar" method="GET" action="{{ route('news.index') }}" role="search">
                <input type="search" name="q" value="{{ $search }}" placeholder="Search news, topics or schools" aria-label="Search news">
                <button type="submit">Search</button>
                <select name="category" aria-label="Category">
                    <option value="">All categories</option>
                    @foreach($categories as $item)
                        <option value="{{ $item->id }}" {{ (string) $category === (string) $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
                <select name="sort" aria-label="Sort news">
                    <option value="latest" {{ $sort !== 'popular' ? 'selected' : '' }}>Latest first</option>
                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Most read</option>
                </select>
            </form>
        @else
            <form class="news-toolbar" method="GET" action="{{ route('news.index') }}" role="search">
                <input type="hidden" name="view" value="videos">
                <input type="search" name="q" value="{{ $search }}" placeholder="Search school videos or schools" aria-label="Search school videos">
                <button type="submit">Search</button>
            </form>
        @endif

        @if($view === 'videos')
            <section class="news-section">
                <div class="news-section-title"><h2>School videos</h2><span>{{ number_format($videoCount) }} published</span></div>
                @if($videos->isNotEmpty())
                    <div class="news-video-grid">
                        @foreach($videos as $video)
                            <a class="news-video-card" href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer">
                                <div class="news-video-image">
                                    <img src="https://img.youtube.com/vi/{{ rawurlencode($video->youtube_video_id) }}/hqdefault.jpg" alt="" loading="lazy">
                                    <span class="news-video-play" aria-hidden="true">▶</span>
                                </div>
                                <div><small>{{ $video->school?->name ?? 'School community' }}</small><h3>{{ $video->title }}</h3><b>Watch video ↗</b></div>
                            </a>
                        @endforeach
                    </div>
                    @if($videoCount > $videos->count())
                        <p class="news-video-note">Showing the latest {{ $videos->count() }} approved school videos.</p>
                    @endif
                @else
                    <div class="news-empty"><h3>No approved school videos yet</h3><p>Videos shared by participating schools will appear here after approval.</p><a href="{{ route('news.index') }}">Back to news</a></div>
                @endif
            </section>
        @else
            @if($leadArticle || $mostRead->isNotEmpty())
                <section class="news-top-stories" aria-label="Top news stories">
                    <div class="news-lead-grid">
                        @if($leadArticle)
                            <a class="news-lead" href="{{ route('news.show', $leadArticle) }}" @if($leadArticle->cover_image) style="--news-cover:url('{{ $leadArticle->cover_image }}')" @endif>
                                <span class="news-lead-content">
                                    <small>{{ $leadArticle->category?->name ?? 'Education news' }} · {{ $leadArticle->published_at?->format('d M Y') }}</small>
                                    <strong>{{ $leadArticle->title }}</strong>
                                    @if($leadArticle->excerpt)<span>{{ \Illuminate\Support\Str::limit($leadArticle->excerpt, 150) }}</span>@endif
                                    <b>Read full story <i aria-hidden="true">→</i></b>
                                </span>
                            </a>
                        @else
                            <div class="news-top-empty"><h3>No matching lead story</h3><p>Try another category or search term.</p></div>
                        @endif

                        @if($secondaryArticle)
                            <a class="news-secondary" href="{{ route('news.show', $secondaryArticle) }}">
                                @if($secondaryArticle->cover_image)
                                    <img src="{{ $secondaryArticle->cover_image }}" alt="" loading="lazy">
                                @else
                                    <div class="news-image-placeholder">FindURSCH</div>
                                @endif
                                <div>
                                    <small>{{ $secondaryArticle->category?->name ?? 'Education' }} · {{ $secondaryArticle->published_at?->diffForHumans() }}</small>
                                    <h2>{{ $secondaryArticle->title }}</h2>
                                    <p>{{ \Illuminate\Support\Str::limit($secondaryArticle->excerpt, 130) }}</p>
                                </div>
                            </a>
                        @else
                            <div class="news-top-empty news-top-empty--secondary"><h3>More school stories</h3><p>Newly published education updates appear here.</p></div>
                        @endif

                        @if($mostRead->isNotEmpty())
                            <aside class="news-most-read">
                                <h2>Most read today</h2>
                                <ol>
                                    @foreach($mostRead as $index => $article)
                                        <li><a href="{{ route('news.show', $article) }}"><b>{{ $index + 1 }}</b><span>{{ $article->title }}<small>{{ $article->category?->name ?? 'Education' }} · {{ $article->published_at?->format('d M Y') }}</small></span></a></li>
                                    @endforeach
                                </ol>
                            </aside>
                        @else
                            <div class="news-most-read news-most-read--empty"><h2>Most read</h2><p>Published stories will appear here as readers discover them.</p></div>
                        @endif
                    </div>
                </section>
            @endif

            @if($searchResults)
                <section class="news-section">
                    <div class="news-section-title">
                        <h2>{{ $selectedCategory?->name ?? 'News results' }}</h2>
                        <span>{{ number_format($searchResults->total()) }} stories</span>
                    </div>
                    @if($searchResults->isNotEmpty())
                        <div class="news-story-grid">
                            @foreach($searchResults as $article)
                                <a class="news-story-card" href="{{ route('news.show', $article) }}">
                                    @if($article->cover_image)<img src="{{ $article->cover_image }}" alt="" loading="lazy">@else<div class="news-image-placeholder">FindURSCH</div>@endif
                                    <div><small>{{ $article->category?->name ?? 'Education' }} · {{ $article->published_at?->format('d M Y') }}</small><h3>{{ $article->title }}</h3><p>{{ \Illuminate\Support\Str::limit($article->excerpt, 145) }}</p><b>Read story →</b></div>
                                </a>
                            @endforeach
                        </div>
                        <div class="news-pagination">{{ $searchResults->links() }}</div>
                    @else
                        <div class="news-empty"><h3>No matching news stories</h3><p>Try another search term or choose a different category.</p><a href="{{ route('news.index') }}">Clear filters</a></div>
                    @endif
                </section>
            @else
                @forelse($categorySections as $section)
                    @php
                        $sectionCategory = $section['category'];
                        $sectionArticles = $section['articles'];
                        $sectionLead = $sectionArticles->first();
                        $sectionSpotlight = $sectionArticles->skip(4)->first();
                    @endphp
                    <section class="news-section" id="news-{{ $sectionCategory->slug }}">
                        <div class="news-section-title">
                            <h2>{{ $sectionCategory->name }}</h2>
                            <a href="{{ route('news.index', ['category' => $sectionCategory->id]) }}">View more →</a>
                        </div>
                        <div class="news-category-grid">
                            @if($sectionLead)
                                <a class="news-feature-card" href="{{ route('news.show', $sectionLead) }}">
                                    @if($sectionLead->cover_image)<img src="{{ $sectionLead->cover_image }}" alt="" loading="lazy">@else<div class="news-image-placeholder">FindURSCH</div>@endif
                                    <div><small>{{ $sectionLead->published_at?->format('d M Y') }} · {{ $sectionLead->read_time_minutes }} min read</small><h3>{{ $sectionLead->title }}</h3><p>{{ \Illuminate\Support\Str::limit($sectionLead->excerpt, 155) }}</p></div>
                                </a>
                            @else
                                <div class="news-feature-card news-feature-card--empty"><div class="news-image-placeholder">FindURSCH</div><div><h3>More {{ strtolower($sectionCategory->name) }}</h3><p>New stories will appear here when published.</p></div></div>
                            @endif

                            <div class="news-row-list">
                                @forelse($sectionArticles->skip(1)->take(3) as $article)
                                    <a href="{{ route('news.show', $article) }}">
                                        @if($article->cover_image)<img src="{{ $article->cover_image }}" alt="" loading="lazy">@else<div class="news-row-placeholder">F</div>@endif
                                        <span><b>{{ $article->title }}</b><small>{{ $article->published_at?->format('d M Y') }}</small></span>
                                    </a>
                                @empty
                                    <p class="news-row-empty">There are no other stories in this section yet.</p>
                                @endforelse
                            </div>

                            @if($sectionSpotlight)
                                <a class="news-category-spotlight" href="{{ route('news.show', $sectionSpotlight) }}">
                                    @if($sectionSpotlight->cover_image)<img src="{{ $sectionSpotlight->cover_image }}" alt="" loading="lazy">@else<div class="news-image-placeholder">FindURSCH</div>@endif
                                    <span class="news-spotlight-flag">{{ $sectionSpotlight->category?->name ?? 'Education' }}</span>
                                    <div><h3>{{ $sectionSpotlight->title }}</h3><p>{{ \Illuminate\Support\Str::limit($sectionSpotlight->excerpt, 130) }}</p><small>{{ $sectionSpotlight->published_at?->format('d M Y') }} · {{ $sectionSpotlight->read_time_minutes }} min read</small></div>
                                </a>
                            @else
                                <div class="news-category-promo">
                                    <small>FindURSCH newsroom</small>
                                    <h3>Have a story in {{ strtolower($sectionCategory->name) }}?</h3>
                                    <p>Share a school announcement, community achievement or education development with our newsroom.</p>
                                    <a href="{{ route('contact') }}">Share a story →</a>
                                </div>
                            @endif
                        </div>
                    </section>

                    @if($loop->iteration === 3)
                        <div class="news-strip"><div><small>For students</small><h3>Find scholarships and bursaries</h3><p>Explore funding opportunities published by participating schools.</p></div><a href="{{ route('scholarships.index') }}">Browse scholarships →</a></div>
                    @elseif($loop->iteration === 5)
                        <div class="news-duo">
                            <div><h3>Get school news delivered</h3><p>Contact the FindURSCH newsroom to enquire about updates and story submissions.</p><a href="{{ route('contact') }}">Contact the newsroom →</a></div>
                            <div><h3>Have a story?</h3><p>Share news from your school, a community event or a policy update that matters to families.</p><a href="{{ route('contact') }}">Submit a story →</a></div>
                        </div>
                    @elseif($loop->iteration === 8)
                        <x-site-ad-banner placement="inlist" />
                    @endif
                @empty
                    <div class="news-empty"><h3>No published news yet</h3><p>Education news and school updates will appear here when stories are published.</p></div>
                @endforelse

                @if($videos->isNotEmpty())
                    <section class="news-section news-video-section">
                        <div class="news-section-title"><h2>School videos</h2><a href="{{ route('news.index', ['view' => 'videos']) }}">View all →</a></div>
                        <div class="news-video-grid">
                            @foreach($videos->take(2) as $video)
                                <a class="news-video-card" href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer">
                                    <div class="news-video-image"><img src="https://img.youtube.com/vi/{{ rawurlencode($video->youtube_video_id) }}/hqdefault.jpg" alt="" loading="lazy"><span class="news-video-play">▶</span></div>
                                    <div><small>{{ $video->school?->name }}</small><h3>{{ $video->title }}</h3><b>Watch video ↗</b></div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        @endif

        <section class="news-bottom-cta">
            <div><small>FindURSCH newsroom</small><h2>Have an education story to share?</h2><p>Tell us about a school announcement, community achievement or education development that matters to families.</p></div>
            <a href="{{ route('contact') }}">Share a story <span aria-hidden="true">→</span></a>
        </section>
        <div class="banner" aria-label="Sponsored banners">
            <x-site-ad-banner placement="foot1" />
            <x-site-ad-banner placement="foot2" />
        </div>
    </main>
</div>
