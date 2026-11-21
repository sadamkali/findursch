<div class="resource-page">
    <a class="blogs-rtab" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>
    <div class="blogs-ad-slim"><span>FindURSCH news and education stories</span><small>Share useful stories with parents, learners and school communities across Uganda</small></div>

    <main class="blogs-wrap resource-detail-wrap article-detail-wrap">
        <nav class="resource-crumbs"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ request()->routeIs('news.show') ? route('news.index') : route('articles.index') }}">{{ request()->routeIs('news.show') ? 'News' : 'Blogs' }}</a><span>›</span><a href="{{ request()->routeIs('news.show') ? route('news.index', ['category' => $article->category_id]) : route('articles.index', ['category' => $article->category_id]) }}">{{ $article->category?->name ?? 'Education' }}</a><span>›</span><b>{{ $article->title }}</b></nav>

        <div class="resource-detail-grid article-detail-grid">
            <article class="resource-detail-content article-detail-content">
                <div class="resource-tags"><span>{{ $article->category?->name ?? 'Education' }}</span><span>{{ $article->read_time_minutes }} min read</span></div>
                <h1>{{ $article->title }}</h1>
                @if($article->cover_image)
                    <figure class="article-cover">
                        <img src="{{ $article->cover_image }}" alt="{{ $article->title }}">
                        <figcaption>{{ $article->category?->name ?? 'FindURSCH' }} · {{ $article->published_at?->format('d M Y') }}</figcaption>
                    </figure>
                @endif
                @if($article->excerpt)<p class="resource-detail-lead article-excerpt">{{ $article->excerpt }}</p>@endif

                <div class="article-byline">
                    <span class="article-avatar">{{ strtoupper(mb_substr($article->author?->name ?? 'F', 0, 1)) }}</span>
                    <span><b>{{ $article->author?->name ?? 'FindURSCH Editorial' }}</b><small>{{ $article->category?->name ?? 'Education' }} · {{ $article->published_at?->format('d M Y') }}</small></span>
                    <span class="article-view-count">{{ number_format($article->views_count) }} views</span>
                </div>

                <div class="resource-share">
                    <span>SHARE</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener">Facebook</a>
                    <a href="https://wa.me/?text={{ urlencode($article->title.' '.request()->fullUrl()) }}" target="_blank" rel="noopener">WhatsApp</a>
                    <button type="button" x-data @click="navigator.clipboard.writeText(window.location.href)">Copy link</button>
                </div>

                <section class="article-body prose prose-gray max-w-none">
                    {!! $article->body !!}
                </section>

                <x-site-ad-banner placement="art" />

                <div class="article-category-tags"><span>TOPIC</span><a href="{{ request()->routeIs('news.show') ? route('news.index', ['category' => $article->category_id]) : route('articles.index', ['category' => $article->category_id]) }}">{{ $article->category?->name ?? 'Education' }}</a></div>
                <section class="article-story-cta">
                    <div><small>Have a story to share?</small><h2>Help parents and school communities stay informed</h2><p>Contact FindURSCH about a school announcement, education story or useful community update.</p></div>
                    <a class="blogs-btn blogs-btn-gold" href="{{ route('contact') }}">Contact the editorial team</a>
                </section>
            </article>

            <aside class="resource-detail-sidebar article-sidebar">
                @if($mostRead->isNotEmpty())
                    <div class="article-sidebox">
                        <h2>Most read in {{ $article->category?->name ?? 'Education' }}</h2>
                        <ol>
                            @foreach($mostRead as $index => $item)
                                <li><a href="{{ request()->routeIs('news.show') ? route('news.show', $item) : route('articles.show', $item) }}"><b>{{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}</b><span>{{ $item->title }}<small>{{ number_format($item->views_count) }} views</small></span></a></li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                @if($spotlightSchool)
                    <div class="article-school-spotlight">
                        @if($spotlightSchool->getFirstMediaUrl('cover'))<img src="{{ $spotlightSchool->getFirstMediaUrl('cover') }}" alt="{{ $spotlightSchool->name }}" loading="lazy">@endif
                        <div><small>School spotlight</small><h3>{{ $spotlightSchool->name }}</h3><p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Explore this verified school profile on FindURSCH.', 100) }}</p><a class="blogs-btn blogs-btn-gold" href="{{ route('schools.show', $spotlightSchool) }}">Learn more</a></div>
                    </div>
                @endif

                <x-site-ad-banner placement="side" />
                <div class="article-sidebar-links"><a href="{{ route('events.index') }}">Upcoming school events →</a><a href="{{ route('scholarships.index') }}">Scholarships and bursaries →</a><a href="{{ route('articles.index') }}">More education blogs →</a></div>
            </aside>
        </div>

        @if($related->isNotEmpty())
            <section class="resource-related article-related">
                <div class="resource-section-head"><div><small>Keep reading</small><h2>Related stories</h2></div><a href="{{ request()->routeIs('news.show') ? route('news.index') : route('articles.index') }}">More {{ request()->routeIs('news.show') ? 'news' : 'blogs' }} →</a></div>
                <div class="resource-related-grid">
                    @foreach($related as $item)
                        <a href="{{ request()->routeIs('news.show') ? route('news.show', $item) : route('articles.show', $item) }}">
                            @if($item->cover_image)<img src="{{ $item->cover_image }}" alt="" loading="lazy">@endif
                            <small>{{ $item->category?->name ?? 'Education' }} · {{ $item->published_at?->format('d M Y') }}</small>
                            <b>{{ $item->title }}</b><span>{{ $item->read_time_minutes }} min read</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>