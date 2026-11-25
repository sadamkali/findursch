@php
    $school = $post->school;
    $schoolLogoUrl = $school?->getFirstMediaUrl('logo');
    $postImageUrl = $post->image_url ?: $school?->getFirstMediaUrl('cover');
    $districtName = $school?->district?->name;
    $regionName = $school?->district?->region?->name;
    $locationParts = collect([
        $school?->town,
        $school?->subCounty?->name,
        $districtName,
        $regionName,
    ])->filter()->unique()->values();
    $locationLabel = $locationParts->isNotEmpty() ? $locationParts->join(', ') : 'Uganda';
    $schoolAbout = trim((string) ($school?->tagline ?: $school?->description));
@endphp

<div class="resource-page school-news-detail-page">
    <a class="blogs-rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>
    <x-site-ad-banner placement="top" />

    <div class="blogs-wrap resource-detail-wrap school-news-wrap">
        <nav class="resource-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>›</span>
            <a href="{{ route('news.index') }}">Education news</a>
            <span>›</span>
            @if($school)
                <a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a>
                <span>›</span>
            @endif
            <b>{{ $post->title }}</b>
        </nav>

        <header class="school-news-heading">
            <div class="school-news-heading-tags">
                <span>School news</span>
                <span>Published {{ $post->published_at?->format('d F Y') }}</span>
            </div>
            <h1>{{ $post->title }}</h1>
            <p>{{ \Illuminate\Support\Str::limit(strip_tags($post->description), 260) }}</p>
            <div class="school-news-heading-meta">
                @if($school)
                    <a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a>
                @endif
                <span>{{ $locationLabel }}</span>
                <span>{{ $readingMinutes }} min read</span>
            </div>
        </header>

        <div class="resource-detail-grid school-news-detail-grid">
            <article class="resource-detail-content school-news-article">
                @if($postImageUrl)
                    <figure class="school-news-cover">
                        <img src="{{ $postImageUrl }}" alt="{{ $post->image_alt ?: (($school?->name ?? 'School').' news: '.$post->title) }}">
                    </figure>
                @endif

                <div class="article-byline school-news-byline">
                    <span class="article-avatar school-news-avatar">
                        @if($schoolLogoUrl)
                            <img src="{{ $schoolLogoUrl }}" alt="{{ $school?->name }} logo">
                        @else
                            {{ strtoupper(mb_substr($school?->name ?? 'S', 0, 1)) }}
                        @endif
                    </span>
                    <span>
                        <b>{{ $school?->name ?? 'School community' }}</b>
                        <small>Official school update · Published {{ $post->published_at?->format('d M Y, g:i A') }}</small>
                    </span>
                    @if($school)
                        <a class="school-news-profile-link" href="{{ route('schools.show', $school) }}">View school profile</a>
                    @endif
                </div>

                <div class="resource-share school-news-share" x-data="{ copied: false }">
                    <span>SHARE THIS UPDATE</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                    <a href="https://wa.me/?text={{ urlencode($post->title.' '.request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer">X</a>
                    <button type="button"
                        @click="navigator.clipboard.writeText(window.location.href).then(() => { copied = true; setTimeout(() => copied = false, 1800) })"
                        x-text="copied ? 'Link copied' : 'Copy link'">Copy link</button>
                </div>

                <section class="resource-prose school-news-prose">
                    <div class="school-news-section-kicker">The update</div>
                    <h2>From the school</h2>
                    <div class="school-news-article-body">{!! nl2br(e($post->description)) !!}</div>
                </section>

                @if($school)
                    <section class="school-news-school-profile">
                        <div class="school-news-school-profile-head">
                            @if($schoolLogoUrl)
                                <img src="{{ $schoolLogoUrl }}" alt="{{ $school->name }} logo">
                            @else
                                <span>{{ strtoupper(mb_substr($school->name, 0, 1)) }}</span>
                            @endif
                            <div>
                                <small>ABOUT THE SCHOOL</small>
                                <h2>{{ $school->name }}</h2>
                                <p>{{ $school->type?->name ?? 'School' }} · {{ $locationLabel }}</p>
                            </div>
                        </div>

                        @if($schoolAbout !== '')
                            <p class="school-news-school-description">{{ \Illuminate\Support\Str::limit(strip_tags($schoolAbout), 360) }}</p>
                        @endif

                        <div class="school-news-school-facts">
                            @if($school->founded_year)
                                <span><b>Established</b>{{ $school->founded_year }}</span>
                            @endif
                            @if($school->ownership)
                                <span><b>Ownership</b>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $school->ownership)) }}</span>
                            @endif
                            @if($school->boarding_type)
                                <span><b>Boarding</b>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $school->boarding_type)) }}</span>
                            @endif
                            @if($school->gender_policy)
                                <span><b>School type</b>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $school->gender_policy)) }}</span>
                            @endif
                        </div>

                        <a class="school-news-school-button" href="{{ route('schools.show', $school) }}">Explore full school profile <span aria-hidden="true">→</span></a>
                    </section>
                @endif
            </article>

            <aside class="resource-detail-sidebar school-news-sidebar">
                <section class="school-news-info-card">
                    <small class="school-news-card-eyebrow">ARTICLE INFORMATION</small>
                    <h2>At a glance</h2>
                    <dl>
                        <div>
                            <dt>Published</dt>
                            <dd>{{ $post->published_at?->format('l, d F Y') ?? 'Date not available' }}</dd>
                        </div>
                        <div>
                            <dt>Time</dt>
                            <dd>{{ $post->published_at?->format('g:i A') ?? 'Not available' }}</dd>
                        </div>
                        <div>
                            <dt>Reading time</dt>
                            <dd>{{ $readingMinutes }} minute{{ $readingMinutes === 1 ? '' : 's' }}</dd>
                        </div>
                        @if($school)
                            <div>
                                <dt>Published by</dt>
                                <dd><a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a></dd>
                            </div>
                        @endif
                        <div>
                            <dt>Location</dt>
                            <dd>{{ $locationLabel }}</dd>
                        </div>
                    </dl>
                </section>

                @if($school)
                    <section class="school-news-contact-card">
                        <small class="school-news-card-eyebrow">SCHOOL CONTACT</small>
                        <h2>Contact the school</h2>
                        @if($school->address || $school->nearest_landmark)
                            <p class="school-news-contact-location">
                                {{ $school->address ?: $school->nearest_landmark }}
                                @if($school->town && $school->address), {{ $school->town }}@endif
                            </p>
                        @endif
                        <div class="school-news-contact-actions">
                            @if($school->phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $school->phone) }}">Call {{ $school->phone }}</a>
                            @endif
                            @if($school->email)
                                <a href="mailto:{{ $school->email }}">Email the school</a>
                            @endif
                            @if($school->website)
                                <a href="{{ \Illuminate\Support\Str::startsWith($school->website, ['http://', 'https://']) ? $school->website : 'https://'.$school->website }}" target="_blank" rel="noopener noreferrer">Visit school website ↗</a>
                            @endif
                            @if($school->maps_url)
                                <a href="{{ $school->maps_url }}" target="_blank" rel="noopener noreferrer">View location on map ↗</a>
                            @endif
                            <a class="school-news-contact-primary" href="{{ route('schools.show', $school) }}">Full school profile</a>
                        </div>
                        @if(!$school->phone && !$school->email && !$school->website && !$school->maps_url)
                            <p class="school-news-no-contact">The school has not published direct contact details yet.</p>
                        @endif
                    </section>
                @endif

                <x-site-ad-banner placement="side" />

                <section class="school-news-explore-card">
                    <small class="school-news-card-eyebrow">EXPLORE FINDURSCH</small>
                    <a href="{{ route('news.index') }}">Education news <span>→</span></a>
                    <a href="{{ route('events.index') }}">School events <span>→</span></a>
                    <a href="{{ route('scholarships.index') }}">Scholarships and bursaries <span>→</span></a>
                    <a href="{{ route('schools.index') }}">Find a school <span>→</span></a>
                </section>
            </aside>
        </div>

        @if($related->isNotEmpty())
            <section class="resource-related article-related school-news-related">
                <div class="resource-section-head">
                    <div>
                        <small>Keep reading</small>
                        <h2>{{ $relatedHeading }}</h2>
                    </div>
                    <a href="{{ route('news.index') }}">All education news</a>
                </div>
                <div class="resource-related-grid">
                    @foreach($related as $item)
                        @php
                            $relatedImage = $item->image_url ?: $item->school?->getFirstMediaUrl('cover');
                        @endphp
                        <a href="{{ route('school-news.show', $item) }}" class="school-news-related-card">
                            @if($relatedImage)
                                <img src="{{ $relatedImage }}" alt="{{ $item->image_alt ?: $item->title }}" loading="lazy">
                            @endif
                            <small>{{ $item->published_at?->format('d M Y') }}</small>
                            <b>{{ $item->title }}</b>
                            <span>{{ $item->school?->name ?? 'School community' }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="school-news-bottom-ad">
            <x-site-ad-banner placement="foot1" />
        </div>
    </div>
</div>
