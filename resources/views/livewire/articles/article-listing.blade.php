<div class="blogs-page">
    @php
        $isNews = ($mode ?? 'blogs') === 'news';
        $indexRoute = $isNews ? 'news.index' : 'articles.index';
        $showRoute = $isNews ? 'news.show' : 'articles.show';
        $kindLabel = $isNews ? 'news' : 'blogs';
    @endphp

    <a class="blogs-rtab" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>
    <x-site-page-hero
        eyebrow="FindURSCH resources"
        :title="($isNews ? 'School News & Updates' : 'FindURSCH Blogs')"
        :description="$isNews ? 'Stay informed with education news, school announcements, policy updates and stories from school communities across Uganda.' : 'Practical advice, inspiring stories and fresh ideas for parents, students and teachers, from study skills and school life to careers, health, faith and culture.'"
        :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => $isNews ? 'News' : 'Blogs']]"
    />

    <main class="blogs-wrap">
        <nav class="blogs-pills" aria-label="{{ $isNews ? 'News categories' : 'Blog categories' }}">
            <a href="{{ route($indexRoute, array_filter(['q' => $search ?: null, 'sort' => $sort !== 'latest' ? $sort : null])) }}"
               class="{{ ! $category ? 'is-active' : '' }}">{{ $isNews ? 'All news' : 'All blogs' }}</a>
            @foreach($categories as $item)
                <a href="{{ route($indexRoute, array_filter(['category' => $item->id, 'q' => $search ?: null, 'sort' => $sort !== 'latest' ? $sort : null])) }}"
                   class="{{ (string) $category === (string) $item->id ? 'is-active' : '' }}">{{ $item->name }}</a>
            @endforeach
        </nav>

        <form class="blogs-bar" method="GET" action="{{ route($indexRoute) }}" role="search">
            <label class="blogs-search-field">
                <span class="sr-only">{{ $isNews ? 'Search news, topics or schools' : 'Search blogs, topics or authors' }}</span>
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ $isNews ? 'Search news, topics or schools' : 'Search blogs, topics or authors' }}">
            </label>
            <button class="blogs-btn blogs-btn-brand" type="submit">Search</button>
            <label>
                <span class="sr-only">Category</span>
                <select name="category" aria-label="{{ $isNews ? 'News category' : 'Blog category' }}">
                    <option value="">All categories</option>
                    @foreach($categories as $item)
                        <option value="{{ $item->id }}" {{ (string) $category === (string) $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="sr-only">Sort {{ $kindLabel }}</span>
                <select name="sort" aria-label="Sort blogs">
                    <option value="latest" {{ $sort !== 'popular' ? 'selected' : '' }}>Latest first</option>
                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Most read</option>
                </select>
            </label>
        </form>

        @if($leadArticle || $mostRead->isNotEmpty())
            <section class="blogs-sec blogs-top-stories" aria-label="Featured {{ $kindLabel }}">
                <div class="blogs-news">
                    @if($leadArticle)
                        <a class="blogs-lead" href="{{ route($showRoute, $leadArticle) }}">
                            @if($leadArticle->cover_image)
                                <img class="blogs-lead__image" src="{{ $leadArticle->cover_image }}" alt="{{ $leadArticle->title }}" fetchpriority="high">
                            @endif
                            <span class="blogs-lead__shade"></span>
                            <span class="blogs-lead__caption">
                                <small>{{ $leadArticle->category?->name ?? 'FindURSCH editorial' }} · {{ $leadArticle->published_at?->format('d M Y') }}</small>
                                <strong>{{ $leadArticle->title }}</strong>
                                <span class="blogs-lead__meta">{{ $leadArticle->read_time_minutes }} min read @if($leadArticle->is_featured) · Featured {{ $kindLabel }} @endif</span>
                            </span>
                        </a>
                    @endif

                    @if($secondaryArticle)
                        <a class="blogs-card blogs-prem" href="{{ route($showRoute, $secondaryArticle) }}">
                            <div class="blogs-media">
                                @if($secondaryArticle->cover_image)
                                    <img src="{{ $secondaryArticle->cover_image }}" alt="{{ $secondaryArticle->title }}" loading="lazy">
                                @else
                                    <span class="blogs-placeholder blogs-placeholder--c"></span>
                                @endif
                            </div>
                            <div class="blogs-card-body">
                                <span class="blogs-kick">{{ $secondaryArticle->category?->name ?? 'Education' }} · {{ $secondaryArticle->published_at?->format('d M Y') }}</span>
                                <h2>{{ $secondaryArticle->title }}</h2>
                                @if($secondaryArticle->excerpt)<p>{{ \Illuminate\Support\Str::limit($secondaryArticle->excerpt, 115) }}</p>@endif
                                <small>{{ $secondaryArticle->read_time_minutes }} min read</small>
                            </div>
                        </a>
                    @endif

                    @if($mostRead->isNotEmpty())
                        <aside class="blogs-card blogs-box" aria-label="Most read {{ $kindLabel }}">
                            <h2>{{ $isNews ? 'Most read today' : 'Most read' }}</h2>
                            <ol>
                                @foreach($mostRead as $index => $article)
                                    <li>
                                        <a href="{{ route($showRoute, $article) }}">
                                            <b>{{ $index + 1 }}</b>
                                            <span>
                                                <em>{{ $article->category?->name ?? 'Education' }}</em>
                                                {{ $article->title }}
                                                <small>{{ number_format($article->views_count) }} views</small>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                            <a class="blogs-box-foot" href="{{ route($indexRoute, ['sort' => 'popular']) }}">See popular {{ $kindLabel }}</a>
                        </aside>
                    @endif
                </div>
            </section>
        @endif

        @if($searchResults)
            <section class="blogs-sec" aria-labelledby="{{ $isNews ? 'news-results-title' : 'blogs-results-title' }}">
                <div class="blogs-head blogs-head-cat">
                    <h2 id="blogs-results-title">
                        @if($selectedCategory){{ $selectedCategory->name }}
                        @elseif($search !== '')Search results
                        @else All blogs
                        @endif
                    </h2>
                    <span>{{ $searchResults->total() }} {{ \Illuminate\Support\Str::plural('blog', $searchResults->total()) }}</span>
                </div>

                @if($searchResults->isNotEmpty())
                    <div class="blogs-results-grid">
                        @foreach($searchResults as $article)
                            <a class="blogs-card blogs-result-card" href="{{ route($showRoute, $article) }}">
                                <div class="blogs-media">
                                    @if($article->cover_image)
                                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" loading="lazy">
                                    @else
                                        <span class="blogs-placeholder blogs-placeholder--{{ ['a','b','c','d'][$loop->index % 4] }}"></span>
                                    @endif
                                </div>
                                <div class="blogs-card-body">
                                    <span class="blogs-kick">{{ $article->category?->name ?? 'Education' }}</span>
                                    <h3>{{ $article->title }}</h3>
                                    @if($article->excerpt)<p>{{ \Illuminate\Support\Str::limit($article->excerpt, 135) }}</p>@endif
                                    <small>{{ $article->published_at?->format('d M Y') }} · {{ $article->read_time_minutes }} min read</small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="blogs-pagination">{{ $searchResults->links() }}</div>
                @else
                    <div class="blogs-empty">
                        <h3>No {{ $kindLabel }} found</h3>
                        <p>Try a different search term or choose another category.</p>
                        <a class="blogs-btn blogs-btn-brand" href="{{ route($indexRoute) }}">Clear filters</a>
                    </div>
                @endif
            </section>
        @else
            @forelse($categorySections as $sectionIndex => $section)
                @php
                    $sectionCategory = $section['category'];
                    $sectionArticles = $section['articles'];
                    $sectionLead = $sectionArticles->first();
                    $sectionFeature = $sectionArticles->count() > 1 ? $sectionArticles->last() : null;
                    $sectionRows = $sectionArticles->slice(1, max(0, $sectionArticles->count() - 2));
                @endphp
                <section class="blogs-sec" id="blog-category-{{ $sectionCategory->slug }}">
                    <div class="blogs-head blogs-head-cat">
                        <h2>{{ $sectionCategory->name }}</h2>
                        <a class="blogs-more" href="{{ route($indexRoute, ['category' => $sectionCategory->id]) }}">View more <span aria-hidden="true">→</span></a>
                    </div>
                    <div class="blogs-news">
                        <a class="blogs-card blogs-big" href="{{ route($showRoute, $sectionLead) }}">
                            <div class="blogs-media">
                                @if($sectionLead->cover_image)
                                    <img src="{{ $sectionLead->cover_image }}" alt="{{ $sectionLead->title }}" loading="lazy">
                                @else
                                    <span class="blogs-placeholder blogs-placeholder--{{ ['a','b','c','d'][$sectionIndex % 4] }}"></span>
                                @endif
                            </div>
                            <div class="blogs-card-body">
                                <h3>{{ $sectionLead->title }}</h3>
                                @if($sectionLead->excerpt)<p>{{ \Illuminate\Support\Str::limit($sectionLead->excerpt, 145) }}</p>@endif
                                <small><span>{{ $sectionLead->published_at?->format('d M Y') }}</span><span>{{ $sectionLead->read_time_minutes }} min read</span></small>
                            </div>
                        </a>

                        <div class="blogs-card blogs-rows">
                            @forelse($sectionRows as $rowArticle)
                                <a class="blogs-row" href="{{ route($showRoute, $rowArticle) }}">
                                    <div class="blogs-row-media">
                                        @if($rowArticle->cover_image)
                                            <img src="{{ $rowArticle->cover_image }}" alt="" loading="lazy">
                                        @else
                                            <span class="blogs-placeholder blogs-placeholder--{{ ['a','b','c','d'][$loop->index % 4] }}"></span>
                                        @endif
                                    </div>
                                    <span class="blogs-row-copy">
                                        <strong>{{ $rowArticle->title }}</strong>
                                        <small>{{ $rowArticle->published_at?->format('d M Y') }}</small>
                                    </span>
                                </a>
                            @empty
                                <p class="blogs-rows-empty">More blogs in this category will appear here as they are published.</p>
                            @endforelse
                        </div>

                        @if($sectionFeature)
                            <a class="blogs-card blogs-prem" href="{{ route($showRoute, $sectionFeature) }}">
                                <div class="blogs-media">
                                    @if($sectionFeature->cover_image)
                                        <img src="{{ $sectionFeature->cover_image }}" alt="{{ $sectionFeature->title }}" loading="lazy">
                                    @else
                                        <span class="blogs-placeholder blogs-placeholder--{{ ['a','b','c','d'][($sectionIndex + 2) % 4] }}"></span>
                                    @endif
                                </div>
                                @if($sectionFeature->is_featured)<span class="blogs-flag">Featured</span>@endif
                                <div class="blogs-card-body">
                                    <span class="blogs-kick">{{ $sectionCategory->name }}</span>
                                    <h3>{{ $sectionFeature->title }}</h3>
                                    @if($sectionFeature->excerpt)<p>{{ \Illuminate\Support\Str::limit($sectionFeature->excerpt, 110) }}</p>@endif
                                    <small><span>{{ $sectionFeature->published_at?->format('d M Y') }}</span><span>{{ $sectionFeature->read_time_minutes }} min read</span></small>
                                </div>
                            </a>
                        @endif
                    </div>
                </section>

                @if($sectionIndex === 2)
                    <x-site-ad-banner placement="inlist" />
                @elseif($sectionIndex === 5)
                    <section class="blogs-duo">
                        <div class="blogs-band"><h3>Have a story to share?</h3><p>We welcome useful perspectives and experiences from parents, teachers and students.</p><a class="blogs-btn blogs-btn-gold" href="{{ route('contact') }}">Contact the editorial team</a></div>
                        <div class="blogs-band blogs-band-dark"><h3>Looking for school information?</h3><p>Explore school profiles and compare options across Uganda.</p><a class="blogs-btn blogs-btn-gold" href="{{ route('schools.index') }}">Explore schools</a></div>
                    </section>
                @endif
            @empty
                <section class="blogs-empty">
                    <h3>No published {{ $kindLabel }} yet</h3>
                    <p>{{ $isNews ? 'Education news and school updates will appear here when they are published.' : 'New stories and useful education guides will appear here when they are published.' }}</p>
                </section>
            @endforelse
        @endif

        <section class="blogs-bottom-cta">
            <div><small>FindURSCH</small><h2>{{ $isNews ? 'The latest education updates for every school community' : 'Useful information for every school journey' }}</h2><p>{{ $isNews ? 'Follow school announcements, education developments and useful updates from across Uganda.' : 'Discover schools, read education stories and compare the options available to your family.' }}</p></div>
            <a class="blogs-btn blogs-btn-gold" href="{{ route('schools.index') }}">Explore schools</a>
        </section>
        <div class="banner" aria-label="Sponsored banners">
            <x-site-ad-banner placement="foot1" />
            <x-site-ad-banner placement="foot2" />
        </div>
    </main>
</div>
