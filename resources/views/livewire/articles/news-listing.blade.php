<div class="news-page">
    <a class="news-register-tab" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>

    <section class="news-heading">
        <p class="news-eyebrow">FindURSCH education newsroom</p>
        <h1>School <em>News &amp; Updates</em> in Uganda</h1>
        <p>Stay informed with education news, school announcements, policy updates and stories from school communities across Uganda.</p>
    </section>

    <main class="news-wrap">
        <div class="news-category-row">
            <nav class="news-pills" aria-label="News categories">
                <a class="{{ empty($category) ? 'is-active' : '' }}" href="{{ route('news.index', ['q' => $search]) }}">All news</a>
                @foreach($categories as $item)
                    <a class="{{ (string) $category === (string) $item->id ? 'is-active' : '' }}" href="{{ route('news.index', ['category' => $item->id, 'q' => $search]) }}">{{ $item->name }}</a>
                @endforeach
            </nav>
            <a class="news-digest" href="{{ route('contact') }}">Contact the newsroom</a>
        </div>

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

        @if($leadArticle || $mostRead->isNotEmpty())
            <section class="news-top-stories" aria-label="Top news stories">
                <div class="news-lead-grid">
                    @if($leadArticle)
                        <a class="news-lead" href="{{ route('news.show', $leadArticle) }}" @if($leadArticle->cover_image) style="--news-cover:url('{{ $leadArticle->cover_image }}')" @endif>
                            <span class="news-lead-content">
                                <small>{{ $leadArticle->category?->name ?? 'Education news' }} · {{ $leadArticle->published_at?->format('d M Y') }}</small>
                                <strong>{{ $leadArticle->title }}</strong>
                                @if($leadArticle->excerpt)
                                    <span>{{ \Illuminate\Support\Str::limit($leadArticle->excerpt, 150) }}</span>
                                @endif
                                <b>Read full story <i aria-hidden="true">→</i></b>
                            </span>
                        </a>
                    @endif

                    @if($secondaryArticle)
                        <a class="news-secondary" href="{{ route('news.show', $secondaryArticle) }}">
                            @if($secondaryArticle->cover_image)
                                <img src="{{ $secondaryArticle->cover_image }}" alt="" loading="lazy">
                            @else
                                <div class="news-image-placeholder">FindURSCH</div>
                            @endif
                            <div>
                                <small>{{ $secondaryArticle->category?->name ?? 'Education' }} · {{ $secondaryArticle->published_at?->format('d M Y') }}</small>
                                <h2>{{ $secondaryArticle->title }}</h2>
                                <p>{{ \Illuminate\Support\Str::limit($secondaryArticle->excerpt, 130) }}</p>
                            </div>
                        </a>
                    @endif

                    @if($mostRead->isNotEmpty())
                        <aside class="news-most-read">
                            <h2>Most read</h2>
                            <ol>
                                @foreach($mostRead as $index => $article)
                                    <li>
                                        <a href="{{ route('news.show', $article) }}">
                                            <b>{{ $index + 1 }}</b>
                                            <span>{{ $article->title }}<small>{{ $article->category?->name ?? 'Education' }} · {{ $article->published_at?->format('d M Y') }}</small></span>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        </aside>
                    @endif
                </div>
            </section>
        @endif

        @if($searchResults)
            <section class="news-section">
                <div class="news-section-title">
                    <h2>{{ $selectedCategory?->name ?? 'News results' }}</h2>
                    <span>{{ $searchResults->total() }} stories</span>
                </div>
                @if($searchResults->isNotEmpty())
                    <div class="news-story-grid">
                        @foreach($searchResults as $article)
                            <a class="news-story-card" href="{{ route('news.show', $article) }}">
                                @if($article->cover_image)
                                    <img src="{{ $article->cover_image }}" alt="" loading="lazy">
                                @else
                                    <div class="news-image-placeholder">FindURSCH</div>
                                @endif
                                <div>
                                    <small>{{ $article->category?->name ?? 'Education' }} · {{ $article->published_at?->format('d M Y') }}</small>
                                    <h3>{{ $article->title }}</h3>
                                    <p>{{ \Illuminate\Support\Str::limit($article->excerpt, 145) }}</p>
                                    <b>Read story →</b>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="news-pagination">{{ $searchResults->links() }}</div>
                @else
                    <div class="news-empty">
                        <h3>No matching news stories</h3>
                        <p>Try another search term or choose a different category.</p>
                        <a href="{{ route('news.index') }}">Clear filters</a>
                    </div>
                @endif
            </section>
        @else
            @forelse($categorySections as $section)
                @php
                    $sectionCategory = $section['category'];
                    $sectionArticles = $section['articles'];
                    $sectionLead = $sectionArticles->first();
                @endphp
                <section class="news-section" id="news-{{ $sectionCategory->slug }}">
                    <div class="news-section-title">
                        <h2>{{ $sectionCategory->name }}</h2>
                        <a href="{{ route('news.index', ['category' => $sectionCategory->id]) }}">View all →</a>
                    </div>
                    <div class="news-category-grid">
                        @if($sectionLead)
                            <a class="news-feature-card" href="{{ route('news.show', $sectionLead) }}">
                                @if($sectionLead->cover_image)
                                    <img src="{{ $sectionLead->cover_image }}" alt="" loading="lazy">
                                @else
                                    <div class="news-image-placeholder">FindURSCH</div>
                                @endif
                                <div>
                                    <small>{{ $sectionLead->published_at?->format('d M Y') }} · {{ $sectionLead->read_time_minutes }} min read</small>
                                    <h3>{{ $sectionLead->title }}</h3>
                                    <p>{{ \Illuminate\Support\Str::limit($sectionLead->excerpt, 155) }}</p>
                                </div>
                            </a>
                        @endif

                        <div class="news-row-list">
                            @foreach($sectionArticles->skip(1) as $article)
                                <a href="{{ route('news.show', $article) }}">
                                    @if($article->cover_image)
                                        <img src="{{ $article->cover_image }}" alt="" loading="lazy">
                                    @else
                                        <div class="news-row-placeholder">F</div>
                                    @endif
                                    <span>
                                        <b>{{ $article->title }}</b>
                                        <small>{{ $article->published_at?->format('d M Y') }}</small>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @empty
                <div class="news-empty">
                    <h3>No published news yet</h3>
                    <p>Education news and school updates will appear here when stories are published.</p>
                </div>
            @endforelse
        @endif

        <section class="news-bottom-cta">
            <div>
                <small>FindURSCH newsroom</small>
                <h2>Have an education story to share?</h2>
                <p>Tell us about a school announcement, community achievement or education development that matters to families.</p>
            </div>
            <a href="{{ route('contact') }}">Share a story <span aria-hidden="true">→</span></a>
        </section>
    </main>
</div>
