<div class="event-reference">
    <a class="rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

    <div class="wrap">
        <a class="ad slim" href="{{ route('contact') }}">Promote your education event<small>Reach parents, students and school communities across Uganda · Contact our team</small></a>

        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('events.index') }}">Events</a><span>›</span><b>{{ $event->title }}</b>
        </nav>

        @php
            $eventEnded = $event->ends_at ? $event->ends_at->isPast() : ($event->starts_at ? $event->starts_at->isPast() : false);
            $eventWhatsapp = $event->school?->whatsapp ?: $event->school?->phone;
            $eventWhatsappNumber = $eventWhatsapp ? preg_replace('/\D+/', '', $eventWhatsapp) : null;
        @endphp

        <div class="cols">
            <article>
                <div class="tags">
                    <span class="tag">School event</span>
                    @if($event->venue)<span class="tag">{{ $event->venue }}</span>@endif
                    @if($event->starts_at)
                        <span class="tag {{ $eventEnded ? 'dk' : 'soon' }}">{{ $eventEnded ? 'Past event' : 'Upcoming' }}</span>
                    @else
                        <span class="tag a">Date to be confirmed</span>
                    @endif
                </div>

                <h1>{{ $event->title }}</h1>

                <div class="hd">
                    <div class="poster ph">
                        @if($event->image_url || $event->school?->getFirstMediaUrl('cover'))
                            <img src="{{ $event->image_url ?: $event->school?->getFirstMediaUrl('cover') }}" alt="{{ $event->image_alt ?: (($event->school?->name ?? $event->title).' event poster') }}">
                        @else
                            <div class="poster-placeholder"><strong>FindURSCH</strong><small>School community event</small></div>
                        @endif
                    </div>
                    <div>
                        <p class="exc">{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 300) }}</p>
                        <dl class="info">
                            <div><dt>Date</dt><dd>{{ $event->starts_at?->format('l, d F Y') ?? 'To be confirmed' }}</dd></div>
                            <div><dt>Time</dt><dd>{{ $event->starts_at?->format('g:i A') ?? 'To be confirmed' }}@if($event->ends_at) – {{ $event->ends_at->format('g:i A') }}@endif</dd></div>
                            <div><dt>Venue</dt><dd>{{ $event->venue ?: 'Contact the organiser for venue details' }}</dd></div>
                            <div><dt>Organiser</dt><dd>@if($event->school)<a href="{{ route('schools.show', $event->school) }}">{{ $event->school->name }}</a>@else School community @endif</dd></div>
                        </dl>
                        @if($eventWhatsappNumber)
                            <a class="btn btn-wa" href="https://wa.me/{{ $eventWhatsappNumber }}?text={{ urlencode('Hello, I would like to enquire about '.$event->title.'.') }}" target="_blank" rel="noopener noreferrer">Talk to the organiser on WhatsApp</a>
                        @elseif($event->school)
                            <a class="btn btn-line" href="{{ route('schools.show', $event->school) }}">View organiser profile</a>
                        @endif
                    </div>
                </div>

                <div class="share" x-data="{ copied: false }">
                    <span>SHARE:</span>
                    <a class="btn sm fb" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                    <a class="btn sm btn-wa" href="https://wa.me/?text={{ urlencode($event->title.' '.request()->fullUrl()) }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    <a class="btn sm xx" href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($event->title) }}" target="_blank" rel="noopener noreferrer">X</a>
                    <button class="btn sm cp" type="button" @click="navigator.clipboard.writeText(window.location.href).then(() => { copied = true; setTimeout(() => copied = false, 1800) })" x-text="copied ? 'Link copied' : 'Copy link'">Copy link</button>
                </div>

                <section class="body">
                    <h2>About the event</h2>
                    <div class="event-description">{!! nl2br(e($event->description)) !!}</div>
                    <div class="strip">
                        <div><small>Premium schools</small><h4>Be the first school parents see</h4><p>Top of search, homepage feature and spotlight adverts.</p></div>
                        <a class="btn btn-brand" href="{{ route('contact') }}">See school plans</a>
                    </div>
                </section>

                <section class="contact">
                    <h2>Contact the organiser</h2>
                    <p>{{ $event->school?->name ?? 'School community' }}</p>
                    <div class="event-contact-lines">
                        @if($event->school?->phone)<a href="tel:{{ $event->school->phone }}">{{ $event->school->phone }}</a>@endif
                        @if($event->school?->email)<a href="mailto:{{ $event->school->email }}">{{ $event->school->email }}</a>@endif
                        @if($event->school?->district)<span>{{ $event->school->district->name }}@if($event->school->district->region), {{ $event->school->district->region->name }}@endif</span>@endif
                        @if(!$event->school?->phone && !$event->school?->email)<span>Contact information has not been published for this organiser.</span>@endif
                    </div>
                </section>

                <div class="tagbox">
                    TAGS:
                    <a href="{{ route('events.index') }}">School events</a>
                    @if($event->school?->district)<a href="{{ route('district.show', $event->school->district) }}">{{ $event->school->district->name }}</a>@endif
                </div>
            </article>

            <aside>
                <div class="ev">
                    <small class="k">Event date</small>
                    <h3>{{ $event->starts_at?->format('D, d M Y') ?? 'Date to be confirmed' }}</h3>
                    <p class="t">{{ $event->starts_at?->format('g:i A') ?? 'Time to be confirmed' }}@if($event->ends_at) – {{ $event->ends_at->format('g:i A') }}@endif</p>
                    <dl>
                        <div><dt>Venue</dt><dd>{{ $event->venue ?: 'Contact organiser' }}</dd></div>
                        <div><dt>Organiser</dt><dd>{{ $event->school?->name ?? 'School community' }}</dd></div>
                        <div><dt>Status</dt><dd>{{ $eventEnded ? 'Past event' : ($event->starts_at ? 'Upcoming' : 'To be confirmed') }}</dd></div>
                    </dl>
                    <p class="state {{ $eventEnded ? 'end' : 'soon' }}">{{ $eventEnded ? 'This event has ended' : ($event->starts_at ? 'Upcoming event' : 'Event details pending') }}</p>
                    @if($eventWhatsappNumber && !$eventEnded)
                        <a class="btn btn-line btn-block" href="https://wa.me/{{ $eventWhatsappNumber }}?text={{ urlencode('Hello, I would like to enquire about '.$event->title.'.') }}" target="_blank" rel="noopener noreferrer">Ask on WhatsApp</a>
                    @elseif($event->school)
                        <a class="btn btn-line btn-block" href="{{ route('schools.show', $event->school) }}">View school profile</a>
                    @endif
                </div>

                @if($upcomingEvents->isNotEmpty())
                    <div class="card box">
                        <h5>Upcoming events</h5>
                        <ul>
                            @foreach($upcomingEvents as $upcoming)
                                <li><a href="{{ route('events.show', $upcoming) }}">
                                    <span class="date">{{ $upcoming->starts_at?->format('d') }}<small>{{ strtoupper($upcoming->starts_at?->format('M')) }}</small></span>
                                    <div><strong>{{ $upcoming->title }}</strong><span>{{ $upcoming->school?->district?->name ?? $upcoming->school?->name }}</span></div>
                                </a></li>
                            @endforeach
                        </ul>
                        <a class="foot" href="{{ route('events.index') }}">See all upcoming events</a>
                    </div>
                @endif

                <a class="ad side" href="{{ route('contact') }}">Advertise with FindURSCH<small>Reach parents and school communities across Uganda</small></a>

                @if($spotlightSchool)
                    <a class="spl" href="{{ route('schools.show', $spotlightSchool) }}">
                        @if($spotlightSchool->getFirstMediaUrl('cover'))<img src="{{ $spotlightSchool->getFirstMediaUrl('cover') }}" alt="{{ $spotlightSchool->name }}" loading="lazy">@endif
                        <div><small>School spotlight</small><h3>{{ $spotlightSchool->name }}</h3><p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Explore this featured school profile.', 105) }}</p><span class="btn btn-gold btn-block">Learn more about this school</span></div>
                    </a>
                @else
                    <a class="spl spl-placeholder" href="{{ route('schools.index') }}"><div><small>School discovery</small><h3>Find a school in Uganda</h3><p>Explore verified school profiles across the country.</p><span class="btn btn-gold btn-block">Browse schools</span></div></a>
                @endif

                <div class="band">
                    <h3>Get event updates</h3>
                    <p>Follow new school events, open days and education activities published on FindURSCH.</p>
                    <a class="btn btn-gold btn-block" href="{{ route('events.index') }}">Browse school events</a>
                </div>
            </aside>
        </div>

        @if($similarEvents->isNotEmpty())
            <div class="simh"><span>Similar events</span></div>
            <div class="sim">
                @foreach($similarEvents as $item)
                    <a class="card ec" href="{{ route('events.show', $item) }}">
                        <div class="ph">
                            @if($item->school?->getFirstMediaUrl('cover'))<img src="{{ $item->school->getFirstMediaUrl('cover') }}" alt="{{ $item->school->name }}" loading="lazy">@else<span class="event-card-mark">EVENT</span>@endif
                        </div>
                        <div class="b">
                            <h3>{{ $item->title }}</h3>
                            <p>{{ $item->school?->name ?? 'School community' }}@if($item->venue), {{ $item->venue }}@endif</p>
                            <p>{{ $item->starts_at?->format('g:i A') ?? 'Time to be confirmed' }}@if($item->ends_at) – {{ $item->ends_at->format('g:i A') }}@endif</p>
                            <p>{{ $item->starts_at?->format('d F Y') ?? 'Date to be confirmed' }}</p>
                        </div>
                        <div class="f"><span>{{ $item->school?->district?->name ?? 'Uganda' }}</span><b>Details →</b></div>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="banner">
            <a class="ad" href="{{ route('contact') }}">Wide advertising placement<small>Enquire about FindURSCH banner advertising</small></a>
            <a class="ad alt" href="{{ route('register') }}">List your school<small>Help families discover your school</small></a>
        </div>
    </div>
</div>
