<div class="article-reference-page">
    <a class="ar-register" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>

    <main class="ar-wrap">
        <x-site-ad-banner placement="top" />

        <nav class="ar-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span>›</span>
            <a href="{{ request()->routeIs('news.show') ? route('news.index') : route('articles.index') }}">{{ request()->routeIs('news.show') ? 'News' : 'Blogs' }}</a><span>›</span>
            <a href="{{ request()->routeIs('news.show') ? route('news.index', ['category' => $article->category_id]) : route('articles.index', ['category' => $article->category_id]) }}">{{ $article->category?->name ?? 'Education' }}</a><span>›</span>
            <b>{{ $article->title }}</b>
        </nav>

        <div class="ar-layout">
            <article class="ar-content">
                <div class="ar-tags">
                    <span class="ar-tag ar-tag-category">{{ $article->category?->name ?? 'Education' }}</span>
                    <span class="ar-tag">{{ request()->routeIs('news.show') ? 'News story' : 'Education blog' }}</span>
                    <span class="ar-tag">{{ max(1, (int) $article->read_time_minutes) }} min read</span>
                </div>

                <h1>{{ $article->title }}</h1>

                @if($article->cover_image)
                    <figure class="ar-photo">
                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" loading="eager">
                    </figure>
                    <p class="ar-credit">{{ $article->category?->name ?? 'FindURSCH' }} · {{ $article->published_at?->format('d F Y') ?? 'Recently published' }}</p>
                @else
                    <div class="ar-photo ar-photo-empty" role="img" aria-label="FindURSCH education article">
                        <span>FindURSCH</span>
                    </div>
                    <p class="ar-credit">{{ $article->category?->name ?? 'FindURSCH' }} · {{ $article->published_at?->format('d F Y') ?? 'Recently published' }}</p>
                @endif

                @if($article->excerpt)
                    <p class="ar-excerpt">{{ $article->excerpt }}</p>
                @endif

                <div class="ar-author">
                    <span class="ar-avatar" aria-hidden="true">{{ strtoupper(mb_substr($article->author?->name ?? 'F', 0, 1)) }}</span>
                    <span class="ar-author-copy">
                        <b>{{ $article->author?->name ?? 'FindURSCH Editorial' }}</b>
                        <small>{{ $article->category?->name ?? 'Education' }} · {{ $article->published_at?->format('d M Y') ?? 'Recently published' }}</small>
                    </span>
                    <span class="ar-article-meta">{{ number_format($article->views_count) }} views <i>·</i> {{ max(1, (int) $article->read_time_minutes) }} min read</span>
                </div>

                <div class="ar-share" x-data="{ copied: false }">
                    <span>SHARE:</span>
                    <a class="ar-share-facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                    <a class="ar-share-whatsapp" href="https://wa.me/?text={{ urlencode($article->title.' '.request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    <a class="ar-share-x" href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($article->title) }}" target="_blank" rel="noopener noreferrer">X</a>
                    <button type="button" class="ar-share-copy" @click="navigator.clipboard.writeText(window.location.href).then(() => { copied = true; setTimeout(() => copied = false, 1800) })" x-text="copied ? 'Link copied' : 'Copy link'">Copy link</button>
                </div>

                <section class="ar-body">
                    {!! $article->body !!}
                </section>

                <x-site-ad-banner placement="art" />

                <div class="ar-topic-tags">
                    <span>TOPIC</span>
                    <a href="{{ request()->routeIs('news.show') ? route('news.index', ['category' => $article->category_id]) : route('articles.index', ['category' => $article->category_id]) }}">{{ $article->category?->name ?? 'Education' }}</a>
                    <a href="{{ request()->routeIs('news.show') ? route('news.index') : route('articles.index') }}">{{ request()->routeIs('news.show') ? 'News' : 'Blogs' }}</a>
                </div>

                <section class="ar-story">
                    <div>
                        <small>Have a story to share?</small>
                        <h2>Help parents and school communities stay informed</h2>
                        <p>Contact FindURSCH about a school announcement, education story or useful community update.</p>
                    </div>
                    <a href="{{ route('contact') }}">Contact the editorial team <b aria-hidden="true">→</b></a>
                </section>
            </article>

            <aside class="ar-sidebar" aria-label="More education stories">
                @if($mostRead->isNotEmpty())
                    <section class="ar-sidebox">
                        <h2>Most read in {{ $article->category?->name ?? 'Education' }}</h2>
                        <ol>
                            @foreach($mostRead as $index => $item)
                                <li>
                                    <a href="{{ request()->routeIs('news.show') ? route('news.show', $item) : route('articles.show', $item) }}">
                                        <b>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</b>
                                        <span>{{ $item->title }}<small>{{ number_format($item->views_count) }} views</small></span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                <x-site-ad-banner placement="side" />

                @if($spotlightSchool)
                    <a class="ar-spotlight" href="{{ route('schools.show', $spotlightSchool) }}">
                        @if($spotlightSchool->getFirstMediaUrl('cover'))
                            <img src="{{ $spotlightSchool->getFirstMediaUrl('cover') }}" alt="{{ $spotlightSchool->name }}" loading="lazy">
                        @endif
                        <div>
                            <small>School spotlight</small>
                            <h3>{{ $spotlightSchool->name }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Explore this verified school profile on FindURSCH.', 100) }}</p>
                            <span>Learn more about this school <b aria-hidden="true">→</b></span>
                        </div>
                    </a>
                @else
                    <a class="ar-spotlight ar-spotlight-placeholder" href="{{ route('schools.index') }}">
                        <div>
                            <small>School discovery</small>
                            <h3>Find a school in Uganda</h3>
                            <p>Explore school profiles and compare options for your family.</p>
                            <span>Browse schools <b aria-hidden="true">→</b></span>
                        </div>
                    </a>
                @endif

                <section class="ar-sidebar-links">
                    <h2>Explore more</h2>
                    <a href="{{ route('events.index') }}">Upcoming school events <b aria-hidden="true">→</b></a>
                    <a href="{{ route('scholarships.index') }}">Scholarships and bursaries <b aria-hidden="true">→</b></a>
                    <a href="{{ route('articles.index') }}">More education blogs <b aria-hidden="true">→</b></a>
                    <a href="{{ route('news.index') }}">Latest education news <b aria-hidden="true">→</b></a>
                </section>
            </aside>
        </div>

        @if($related->isNotEmpty())
            <section class="ar-related">
                <div class="ar-related-heading">
                    <h2>Related stories</h2>
                    <a href="{{ request()->routeIs('news.show') ? route('news.index') : route('articles.index') }}">More {{ request()->routeIs('news.show') ? 'news' : 'blogs' }} <b aria-hidden="true">→</b></a>
                </div>
                <div class="ar-related-grid">
                    @foreach($related as $item)
                        <a class="ar-related-card" href="{{ request()->routeIs('news.show') ? route('news.show', $item) : route('articles.show', $item) }}">
                            @if($item->cover_image)
                                <img src="{{ $item->cover_image }}" alt="" loading="lazy">
                            @else
                                <span class="ar-related-placeholder">FindURSCH</span>
                            @endif
                            <div class="ar-related-card-copy">
                                <small>{{ $item->category?->name ?? 'Education' }} · {{ $item->published_at?->format('d M Y') }}</small>
                                <b>{{ $item->title }}</b>
                            </div>
                            <span class="ar-related-meta">{{ max(1, (int) $item->read_time_minutes) }} min read <b aria-hidden="true">→</b></span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>