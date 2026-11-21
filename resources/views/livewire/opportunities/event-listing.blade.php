<div class="event-directory-reference">
    <a class="ed-register" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

    <header class="ed-hero">
        <div class="ed-shell">
            <nav class="ed-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a><span>›</span><b>Events</b>
            </nav>
            <p class="ed-eyebrow">FindURSCH community calendar</p>
            <h1>School Events <em>&amp; Open Days</em></h1>
            <p class="ed-intro">Discover school open days, education fairs, conferences and activities shared by school communities across Uganda.</p>
            <div class="ed-hero-note"><span></span> School open days <i>•</i> Education fairs <i>•</i> Community activities</div>
        </div>
    </header>

    <div class="ed-ad">
        <div><small>FOR SCHOOLS &amp; ORGANISERS</small><strong>Promote your education event</strong><span>Reach parents, students and school communities across Uganda</span></div>
        <a href="{{ route('contact') }}">Contact our team <b aria-hidden="true">→</b></a>
    </div>

    <main class="ed-shell ed-main">
        <section class="ed-toolbar" aria-label="Search and filter events">
            <form class="ed-search" method="GET" action="{{ route('events.index') }}">
                <span aria-hidden="true">⌕</span>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search events, schools or venues" aria-label="Search events">
                <button type="submit">Search events</button>
            </form>
            <nav class="ed-period" aria-label="Event date filter">
                <a href="{{ route('events.index', array_filter(['q' => $search ?: null])) }}" @class(['active' => $period === 'upcoming'])>Upcoming</a>
                <a href="{{ route('events.index', array_filter(['period' => 'all', 'q' => $search ?: null])) }}" @class(['active' => $period === 'all'])>All events</a>
                <a href="{{ route('events.index', array_filter(['period' => 'past', 'q' => $search ?: null])) }}" @class(['active' => $period === 'past'])>Past events</a>
            </nav>
        </section>

        <section class="ed-results-heading">
            <div><small>COMMUNITY CALENDAR</small><h2>{{ $period === 'past' ? 'Past events' : ($period === 'all' ? 'All school events' : 'Upcoming events') }}</h2></div>
            <span>{{ number_format($events->total()) }} {{ \Illuminate\Support\Str::plural('event', $events->total()) }}</span>
        </section>

        @if($events->isNotEmpty())
            <div class="ed-grid">
                @foreach($events as $event)
                    @php
                        $eventEnded = $event->ends_at ? $event->ends_at->isPast() : ($event->starts_at ? $event->starts_at->isPast() : false);
                    @endphp
                    <article class="ed-card" wire:key="event-listing-{{ $event->id }}">
                        <a class="ed-card-media" href="{{ route('events.show', $event) }}" aria-label="View {{ $event->title }}">
                            @if($event->image_url || $event->school?->getFirstMediaUrl('cover'))
                                <img src="{{ $event->image_url ?: $event->school?->getFirstMediaUrl('cover') }}" alt="{{ $event->image_alt ?: ($event->school?->name ?? $event->title) }}" loading="lazy">
                            @else
                                <span class="ed-card-placeholder"><b>FindURSCH</b><small>School community event</small></span>
                            @endif
                            @if($event->starts_at)<span class="ed-date-chip">{{ $event->starts_at->format('d M Y') }}</span>@endif
                            <span class="ed-status {{ $eventEnded ? 'past' : 'upcoming' }}">{{ $eventEnded ? 'Past event' : 'Upcoming' }}</span>
                        </a>
                        <div class="ed-card-content">
                            <p class="ed-card-place">{{ $event->school?->name ?? 'FindURSCH community' }} <span>·</span> {{ $event->venue ?: ($event->school?->district?->name ?? 'Uganda') }}</p>
                            <h3><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h3>
                            <p class="ed-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 135) }}</p>
                            <div class="ed-card-footer">
                                <span>{{ $event->starts_at?->format('D, d M Y · g:i A') ?? 'Date to be confirmed' }}</span>
                                <a href="{{ route('events.show', $event) }}">View event <b aria-hidden="true">→</b></a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="ed-pagination">{{ $events->links() }}</div>
        @else
            <section class="ed-empty">
                <span class="ed-empty-icon" aria-hidden="true">◎</span>
                <p class="ed-eyebrow">THE COMMUNITY CALENDAR</p>
                <h2>No {{ $period === 'past' ? 'past' : 'upcoming' }} events published yet</h2>
                <p>School events will appear here when an organiser publishes them. Check back for open days, education fairs and school activities.</p>
                @if($search !== '')
                    <a class="ed-button ed-button-outline" href="{{ route('events.index') }}">Clear search</a>
                @else
                    <a class="ed-button ed-button-outline" href="{{ route('events.index', ['period' => 'all']) }}">View all events</a>
                @endif
            </section>
        @endif

        @if($period !== 'past' && $pastEvents->isNotEmpty())
            <section class="ed-past-section">
                <div class="ed-results-heading"><div><small>PREVIOUSLY HELD</small><h2>Recent past events</h2></div><a href="{{ route('events.index', ['period' => 'past']) }}">View all past events <b aria-hidden="true">→</b></a></div>
                <div class="ed-past-list">
                    @foreach($pastEvents as $event)
                        <a href="{{ route('events.show', $event) }}">
                            <span>{{ $event->starts_at?->format('d M') ?? 'TBC' }}</span>
                            <b>{{ $event->title }}</b>
                            <small>{{ $event->school?->name ?? 'FindURSCH community' }}</small>
                            <i aria-hidden="true">→</i>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="ed-bottom-cta">
            <div><small>FOR SCHOOL COMMUNITIES</small><h2>Have an event to share?</h2><p>Contact FindURSCH about promoting school events and open days to families across Uganda.</p></div>
            <a class="ed-button" href="{{ route('contact') }}">Contact FindURSCH <b aria-hidden="true">→</b></a>
        </section>
    </main>
</div>