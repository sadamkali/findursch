<div class="home-page">
    @php
        $heroImage = $spotlightSchool?->getFirstMediaUrl('cover') ?: $featuredSchools->first()?->getFirstMediaUrl('cover');
    @endphp

    <section class="home-hero">
        @if($heroImage)<div class="home-hero__photo" style="background-image:url('{{ $heroImage }}')" aria-hidden="true"></div>@endif
        <div class="site-wrap home-hero__content">
            <p class="home-eyebrow">Uganda's school discovery platform</p>
            <h1>Find the <em>right school</em><br class="hidden sm:block"> for your child</h1>
            <p class="home-hero__intro">Explore nursery, primary, secondary, vocational and university listings across Uganda, and compare your options side by side.</p>
            <form action="{{ route('schools.index') }}" method="GET" class="home-search" x-data="searchSuggestions" @submit="if (!q) $el.querySelector('[name=search]').disabled = true">
                <div class="home-search__query">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
                    <input type="search" name="search" x-model="q" @input.debounce.350ms="search()" @focus="if (q.length >= 2) open = true" placeholder="Search by name, location or district" aria-label="Search schools" autocomplete="off">
                    <div class="home-search__suggestions" x-cloak x-show="open && results.length" @click.outside="open = false">
                        <template x-for="result in results" :key="result.type + '-' + result.url">
                            <a :href="result.url" class="home-search__suggestion"><span x-text="result.label"></span><small x-text="result.detail || (result.type === 'district' ? 'District' : 'School')"></small></a>
                        </template>
                    </div>
                </div>
                <label class="home-search__select"><span class="sr-only">School type</span>
                    <select name="type" aria-label="School type">
                        <option value="">All types</option>
                        @foreach($schoolTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                    </select>
                </label>
                <label class="home-search__select home-search__district"><span class="sr-only">District</span>
                    <select name="district" aria-label="District">
                        <option value="">All districts</option>
                        @foreach($searchDistricts as $district)<option value="{{ $district->id }}">{{ $district->name }}</option>@endforeach
                    </select>
                </label>
                <button class="btn btn-search" type="submit">Search <span aria-hidden="true">→</span></button>
            </form>
            <div class="home-hero__stats" aria-label="FindurSch platform statistics">
                <span><strong>{{ number_format($stats['schools']) }}</strong> school listings</span>
                <span><strong>{{ number_format($stats['districts']) }}</strong> districts</span>
                <span><strong>{{ number_format($stats['reviews']) }}</strong> approved reviews</span>
            </div>
        </div>
        <div class="home-hero__dots" aria-hidden="true"><i class="is-active"></i><i></i><i></i></div>
    </section>

    <main class="site-wrap">
        {{-- Featured schools with a discovery sidebar --}}
        <div class="home-cols home-section home-featured-layout">
            <section class="home-main-column" aria-labelledby="featured-schools-title">
                <div class="home-section-heading">
                    <h2 id="featured-schools-title">Featured <span>schools</span></h2>
                    <a class="home-more" href="{{ route('schools.index') }}">View all →</a>
                </div>
                @if($featuredSchools->isNotEmpty())
                    <div class="home-school-grid">
                        @foreach($featuredSchools as $school)
                            <article class="home-school-card" data-reveal>
                                <a href="{{ route('schools.show', $school) }}" class="home-school-card__link">
                                    <div class="home-school-card__image">
                                        @if($school->getFirstMediaUrl('cover'))
                                            <img src="{{ $school->getFirstMediaUrl('cover') }}" alt="{{ $school->name }}" loading="lazy">
                                        @else
                                            <div class="home-image-placeholder" aria-hidden="true">{{ strtoupper(mb_substr($school->name, 0, 2)) }}</div>
                                        @endif
                                        @if($school->is_featured)<span class="home-image-flag">Featured</span>@endif
                                    </div>
                                    <div class="home-school-card__body">
                                        <span class="home-kicker">{{ $school->type?->name ?? 'School' }}</span>
                                        <h3>{{ $school->name }}</h3>
                                        <p>{{ $school->district?->name ?? 'Uganda' }} District</p>
                                        <div class="home-card-tags">
                                            @if($school->verification_status === 'verified')<span class="home-tag home-tag--verified">Verified</span>@endif
                                            @if($school->is_demo)<span class="home-tag">Sample profile</span>@endif
                                            @foreach($school->curricula->take(1) as $curriculum)<span class="home-tag">{{ $curriculum->abbreviation }}</span>@endforeach
                                        </div>
                                    </div>
                                </a>
                                <div class="home-school-card__footer">
                                    @if((float) $school->rating_average > 0)
                                        <span><b>★</b> {{ number_format((float) $school->rating_average, 1) }} <small>({{ $school->reviews_count }} reviews)</small></span>
                                    @else
                                        <span class="home-muted">No reviews yet</span>
                                    @endif
                                    <button type="button" x-data @click.stop="$dispatch('findursch:compare-add', '{{ $school->slug }}')" aria-label="Add {{ $school->name }} to compare">+ Compare</button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="home-empty-state"><h3>School listings are being prepared</h3><p>New school profiles will appear here as they are approved.</p><a href="{{ route('schools.index') }}" class="home-more">Browse the directory →</a></div>
                @endif

                @if($spotlightSchool)
                    <article class="home-spotlight" data-reveal>
                        <a class="home-spotlight__media" href="{{ route('schools.show', $spotlightSchool) }}" aria-label="View {{ $spotlightSchool->name }}">
                            @if($spotlightSchool->getFirstMediaUrl('cover'))
                                <img src="{{ $spotlightSchool->getFirstMediaUrl('cover') }}" alt="{{ $spotlightSchool->name }}" loading="lazy">
                            @else
                                <span class="home-image-placeholder" aria-hidden="true">{{ strtoupper(mb_substr($spotlightSchool->name, 0, 2)) }}</span>
                            @endif
                        </a>
                        <div class="home-spotlight__body">
                            <span class="home-kicker">School spotlight</span>
                            <h3>{{ $spotlightSchool->name }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Explore this school profile and learn more about its programmes and facilities.', 145) }}</p>
                            <div class="home-spotlight__meta">
                                @if($spotlightSchool->phone)<span><b>Tel</b> {{ $spotlightSchool->phone }}</span>@endif
                                <span><b>Location</b> {{ $spotlightSchool->district?->name ?? 'Uganda' }}</span>
                            </div>
                            <a class="home-more" href="{{ route('schools.show', $spotlightSchool) }}">Learn more about this school →</a>
                        </div>
                    </article>
                @endif

                <div class="home-strip" data-reveal>
                    <div><h3>Is your school on FindurSch?</h3><p>Create a school profile and help families discover your institution.</p></div>
                    <a href="{{ route('register') }}" class="btn btn-gold">List your school</a>
                </div>
            </section>

            <aside class="home-sidebar" aria-label="School discovery resources">
                <a href="{{ route('register') }}" class="home-sidebar-ad" data-reveal>
                    <span>For school communities</span>
                    <strong>Put your school in front of families</strong>
                    <small>Create a school profile on FindurSch</small>
                    <b class="btn btn-gold">List your school →</b>
                </a>
                <section class="home-sidebox" data-reveal>
                    <h2>Browse by category</h2>
                    <ul>
                        @forelse($schoolTypes as $type)
                            <li><a href="{{ route('schools.index', ['type' => $type->id]) }}"><span class="home-side-icon">{{ mb_substr($type->name, 0, 1) }}</span><span>{{ $type->name }}</span><em>{{ $type->public_schools_count }}</em></a></li>
                        @empty
                            <li class="home-side-empty">School categories will appear here.</li>
                        @endforelse
                    </ul>
                    <a class="home-sidebox__foot" href="{{ route('schools.index') }}">All school categories →</a>
                </section>
                <section class="home-sidebox" data-reveal>
                    <h2>Explore popular districts</h2>
                    <ul>
                        @forelse($districts as $district)
                            <li><a href="{{ route('district.show', $district) }}"><span class="home-side-icon home-side-icon--pin">⌖</span><span>{{ $district->name }}</span><em>{{ $district->school_count }}</em></a></li>
                        @empty
                            <li class="home-side-empty">District listings will appear here.</li>
                        @endforelse
                    </ul>
                    <a class="home-sidebox__foot" href="{{ route('schools.index') }}">Explore all schools →</a>
                </section>
            </aside>
        </div>

        {{-- Editorial education news --}}
        <section class="home-section home-news-section" aria-labelledby="home-news-title">
            <div class="home-section-heading"><h2 id="home-news-title">Education <span>news headlines</span></h2><a class="home-more" href="{{ route('articles.index') }}">All news →</a></div>
            @if($leadArticle || $newsArticles->isNotEmpty())
                <div class="home-news-grid">
                    @if($leadArticle)
                        <a href="{{ route('articles.show', $leadArticle) }}" class="home-news-lead" data-reveal>
                            @if($leadArticle->cover_image)<img src="{{ $leadArticle->cover_image }}" alt="" loading="lazy">@endif
                            <span class="home-news-lead__shade"></span>
                            <div class="home-news-lead__caption">
                                <small>{{ $leadArticle->category?->name ?? 'Education' }} · {{ $leadArticle->published_at?->format('d M Y') }}</small>
                                <h3>{{ $leadArticle->title }}</h3>
                                @if($leadArticle->excerpt)<p>{{ \Illuminate\Support\Str::limit($leadArticle->excerpt, 115) }}</p>@endif
                            </div>
                        </a>
                    @endif
                    <div class="home-news-list" data-reveal>
                        @forelse($newsArticles as $article)
                            <a href="{{ route('articles.show', $article) }}" class="home-news-row">
                                <div class="home-news-row__image">
                                    @if($article->cover_image)<img src="{{ $article->cover_image }}" alt="" loading="lazy">@else<span>F</span>@endif
                                </div>
                                <div><h3>{{ $article->title }}</h3><small>{{ $article->published_at?->format('d M Y') }}</small></div>
                            </a>
                        @empty
                            <p class="home-side-empty">More education headlines will appear here.</p>
                        @endforelse
                        <a class="home-news-list__more" href="{{ route('articles.index') }}">Browse all headlines →</a>
                    </div>
                    @if($spotlightSchool)
                        <a href="{{ route('schools.show', $spotlightSchool) }}" class="home-news-school" data-reveal>
                            <div class="home-news-school__image">
                                @if($spotlightSchool->getFirstMediaUrl('cover'))<img src="{{ $spotlightSchool->getFirstMediaUrl('cover') }}" alt="{{ $spotlightSchool->name }}" loading="lazy">@else<span>{{ strtoupper(mb_substr($spotlightSchool->name, 0, 2)) }}</span>@endif
                                <b>School spotlight</b>
                            </div>
                            <div class="home-news-school__body"><h3>{{ $spotlightSchool->name }}</h3><p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Discover this school profile on FindurSch.', 115) }}</p><small>{{ $spotlightSchool->district?->name ?? 'Uganda' }} · View profile →</small></div>
                        </a>
                    @endif
                </div>
            @else
                <div class="home-empty-state"><h3>Education news is coming soon</h3><p>Published education stories and guides will appear here.</p><a class="home-more" href="{{ route('articles.index') }}">Visit news &amp; guides →</a></div>
            @endif
        </section>

        {{-- Lower resources and owner callouts --}}
        <div class="home-cols home-section home-resources-layout">
            <section class="home-main-column home-stack">
                <div class="home-band" data-reveal>
                    <div><small>Make an informed choice</small><h2>Compare schools before you decide</h2><p>Review school details side by side and build a shortlist that suits your family.</p></div>
                    <a href="{{ route('compare') }}" class="btn btn-gold">Compare schools →</a>
                </div>
                <section aria-labelledby="home-guides-title">
                    <div class="home-section-heading"><h2 id="home-guides-title">Education <span>guides &amp; updates</span></h2><a class="home-more" href="{{ route('articles.index') }}">All articles →</a></div>
                    @if($insightArticles->isNotEmpty())
                        <div class="home-guide-grid">
                            @foreach($insightArticles->take(2) as $article)
                                <a href="{{ route('articles.show', $article) }}" class="home-guide-card" data-reveal>
                                    <div class="home-guide-card__image">@if($article->cover_image)<img src="{{ $article->cover_image }}" alt="" loading="lazy">@else<span>FindurSch</span>@endif</div>
                                    <div class="home-guide-card__body"><small>{{ $article->category?->name ?? 'Education' }}</small><h3>{{ $article->title }}</h3><p>{{ \Illuminate\Support\Str::limit($article->excerpt, 105) }}</p><span>{{ $article->published_at?->format('d M Y') }} · {{ $article->read_time_minutes }} min read</span></div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="home-empty-state home-empty-state--compact"><p>New education guides will be published here.</p><a class="home-more" href="{{ route('articles.index') }}">Browse articles →</a></div>
                    @endif
                </section>
                <div class="home-band home-band--light" data-reveal>
                    <div><small>Find schools across Uganda</small><h2>Start with the school type that fits your needs</h2>
                        <div class="home-chips">@foreach($schoolTypes->take(5) as $type)<a href="{{ route('schools.index', ['type' => $type->id]) }}">{{ $type->name }}</a>@endforeach</div>
                    </div>
                    <a href="{{ route('schools.index') }}" class="btn btn-outline-light">Browse schools</a>
                </div>
            </section>

            <aside class="home-sidebar" aria-label="More education resources">
                <section class="home-sidebox home-jobs-box" data-reveal>
                    <h2>Schools to explore</h2>
                    <ul>
                        @forelse($featuredSchools as $school)
                            <li><a href="{{ route('schools.show', $school) }}"><span>{{ $school->name }}</span><em>{{ $school->district?->name }}</em></a></li>
                        @empty
                            <li class="home-side-empty">School profiles will appear here.</li>
                        @endforelse
                    </ul>
                    <a class="home-sidebox__foot" href="{{ route('schools.index') }}">View school directory →</a>
                </section>
                <a href="{{ route('register') }}" class="home-sidebar-promo" data-reveal>
                    <small>For school owners</small><h2>Help families discover your school</h2><p>Build a profile with your school's details, programmes and contact information.</p><span class="btn btn-gold">Register your school</span>
                </a>
                <section class="home-sidebox home-help-box" data-reveal>
                    <h2>Need help choosing?</h2>
                    <div class="home-help-box__body"><p>Explore school profiles, compare key details and contact schools directly for current admissions information.</p><a class="home-sidebox__foot" href="{{ route('schools.index') }}">Start exploring →</a></div>
                </section>
            </aside>
        </div>
    </main>

    {{-- Tinted editorial insights band --}}
    <section class="home-insights">
        <div class="site-wrap">
            <div class="home-section-heading"><h2>Educational <span>insights</span></h2><a class="home-more" href="{{ route('articles.index') }}">All articles →</a></div>
            <div class="home-insights-grid">
                @if($insightArticles->first())
                    @php($primaryInsight = $insightArticles->first())
                    <a href="{{ route('articles.show', $primaryInsight) }}" class="home-insight-feature" data-reveal>
                        <div class="home-insight-feature__image">@if($primaryInsight->cover_image)<img src="{{ $primaryInsight->cover_image }}" alt="" loading="lazy">@else<span>FindurSch</span>@endif</div>
                        <div class="home-insight-feature__body"><small>{{ $primaryInsight->category?->name ?? 'Education' }}</small><h3>{{ $primaryInsight->title }}</h3><p>{{ \Illuminate\Support\Str::limit($primaryInsight->excerpt, 125) }}</p><span>{{ $primaryInsight->published_at?->format('M Y') }} · {{ $primaryInsight->read_time_minutes }} min read</span></div>
                    </a>
                @else
                    <div class="home-insight-feature home-empty-state" data-reveal><h3>Education insights</h3><p>Helpful stories and guides will appear here when published.</p></div>
                @endif

                <div class="home-insight-list" data-reveal>
                    @forelse($insightArticles->skip(1)->take(3) as $article)
                        <a href="{{ route('articles.show', $article) }}" class="home-news-row">
                            <div class="home-news-row__image">@if($article->cover_image)<img src="{{ $article->cover_image }}" alt="" loading="lazy">@else<span>F</span>@endif</div>
                            <div><h3>{{ $article->title }}</h3><small>{{ $article->published_at?->format('d M Y') }}</small></div>
                        </a>
                    @empty
                        <p class="home-side-empty">More articles will appear here as they are published.</p>
                    @endforelse
                    <a class="home-news-list__more" href="{{ route('articles.index') }}">Read more articles →</a>
                </div>

                @if($spotlightSchool)
                    <a href="{{ route('schools.show', $spotlightSchool) }}" class="home-insight-spotlight" data-reveal>
                        @if($spotlightSchool->getFirstMediaUrl('cover'))<img src="{{ $spotlightSchool->getFirstMediaUrl('cover') }}" alt="" loading="lazy">@endif
                        <span class="home-insight-spotlight__shade"></span>
                        <div><small>School spotlight</small><h3>{{ $spotlightSchool->name }}</h3><p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Explore this school on FindurSch.', 95) }}</p><span class="btn btn-gold btn-block">Learn more about this school</span></div>
                    </a>
                @else
                    <div class="home-insight-spotlight home-insight-spotlight--empty" data-reveal><div><small>School discovery</small><h3>Find a school that fits</h3><p>Explore school profiles from across Uganda.</p><a class="btn btn-gold btn-block" href="{{ route('schools.index') }}">Browse schools</a></div></div>
                @endif
            </div>
        </div>
    </section>

    <section class="home-final-cta">
        <div class="site-wrap">
            <p class="home-eyebrow">For schools and education communities</p>
            <h2>Is your school listed?</h2>
            <p>Join schools across Uganda helping parents, students and educators find the information they need.</p>
            <div class="home-final-cta__actions"><a href="{{ route('register') }}" class="btn btn-white">Register your school</a><a href="{{ route('about') }}" class="btn btn-ghost">Learn about FindurSch</a></div>
        </div>
    </section>
    <a href="{{ route('register') }}" class="home-register-tab">Register your school</a>
</div>
