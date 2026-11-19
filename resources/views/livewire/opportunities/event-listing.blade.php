<div class="resource-page">
    <a class="blogs-rtab" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>
    <div class="blogs-ad-slim"><span>Promote your education event</span><small>Reach parents, students and school communities across Uganda · <a href="{{ route('contact') }}">Contact our team</a></small></div>
    <section class="resource-hero">
        <p class="resource-eyebrow">FindURSCH community calendar</p>
        <h1>School <em>Events &amp; Open Days</em></h1>
        <p>Discover school open days, education fairs, conferences and activities shared by school communities across Uganda.</p>
    </section>
    <main class="blogs-wrap resource-main">
        <div class="resource-toolbar">
            <form class="resource-search" method="GET" action="{{ route('events.index') }}">
                <input type="search" name="q" value="{{ $search }}" placeholder="Search events, schools or venues" aria-label="Search events">
                <button class="blogs-btn blogs-btn-brand" type="submit">Search</button>
            </form>
            <nav class="resource-pills" aria-label="Event date filter">
                <a href="{{ route('events.index', array_filter(['q' => $search ?: null])) }}" @class(['is-active' => $period === 'upcoming'])>Upcoming</a>
                <a href="{{ route('events.index', array_filter(['period' => 'all', 'q' => $search ?: null])) }}" @class(['is-active' => $period === 'all'])>All events</a>
                <a href="{{ route('events.index', array_filter(['period' => 'past', 'q' => $search ?: null])) }}" @class(['is-active' => $period === 'past'])>Past events</a>
            </nav>
        </div>
        <div class="resource-section-head">
            <div><small>Community calendar</small><h2>{{ $period === 'past' ? 'Past events' : ($period === 'all' ? 'All school events' : 'Upcoming events') }}</h2></div>
            <span>{{ $events->total() }} {{ \Illuminate\Support\Str::plural('event', $events->total()) }}</span>
        </div>
        @if($events->isNotEmpty())
            <div class="resource-grid">
                @foreach($events as $event)
                    <a class="resource-card" href="{{ route('events.show', $event) }}">
                        <div class="resource-card-image">
                            @if($event->school?->getFirstMediaUrl('cover'))
                                <img src="{{ $event->school->getFirstMediaUrl('cover') }}" alt="{{ $event->school->name }}" loading="lazy">
                            @else
                                <span class="resource-card-placeholder">FindURSCH events</span>
                            @endif
                            @if($event->starts_at)<span class="resource-date-chip">{{ $event->starts_at->format('d M Y') }}</span>@endif
                        </div>
                        <div class="resource-card-body">
                            <small>{{ $event->school?->name ?? 'FindURSCH community' }} · {{ $event->venue ?: ($event->school?->district?->name ?? 'Uganda') }}</small>
                            <h3>{{ $event->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 125) }}</p>
                            <div class="resource-card-meta">
                                <span>{{ $event->starts_at?->format('D, d M Y · g:i A') ?? 'Date to be confirmed' }}</span>
                                <b>View event <span aria-hidden="true">→</span></b>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="blogs-pagination">{{ $events->links() }}</div>
        @else
            <div class="resource-empty">
                <span class="resource-empty-icon">◎</span>
                <h3>No {{ $period === 'past' ? 'past' : 'upcoming' }} events found</h3>
                <p>Events will appear here after a school publishes them. Check back for open days, fairs and school activities.</p>
                @if($search !== '')<a class="blogs-btn blogs-btn-brand" href="{{ route('events.index') }}">Clear search</a>@endif
            </div>
        @endif
        @if($period !== 'past' && $pastEvents->isNotEmpty())
            <section class="resource-past">
                <div class="resource-section-head"><div><small>Previously held</small><h2>Recent past events</h2></div><a href="{{ route('events.index', ['period' => 'past']) }}">View all past events →</a></div>
                <div class="resource-past-list">
                    @foreach($pastEvents as $event)
                        <a href="{{ route('events.show', $event) }}"><span>{{ $event->starts_at?->format('d M') }}</span><b>{{ $event->title }}</b><small>{{ $event->school?->name }}</small></a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>