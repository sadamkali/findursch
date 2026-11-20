<div class="findursch-home-reference" style="--brand: {{ $homepageTheme['primary_color'] }}; --accent: {{ $homepageTheme['accent_color'] }}; --serif: '{{ $homepageTheme['font_display'] }}', Georgia, serif; --sans: '{{ $homepageTheme['font_body'] }}', system-ui, sans-serif;">
    @if($homepageDisplaySettings['register_tab'])
        <a class="tab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>
    @endif

    <section class="hero" x-data="homepageHero(@js($homepageSlides))" :style="backgroundStyle">
        <div>
            <h1 x-text="currentSlide.title">Find the right school for your child</h1>
            <p x-text="currentSlide.description">Browse nursery, primary, secondary, vocational and university listings across Uganda, and compare them side by side.</p>
            @if($homepageDisplaySettings['search'])
                <form class="search" action="{{ route('schools.index') }}" method="GET">
                    <input type="search" name="search" placeholder="Search by name, location or district" aria-label="Search schools">
                    <select name="type" aria-label="School type">
                        <option value="">All types</option>
                        @foreach($schoolTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-red" type="submit">Search</button>
                </form>
            @endif
            <a class="btn btn-gold hero-slide-cta" x-show="currentSlide.button_label && currentSlide.button_url" :href="currentSlide.button_url" x-text="currentSlide.button_label"></a>
        </div>
        <div class="hero-carousel-controls" x-show="slides.length > 1" x-cloak>
            <button type="button" class="hero-carousel-arrow" @click="previous()" aria-label="Previous slide">‹</button>
            <div class="dots" role="tablist" aria-label="Homepage slides">
                <template x-for="(slide, index) in slides" :key="index">
                    <button type="button" :class="{on: active === index}" :aria-label="'Show slide ' + (index + 1)" :aria-selected="active === index" role="tab" @click="show(index)"></button>
                </template>
            </div>
            <button type="button" class="hero-carousel-arrow" @click="next()" aria-label="Next slide">›</button>
        </div>
    </section>

    <main class="wrap">
        @if($homepageDisplaySettings['featured_schools'] || $homepageDisplaySettings['sidebar'])
        <div class="cols sec" @if(!$homepageDisplaySettings['featured_schools'] || !$homepageDisplaySettings['sidebar']) style="grid-template-columns:minmax(0,1fr)" @endif>
            @if($homepageDisplaySettings['featured_schools'])
            <div>
                <div class="head">
                    <h2>Featured <span>schools</span></h2>
                    <a class="more" href="{{ route('schools.index') }}">View all</a>
                </div>

                <div class="slider">
                    <span class="arrow l" aria-hidden="true">‹</span>
                    <span class="arrow r" aria-hidden="true">›</span>
                    <div class="cards">
                        @forelse($featuredSchools as $school)
                            <a class="card school" href="{{ route('schools.show', $school) }}">
                                @php
                                    $schoolPhoto = $school->getFirstMediaUrl('cover');
                                    $photoTone = $loop->iteration === 2 ? 'b' : ($loop->iteration === 3 ? 'c' : '');
                                @endphp
                                <div class="ph {{ $photoTone }}">
                                    @if($schoolPhoto)
                                        <img src="{{ $schoolPhoto }}" alt="{{ $school->name }}" loading="lazy">
                                    @else
                                        <span class="ph-initials">{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="b">
                                    <span class="kick">{{ $school->type?->name ?? 'School' }}</span>
                                    <h3>{{ $school->name }}</h3>
                                    <small>{{ $school->district?->name ?? 'Uganda' }} district</small>
                                    @foreach($school->curricula->take(1) as $curriculum)
                                        <span class="tag">{{ $curriculum->abbreviation }}</span>
                                    @endforeach
                                    @if($school->created_at?->greaterThan(now()->subDays(14)))
                                        <span class="tag new">New</span>
                                    @endif
                                    @if($school->verification_status === 'verified')
                                        <span class="tag">Verified</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="card school school-empty">
                                <div class="ph"><span class="ph-initials">FS</span></div>
                                <div class="b"><span class="kick">FindURSCH directory</span><h3>School listings are being prepared</h3><small>Approved school profiles will appear here.</small></div>
                            </div>
                        @endforelse
                        @if($featuredSchools->count() === 1)
                            <a class="card school school-empty-link" href="{{ route('schools.index') }}"><div class="ph b"><span class="ph-initials">+</span></div><div class="b"><span class="kick">Explore more</span><h3>Discover schools across Uganda</h3><small>Browse the school directory</small><span class="tag">View schools</span></div></a>
                            <a class="card school school-empty-link" href="{{ route('compare') }}"><div class="ph c"><span class="ph-initials">↔</span></div><div class="b"><span class="kick">Make a shortlist</span><h3>Compare your options</h3><small>Review schools side by side</small><span class="tag">Compare</span></div></a>
                        @elseif($featuredSchools->count() === 2)
                            <a class="card school school-empty-link" href="{{ route('schools.index') }}"><div class="ph c"><span class="ph-initials">+</span></div><div class="b"><span class="kick">Explore more</span><h3>Find another school</h3><small>Browse schools across Uganda</small><span class="tag">View all</span></div></a>
                        @endif
                    </div>
                </div>
                <div class="dots" aria-hidden="true"><i></i><i class="on"></i><i></i></div>

                @if($spotlightSchool)
                    <div class="card spot">
                        <a class="ph d" href="{{ route('schools.show', $spotlightSchool) }}" aria-label="View {{ $spotlightSchool->name }}">
                            @if($spotlightSchool->getFirstMediaUrl('cover'))
                                <img src="{{ $spotlightSchool->getFirstMediaUrl('cover') }}" alt="{{ $spotlightSchool->name }}" loading="lazy">
                            @else
                                <span class="ph-initials">{{ strtoupper(mb_substr($spotlightSchool->name, 0, 2)) }}</span>
                            @endif
                        </a>
                        <div class="t">
                            <h3>School spotlight</h3>
                            <p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Discover this school and explore its programmes, facilities and learning environment.', 150) }}</p>
                            <div class="meta">
                                @if($spotlightSchool->phone)<b>Tel</b> {{ $spotlightSchool->phone }} &nbsp;@endif
                                <b>Location</b> {{ $spotlightSchool->district?->name ?? 'Uganda' }}
                            </div>
                            <a class="more" href="{{ route('schools.show', $spotlightSchool) }}">Learn more about this school</a>
                        </div>
                    </div>
                @endif

                <div class="strip">
                    <div><h4>Is your school on FindURSCH?</h4><p>Parents across Uganda search here for schools every day.</p></div>
                    <a class="btn btn-gold" href="{{ route('register') }}">List your school</a>
                </div>
            </div>
            @endif

            @if($homepageDisplaySettings['sidebar'])
            <aside>
                @if($homepageDisplaySettings['banner_ads'])
                @if($homeSidebarAd)
                    <a class="ad ad-primary ad-managed" href="{{ $homeSidebarAd->destination_url ?: route('schools.index') }}" target="{{ $homeSidebarAd->destination_url ? '_blank' : '_self' }}" rel="{{ $homeSidebarAd->destination_url ? 'noopener noreferrer' : '' }}">
                        @if($homeSidebarAd->image_url)<img src="{{ $homeSidebarAd->image_url }}" alt="{{ $homeSidebarAd->image_alt ?: $homeSidebarAd->title }}" loading="lazy">@endif
                        <span>{{ $homeSidebarAd->title }}</span>
                        <small>{{ $homeSidebarAd->description }}</small>
                        <b class="ad-cta">{{ $homeSidebarAd->cta_label ?: 'Learn more' }} →</b>
                    </a>
                @else
                    <a class="ad ad-primary" href="{{ route('contact') }}">
                        <span>Advertise with FindURSCH</span>
                        <small>Reach parents and students across Uganda</small>
                        <b class="ad-cta">Advertise with us →</b>
                    </a>
                @endif
                @endif

                @if($homepageDisplaySettings['categories'])
                <div class="card box">
                    <h5>Browse by category</h5>
                    <ul>
                        @forelse($schoolTypes->take(5) as $type)
                            <li><a href="{{ route('schools.index', ['type' => $type->id]) }}"><span class="ico">{{ mb_substr($type->name, 0, 1) }}</span>{{ $type->name }}<em>{{ number_format($type->public_schools_count) }}</em></a></li>
                        @empty
                            <li><span class="box-empty">School categories will appear here.</span></li>
                        @endforelse
                    </ul>
                    <a class="foot" href="{{ route('schools.index') }}">All categories</a>
                </div>
                @endif

                @if($homepageDisplaySettings['upcoming_events'])
                <div class="card box">
                    <h5>Upcoming events</h5>
                    <ul>
                        @forelse($upcomingEvents as $event)
                            <li>
                                <a href="{{ route('events.show', $event) }}">
                                    <span class="date">{{ $event->starts_at?->format('d') }}<small>{{ strtoupper($event->starts_at?->format('M')) }}</small></span>
                                    <div><strong>{{ $event->title }}</strong><span>{{ $event->school?->name }}</span></div>
                                </a>
                            </li>
                        @empty
                            <li><span class="box-empty">Upcoming school events will appear here when schools publish them.</span></li>
                        @endforelse
                    </ul>
                    <a class="foot" href="{{ route('events.index') }}">View all events</a>
                </div>
                @endif
            </aside>
            @endif
        </div>
        @endif

        @if($homepageDisplaySettings['news'])
        <section class="sec">
            <div class="head">
                <h2>Education <span>news headlines</span></h2>
                <a class="more" href="{{ route('news.index') }}">All news</a>
            </div>
            <div class="news">
                @if($leadArticle)
                    <a class="lead ph c" href="{{ route('news.show', $leadArticle) }}">
                        @if($leadArticle->cover_image)
                            <img src="{{ $leadArticle->cover_image }}" alt="{{ $leadArticle->title }}" loading="lazy">
                        @endif
                        <div class="cap">
                            <small>{{ $leadArticle->category?->name ?? 'Education news' }} · {{ $leadArticle->published_at?->diffForHumans() }}</small>
                            <h3>{{ $leadArticle->title }}</h3>
                            <div class="dots" aria-hidden="true"><i class="on"></i><i></i><i></i><i></i></div>
                        </div>
                    </a>
                @else
                    <div class="lead ph c"><div class="cap"><small>FindURSCH newsroom</small><h3>Education news and school announcements</h3><a class="btn btn-gold" href="{{ route('news.index') }}">Explore news</a></div></div>
                @endif

                <div class="card rows">
                    @forelse($newsArticles->take(3) as $article)
                        <a class="row" href="{{ route('news.show', $article) }}">
                            <div class="ph {{ $loop->iteration === 1 ? 'b' : ($loop->iteration === 2 ? 'd' : '') }}">
                                @if($article->cover_image)<img src="{{ $article->cover_image }}" alt="" loading="lazy">@else<span>F</span>@endif
                            </div>
                            <div><h4>{{ $article->title }}</h4><small>{{ $article->published_at?->format('d M Y') }}</small></div>
                        </a>
                    @empty
                        <p class="box-empty">Published education headlines will appear here.</p>
                    @endforelse
                    <div class="mini"><span>↑</span><span>↓</span></div>
                </div>

                @if($latestSchoolUpdates->first())
                    @php
                        $featuredSchoolUpdate = $latestSchoolUpdates->first();
                    @endphp
                    <a class="card prem" href="{{ route('school-news.show', $featuredSchoolUpdate) }}">
                        <div class="ph d">
                            @if($featuredSchoolUpdate->school?->getFirstMediaUrl('cover'))
                                <img src="{{ $featuredSchoolUpdate->school->getFirstMediaUrl('cover') }}" alt="" loading="lazy">
                            @else
                                <span class="ph-initials">{{ strtoupper(mb_substr($featuredSchoolUpdate->school?->name ?? 'FS', 0, 2)) }}</span>
                            @endif
                        </div>
                        <span class="flag">School news</span>
                        <div class="b">
                            <h3>{{ $featuredSchoolUpdate->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($featuredSchoolUpdate->description, 125) }}</p>
                            <small><span>{{ $featuredSchoolUpdate->published_at?->format('d M Y') }}</span><span>{{ $featuredSchoolUpdate->school?->name }}</span></small>
                        </div>
                    </a>
                @else
                    <a class="card prem prem-empty" href="{{ route('news.index') }}">
                        <div class="ph d"><span class="ph-initials">FS</span></div>
                        <div class="b"><h3>News from school communities</h3><p>School announcements will be featured here when they are published.</p><small><span>FindURSCH</span><span>Latest updates</span></small></div>
                    </a>
                @endif
            </div>
        </section>
        @endif

        @if($homepageDisplaySettings['videos'] || $homepageDisplaySettings['scholarships'] || $homepageDisplaySettings['sidebar'] || $homepageDisplaySettings['banner_ads'])
        <div class="cols sec" @if(!$homepageDisplaySettings['sidebar']) style="grid-template-columns:minmax(0,1fr)" @endif>
            <div class="stack">
                @if($homepageDisplaySettings['banner_ads'])
                <div class="band">
                    <div><small>Advertise with us</small><h3>Reach parents and students every month</h3><p>Promote your school, business or service to families across Uganda.</p></div>
                    <div class="acts"><a class="btn btn-gold" href="{{ route('contact') }}">Advertise</a><a class="btn btn-ghost" href="{{ route('contact') }}">Send inquiry</a></div>
                </div>
                @endif

                @if($homepageDisplaySettings['videos'])
                <div>
                    <div class="head"><h2>Featured <span>school videos</span></h2><a class="more" href="{{ route('schools.index') }}">All videos</a></div>
                    @if($featuredVideos->isNotEmpty())
                        <div class="vids">
                            @foreach($featuredVideos as $video)
                                <a class="card vid" href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer">
                                    <div class="ph {{ $loop->iteration === 1 ? 'c' : 'b' }}">
                                        <img src="https://img.youtube.com/vi/{{ rawurlencode($video->youtube_video_id) }}/hqdefault.jpg" alt="" loading="lazy">
                                        <i class="play"></i>
                                    </div>
                                    <div class="b"><small>{{ $video->school?->name ?? 'School' }}</small><h4>{{ $video->title }}</h4><span>Watch video ↗</span></div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="card video-empty"><p>Approved school videos will appear here when participating schools share them.</p><a href="{{ route('schools.index') }}" class="more">Explore schools →</a></div>
                    @endif
                </div>
                @endif

                @if($homepageDisplaySettings['scholarships'])
                <div class="band edge">
                    <div>
                        <h3>Scholarships &amp; bursaries</h3>
                        <p>Browse funding opportunities for Ugandan students at every level.</p>
                        <div class="chips">
                            <a href="{{ route('scholarships.index') }}">School bursaries</a>
                            <a href="{{ route('scholarships.index') }}">Student funding</a>
                            <a href="{{ route('scholarships.index') }}">Award opportunities</a>
                            <a href="{{ route('scholarships.index') }}">Published deadlines</a>
                        </div>
                        @if($featuredBursaries->isNotEmpty())
                            <div class="bursary-preview">
                                @foreach($featuredBursaries->take(2) as $bursary)
                                    <a href="{{ route('scholarships.show', $bursary) }}"><b>{{ $bursary->title }}</b><small>{{ $bursary->deadline ? 'Closes '.$bursary->deadline->format('d M Y') : 'Contact the school' }}</small></a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <a class="btn btn-ghost" href="{{ route('scholarships.index') }}">Browse</a>
                </div>
                @endif
            </div>

            @if($homepageDisplaySettings['sidebar'])
            <aside>
                <div class="card box jobs">
                    <h5>Latest jobs</h5>
                    <div class="jobs-empty">
                        <b>School job listings are coming soon.</b>
                        <span>Contact FindURSCH to enquire about sharing a vacancy.</span>
                    </div>
                    <a class="foot" href="{{ route('contact') }}">Enquire about job listings</a>
                </div>

                @if($homepageDisplaySettings['banner_ads'])
                <a class="promo" href="{{ route('contact') }}">
                    <small>Advertise</small>
                    <h3>Put your brand in front of parents</h3>
                    <p>Reach families across Uganda every month with a FindURSCH promotion.</p>
                    <span class="btn btn-gold">Advertise with us</span>
                </a>
                @endif

                @if($homepageDisplaySettings['reviews'])
                <div class="card box">
                    <h5>Stories from schools &amp; families</h5>
                    @if($featuredReviews->first())
                        @php
                            $homeReview = $featuredReviews->first();
                        @endphp
                        <div class="quote">
                            <div class="st" aria-label="{{ $homeReview->rating }} out of 5 stars">
                                @for($star = 1; $star <= 5; $star++){{ $star <= (int) $homeReview->rating ? '★' : '☆' }}@endfor
                            </div>
                            <p>“{{ \Illuminate\Support\Str::limit($homeReview->body, 190) }}”</p>
                            <div class="who"><i>{{ strtoupper(mb_substr($homeReview->user?->name ?? 'P', 0, 1)) }}</i><span><b>{{ $homeReview->user?->name ?? 'FindURSCH parent' }}</b>{{ $homeReview->school?->name }}</span></div>
                            <div class="dots" aria-hidden="true"><i></i><i class="on"></i><i></i></div>
                        </div>
                    @else
                        <div class="quote"><p>Approved stories and reviews from school communities will appear here.</p><a class="more" href="{{ route('schools.index') }}">Explore schools</a></div>
                    @endif
                </div>
                @endif
            </aside>
            @endif
        </div>
        @endif
    </main>

    @if($homepageDisplaySettings['insights'])
    <section class="tinted">
        <div class="wrap">
            <div class="head"><h2>Educational <span>insights</span></h2><a class="more" href="{{ route('articles.index') }}">All articles</a></div>
            <div class="ins">
                @if($insightArticles->first())
                    @php
                        $primaryInsight = $insightArticles->first();
                    @endphp
                    <a class="card big" href="{{ route('articles.show', $primaryInsight) }}">
                        <div class="ph d">
                            @if($primaryInsight->cover_image)<img src="{{ $primaryInsight->cover_image }}" alt="" loading="lazy">@else<span class="ph-initials">FS</span>@endif
                        </div>
                        <div class="b"><h3>{{ $primaryInsight->title }}</h3><p>{{ \Illuminate\Support\Str::limit($primaryInsight->excerpt, 135) }}</p><small><span>{{ $primaryInsight->published_at?->format('M Y') }}</span><span>{{ $primaryInsight->read_time_minutes }} min read</span></small></div>
                    </a>
                @else
                    <div class="card big big-empty"><div class="ph d"><span class="ph-initials">FS</span></div><div class="b"><h3>Educational insights</h3><p>Helpful articles and guidance will appear here as they are published.</p></div></div>
                @endif

                <div class="card rows">
                    @forelse($insightArticles->skip(1)->take(3) as $article)
                        <a class="row" href="{{ route('articles.show', $article) }}">
                            <div class="ph {{ $loop->iteration === 1 ? 'b' : ($loop->iteration === 2 ? '' : 'c') }}">
                                @if($article->cover_image)<img src="{{ $article->cover_image }}" alt="" loading="lazy">@else<span>F</span>@endif
                            </div>
                            <div><h4>{{ $article->title }}</h4><small>{{ $article->published_at?->format('M Y') }}</small></div>
                        </a>
                    @empty
                        <p class="box-empty">More educational articles will appear here.</p>
                    @endforelse
                    <div class="mini"><span>↑</span><span>↓</span></div>
                </div>

                @if($spotlightSchool)
                    <a class="spl" href="{{ route('schools.show', $spotlightSchool) }}">
                        @if($spotlightSchool->getFirstMediaUrl('cover'))<img src="{{ $spotlightSchool->getFirstMediaUrl('cover') }}" alt="" loading="lazy">@endif
                        <div><small>School spotlight</small><h3>{{ $spotlightSchool->name }}</h3><p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Explore this school profile on FindURSCH.', 100) }}</p><span class="btn btn-gold btn-block">Learn more about this school</span></div>
                    </a>
                @else
                    <div class="spl"><div><small>School discovery</small><h3>Find a school that fits</h3><p>Explore profiles and compare schools across Uganda.</p><a class="btn btn-gold btn-block" href="{{ route('schools.index') }}">Browse schools</a></div></div>
                @endif
            </div>
        </div>
    </section>
    @endif

    @if($homepageDisplaySettings['banner_ads'])
    <div class="banner">
        @forelse($homeBannerAds as $ad)
            <a class="ad ad-wide ad-managed" href="{{ $ad->destination_url ?: route('schools.index') }}" target="{{ $ad->destination_url ? '_blank' : '_self' }}" rel="{{ $ad->destination_url ? 'noopener noreferrer' : '' }}">
                @if($ad->image_url)<img src="{{ $ad->image_url }}" alt="{{ $ad->image_alt ?: $ad->title }}" loading="lazy">@endif
                <span>{{ $ad->title }}</span><small>{{ $ad->description }}</small><b class="ad-cta">{{ $ad->cta_label ?: 'Learn more' }} →</b>
            </a>
        @empty
            <a class="ad ad-wide" href="{{ route('contact') }}"><span>Promote your school or service</span><small>Reach parents and students across Uganda</small><b class="ad-cta">Advertise with us →</b></a>
            <a class="ad ad-alt" href="{{ route('register') }}"><span>School profile promotion</span><small>Help families discover your school</small><b class="ad-cta">List your school →</b></a>
        @endforelse
    </div>
    @endif

    @if($homepageDisplaySettings['cta'])
    <section class="cta">
        <h2>Is your school listed?</h2>
        <p>Join schools across Uganda reaching parents, students and educators every day.</p>
        <div class="acts"><a class="btn btn-white" href="{{ route('register') }}">Register your school</a><a class="btn btn-ghost" href="{{ route('schools.index') }}">View school directory</a></div>
    </section>
    @endif
</div>