<div class="resource-page">
    <a class="blogs-rtab" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>
    <x-site-ad-banner placement="top" />
    <main class="blogs-wrap resource-detail-wrap">
        <nav class="resource-crumbs"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('scholarships.index') }}">Scholarships</a><span>›</span><b>{{ $scholarship->title }}</b></nav>
        <div class="resource-detail-grid">
            <article class="resource-detail-content">
                <div class="resource-tags"><span>Scholarship / bursary</span><span>{{ $scholarship->deadline && $scholarship->deadline->isPast() ? 'Deadline passed' : 'Check availability' }}</span></div>
                <h1>{{ $scholarship->title }}</h1>
                <p class="resource-detail-lead">{{ $scholarship->award ?: 'Funding opportunity' }} · {{ $scholarship->school?->name }}</p>
                @if($scholarship->school?->getFirstMediaUrl('cover'))<img class="resource-detail-cover" src="{{ $scholarship->school->getFirstMediaUrl('cover') }}" alt="{{ $scholarship->school->name }}">@endif
                <section class="resource-prose"><h2>About this opportunity</h2><div>{!! nl2br(e($scholarship->description)) !!}</div></section>
                <section class="resource-organiser"><h2>Contact the publishing school</h2><p>{{ $scholarship->school?->name }}</p><div class="resource-contact-lines">@if($scholarship->school?->phone)<a href="tel:{{ $scholarship->school->phone }}">{{ $scholarship->school->phone }}</a>@endif @if($scholarship->school?->email)<a href="mailto:{{ $scholarship->school->email }}">{{ $scholarship->school->email }}</a>@endif</div></section>
            </article>
            <aside class="resource-detail-sidebar">
                <div class="resource-event-box">
                    <small>Application information</small>
                    <h2>{{ $scholarship->award ?: 'Scholarship / bursary' }}</h2>
                    <dl><div><dt>Deadline</dt><dd>{{ $scholarship->deadline?->format('d F Y') ?? 'Not provided' }}</dd></div><div><dt>Publishing school</dt><dd>{{ $scholarship->school?->name }}</dd></div><div><dt>Location</dt><dd>{{ $scholarship->school?->district?->name ?? 'Uganda' }}</dd></div></dl>
                    <p class="resource-sidebar-warning">Contact the school to confirm eligibility, required documents and application arrangements. FindURSCH does not collect scholarship application fees.</p>
                    <a class="blogs-btn blogs-btn-brand" href="{{ route('schools.show', $scholarship->school) }}">Contact the school</a>
                </div>
                <x-site-ad-banner placement="side" />
            </aside>
        </div>
        @if($related->isNotEmpty())
            <section class="resource-related"><div class="resource-section-head"><div><small>More opportunities</small><h2>Related scholarships</h2></div><a href="{{ route('scholarships.index') }}">All scholarships →</a></div>
                <div class="resource-related-grid">@foreach($related as $item)<a href="{{ route('scholarships.show', $item) }}"><small>{{ $item->deadline?->format('d M Y') ?? 'Deadline not listed' }}</small><b>{{ $item->title }}</b><span>{{ $item->school?->name }}</span></a>@endforeach</div>
            </section>
        @endif
        <div class="banner" aria-label="Sponsored banners">
            <x-site-ad-banner placement="foot1" />
            <x-site-ad-banner placement="foot2" />
        </div>
    </main>
</div>