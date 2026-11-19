<div class="resource-page">
    <a class="blogs-rtab" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>
    <div class="blogs-ad-slim"><span>Scholarship and bursary listings</span><small>Explore opportunities shared by school communities · <a href="{{ route('contact') }}">Contact FindURSCH</a></small></div>
    <section class="resource-hero">
        <p class="resource-eyebrow">Student opportunities</p>
        <h1>Scholarships &amp; <em>Bursaries</em> in Uganda</h1>
        <p>Discover school bursaries and student funding opportunities published by participating schools. <b>{{ $openCount }}</b> currently open or without a listed deadline.</p>
    </section>
    <main class="blogs-wrap resource-main">
        <form class="resource-search resource-scholarship-search" method="GET" action="{{ route('scholarships.index') }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search scholarships, schools or courses" aria-label="Search scholarships">
            <button class="blogs-btn blogs-btn-brand" type="submit">Search</button>
            <select name="deadlineFilter" aria-label="Deadline">
                <option value="all" @selected($deadlineFilter === 'all')>Any deadline</option>
                <option value="open" @selected($deadlineFilter === 'open')>Open now</option>
                <option value="closed" @selected($deadlineFilter === 'closed')>Closed</option>
            </select>
        </form>
        <div class="resource-safety-note" role="note"><span>!</span><div><small>Scholarship safety notice</small><b>Never pay anyone to receive or secure a scholarship.</b><p>Check eligibility, application steps and deadlines directly with the publishing school. Be cautious of anyone who asks for processing, registration or release fees.</p></div></div>
        <div class="resource-section-head"><div><small>Funding opportunities</small><h2>{{ $deadlineFilter === 'closed' ? 'Closed opportunities' : ($deadlineFilter === 'open' ? 'Open opportunities' : 'Published scholarships & bursaries') }}</h2></div><span>{{ $scholarships->total() }} {{ \Illuminate\Support\Str::plural('opportunity', $scholarships->total()) }}</span></div>
        @if($scholarships->isNotEmpty())
            <div class="resource-grid">
                @foreach($scholarships as $item)
                    <a class="resource-card" href="{{ route('scholarships.show', $item) }}">
                        <div class="resource-card-image resource-scholarship-image">
                            @if($item->school?->getFirstMediaUrl('cover'))<img src="{{ $item->school->getFirstMediaUrl('cover') }}" alt="{{ $item->school->name }}" loading="lazy">@else<span class="resource-card-placeholder">Student opportunity</span>@endif
                            <span class="resource-date-chip">{{ $item->deadline ? ($item->deadline->isPast() ? 'Deadline passed' : 'Deadline '.$item->deadline->format('d M Y')) : 'Deadline not listed' }}</span>
                        </div>
                        <div class="resource-card-body">
                            <small>{{ $item->school?->name ?? 'School opportunity' }} · {{ $item->award ?: 'Bursary / scholarship' }}</small>
                            <h3>{{ $item->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 135) }}</p>
                            <div class="resource-card-meta"><span>{{ $item->deadline ? 'Deadline '.$item->deadline->format('d M Y') : 'Contact school for details' }}</span><b>View details →</b></div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="blogs-pagination">{{ $scholarships->links() }}</div>
        @else
            <div class="resource-empty">
                <span class="resource-empty-icon">✦</span>
                <h3>No published scholarships or bursaries found</h3>
                <p>New opportunities will appear here when participating schools publish them. Always confirm eligibility and deadlines with the school.</p>
                @if($search !== '' || $deadlineFilter !== 'all')<a class="blogs-btn blogs-btn-brand" href="{{ route('scholarships.index') }}">Clear filters</a>@endif
            </div>
        @endif
    </main>
</div>