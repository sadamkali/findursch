<div class="resource-page">
    <a class="blogs-rtab" href="{{ rtrim(config('services.dashboard.url', 'https://findursch-dashboard.vercel.app'), '/') }}/register?type=school_owner">REGISTER YOUR SCHOOL</a>
    <x-site-ad-banner placement="top" />
    <main class="blogs-wrap resource-detail-wrap">
        <nav class="resource-crumbs"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('news.index') }}">News</a><span>›</span><a href="{{ route('schools.show', $post->school) }}">{{ $post->school?->name }}</a><span>›</span><b>{{ $post->title }}</b></nav>
        <div class="resource-detail-grid article-detail-grid">
            <article class="resource-detail-content article-detail-content">
                <div class="resource-tags"><span>School news</span><span>{{ $post->published_at?->format('d M Y') }}</span></div>
                <h1>{{ $post->title }}</h1>
                <p class="resource-detail-lead">{{ $post->school?->name }} · {{ $post->school?->district?->name ?? 'Uganda' }}</p>
                @if($post->image_url || $post->school?->getFirstMediaUrl('cover'))<img class="resource-detail-cover" src="{{ $post->image_url ?: $post->school?->getFirstMediaUrl('cover') }}" alt="{{ $post->image_alt ?: (($post->school?->name ?? 'School'). ' update') }}">@endif
                <div class="article-byline"><span class="article-avatar">{{ strtoupper(mb_substr($post->school?->name ?? 'S', 0, 1)) }}</span><span><b>{{ $post->school?->name }}</b><small>School announcement · {{ $post->published_at?->format('d M Y') }}</small></span></div>
                <div class="resource-share"><span>SHARE</span><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener">Facebook</a><a href="https://wa.me/?text={{ urlencode($post->title.' '.request()->fullUrl()) }}" target="_blank" rel="noopener">WhatsApp</a><button type="button" x-data @click="navigator.clipboard.writeText(window.location.href)">Copy link</button></div>
                <section class="resource-prose"><h2>School update</h2><div>{!! nl2br(e($post->description)) !!}</div></section>
                <section class="resource-organiser"><h2>About the school</h2><p>{{ $post->school?->name }}</p><div class="resource-contact-lines">@if($post->school?->phone)<a href="tel:{{ $post->school->phone }}">{{ $post->school->phone }}</a>@endif @if($post->school?->email)<a href="mailto:{{ $post->school->email }}">{{ $post->school->email }}</a>@endif<a href="{{ route('schools.show', $post->school) }}">View school profile</a></div></section>
            </article>
            <aside class="resource-detail-sidebar">
                <div class="resource-sidebar-promo"><small>School profile</small><h3>{{ $post->school?->name }}</h3><p>View this school's profile, contact information, facilities and admissions details.</p><a class="blogs-btn blogs-btn-gold" href="{{ route('schools.show', $post->school) }}">View school</a></div>
                <x-site-ad-banner placement="side" />
                <div class="article-sidebar-links"><a href="{{ route('events.index') }}">School events →</a><a href="{{ route('scholarships.index') }}">Scholarships and bursaries →</a><a href="{{ route('news.index') }}">Latest education news →</a></div>
            </aside>
        </div>
        @if($related->isNotEmpty())
            <section class="resource-related article-related"><div class="resource-section-head"><div><small>From this school</small><h2>More school updates</h2></div></div><div class="resource-related-grid">@foreach($related as $item)<a href="{{ route('school-news.show', $item) }}"><small>{{ $item->published_at?->format('d M Y') }}</small><b>{{ $item->title }}</b><span>{{ $post->school?->name }}</span></a>@endforeach</div></section>
        @endif
    </main>
</div>