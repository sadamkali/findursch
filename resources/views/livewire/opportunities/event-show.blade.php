<div class="resource-page">
    <a class="blogs-rtab" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>
    <div class="blogs-ad-slim"><span>Promote your education event</span><small>Reach parents, students and school communities across Uganda · <a href="{{ route('contact') }}">Contact our team</a></small></div>
    <main class="blogs-wrap resource-detail-wrap">
        <nav class="resource-crumbs"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('events.index') }}">Events</a><span>›</span><b>{{ $event->title }}</b></nav>
        <div class="resource-detail-grid">
            <article class="resource-detail-content">
                <div class="resource-tags"><span>School event</span><span>{{ $event->starts_at && $event->starts_at->isPast() ? 'Past event' : 'Upcoming' }}</span>@if($event->venue)<span>{{ $event->venue }}</span>@endif</div>
                <h1>{{ $event->title }}</h1>
                <p class="resource-detail-lead">{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 300) }}</p>
                <div class="resource-event-summary">
                    <div class="resource-event-poster">
                        @if($event->school?->getFirstMediaUrl('cover'))
                            <img src="{{ $event->school->getFirstMediaUrl('cover') }}" alt="{{ $event->school->name }}">
                        @else
                            <span>FindURSCH<br>Community Event</span>
                        @endif
                    </div>
                    <dl class="resource-facts">
                        <div><dt>Date</dt><dd>{{ $event->starts_at?->format('l, d F Y') ?? 'To be confirmed' }}</dd></div>
                        <div><dt>Time</dt><dd>{{ $event->starts_at?->format('g:i A') ?? 'To be confirmed' }}@if($event->ends_at) – {{ $event->ends_at->format('g:i A') }}@endif</dd></div>
                        <div><dt>Venue</dt><dd>{{ $event->venue ?: 'Contact the organiser for venue details' }}</dd></div>
                        <div><dt>Organiser</dt><dd><a href="{{ route('schools.show', $event->school) }}">{{ $event->school?->name ?? 'School community' }}</a></dd></div>
                    </dl>
                </div>
                <div class="resource-share"><span>SHARE</span><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener">Facebook</a><a href="https://wa.me/?text={{ urlencode($event->title.' '.request()->fullUrl()) }}" target="_blank" rel="noopener">WhatsApp</a><button type="button" x-data @click="navigator.clipboard.writeText(window.location.href)">Copy link</button></div>
                <section class="resource-prose">
                    <h2>About the event</h2>
                    <div>{!! nl2br(e($event->description)) !!}</div>
                </section>
                <section class="resource-organiser">
                    <h2>Contact the organiser</h2>
                    <p>{{ $event->school?->name }}</p>
                    <div class="resource-contact-lines">
                        @if($event->school?->phone)<a href="tel:{{ $event->school->phone }}">{{ $event->school->phone }}</a>@endif
                        @if($event->school?->email)<a href="mailto:{{ $event->school->email }}">{{ $event->school->email }}</a>@endif
                        @if($event->school?->district)<span>{{ $event->school->district->name }}{{ $event->school->district->region ? ', '.$event->school->district->region->name : '' }}</span>@endif
                    </div>
                </section>
            </article>
            <aside class="resource-detail-sidebar">
                <div class="resource-event-box">
                    <small>Event date</small>
                    <h2>{{ $event->starts_at?->format('D, d M Y') ?? 'Date to be confirmed' }}</h2>
                    <p>{{ $event->starts_at?->format('g:i A') ?? 'Time to be confirmed' }}@if($event->ends_at) – {{ $event->ends_at->format('g:i A') }}@endif</p>
                    <dl><div><dt>Venue</dt><dd>{{ $event->venue ?: 'Contact organiser' }}</dd></div><div><dt>Organiser</dt><dd>{{ $event->school?->name }}</dd></div><div><dt>Format</dt><dd>Contact organiser</dd></div></dl>
                    @if($event->school?->phone)<a class="blogs-btn blogs-btn-brand" href="https://wa.me/{{ preg_replace('/\D+/', '', $event->school->phone) }}">Ask the organiser</a>@else<a class="blogs-btn blogs-btn-brand" href="{{ route('schools.show', $event->school) }}">View school profile</a>@endif
                </div>
                <div class="resource-sidebar-promo"><small>School discovery</small><h3>Explore the school hosting this event</h3><p>View its profile, facilities and admission information.</p><a class="blogs-btn blogs-btn-gold" href="{{ route('schools.show', $event->school) }}">View school profile</a></div>
            </aside>
        </div>
        @if($similarEvents->isNotEmpty())
            <section class="resource-related"><div class="resource-section-head"><div><small>More to explore</small><h2>Similar events</h2></div><a href="{{ route('events.index') }}">All events →</a></div>
                <div class="resource-related-grid">@foreach($similarEvents as $item)<a href="{{ route('events.show', $item) }}"><small>{{ $item->starts_at?->format('d M Y') }}</small><b>{{ $item->title }}</b><span>{{ $item->school?->name }}</span></a>@endforeach</div>
            </section>
        @endif
    </main>
</div>