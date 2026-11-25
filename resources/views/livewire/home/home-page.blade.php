<div class="findursch-home-page">
<a class="tab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

<section class="hero" data-home-hero role="region" aria-label="FindURSCH homepage highlights" aria-roledescription="carousel">
  <div class="hero-slides" aria-hidden="true">
    @foreach($homepageSlides as $heroIndex => $heroSlide)
      <div
        class="hero-slide {{ $loop->first ? 'is-active' : '' }}"
        data-home-hero-slide
        data-slide-title="{{ $heroSlide['title'] }}"
        data-slide-description="{{ $heroSlide['description'] }}"
        data-slide-button-label="{{ $heroSlide['button_label'] }}"
        data-slide-button-url="{{ $heroSlide['button_url'] }}"
        aria-hidden="true"
      >
        @if($heroSlide['image_url'])
          <img
            src="{{ $heroSlide['image_url'] }}"
            alt=""
            @if($loop->first) fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif
          >
        @endif
      </div>
    @endforeach
  </div>

  <div class="hero-content">
    <h1 data-home-hero-title>{{ $firstHeroSlide['title'] }}</h1>
    <p data-home-hero-description @if($firstHeroSlide['description'] === '') hidden @endif>{{ $firstHeroSlide['description'] }}</p>
    <a
      class="hero-cta btn btn-gold"
      data-home-hero-cta
      href="{{ $firstHeroSlide['button_url'] ?: '#' }}"
      @if($firstHeroSlide['button_label'] === '' || $firstHeroSlide['button_url'] === '') hidden @endif
    >{{ $firstHeroSlide['button_label'] }}</a>

    <form class="search" method="GET" action="{{ route('schools.index') }}" role="search">
      <input type="search" name="search" placeholder="Search by name, location or district" aria-label="Search schools">
      <select name="type" aria-label="School type">
        <option value="">All types</option>
        @foreach($schoolTypes as $schoolType)<option value="{{ $schoolType->id }}">{{ $schoolType->name }}</option>@endforeach
      </select>
      <button class="btn btn-red" type="submit">Search</button>
    </form>
  </div>

  @if(count($homepageSlides) > 1)
    <button class="hero-arrow hero-arrow--prev" type="button" data-home-hero-prev aria-label="Previous homepage slide">‹</button>
    <button class="hero-arrow hero-arrow--next" type="button" data-home-hero-next aria-label="Next homepage slide">›</button>
  @endif

  @if(count($homepageSlides) > 1)
    <div class="dots hero-dots" role="group" aria-label="Choose homepage slide">
      @foreach($homepageSlides as $heroIndex => $heroSlide)
        <button
          type="button"
          data-home-hero-dot="{{ $heroIndex }}"
          class="{{ $loop->first ? 'on' : '' }}"
          aria-label="Show homepage slide {{ $loop->iteration }}"
          aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
        ></button>
      @endforeach
    </div>
  @endif
</section>

<div class="wrap">
  <div class="cols sec">
    <div>
      <div class="head"><h2>Featured <span>schools</span></h2><a class="more" href="{{ route('schools.index', ['sort' => 'featured']) }}">View all</a></div>
      @if($featuredSchools->isNotEmpty())
        <div class="slider">
          @if($featuredSchools->count() > 3)
            <button class="arrow l" type="button" data-home-school-prev aria-label="Previous schools">‹</button>
            <button class="arrow r" type="button" data-home-school-next aria-label="Next schools">›</button>
          @endif
          <div class="cards" data-home-school-track>
            @foreach($featuredSchools as $school)
              <x-school-tile-card
                class="school"
                :school="$school"
                :saved="in_array((int) $school->id, $savedSchoolIds, true)"
                :show-save="true"
                :show-compare="true"
                :show-fee="true"
                :show-description="true"
              />
            @endforeach
          </div>
        </div>
        @if($featuredSchools->count() > 3)
          <div class="dots school-dots" aria-label="Featured school pages">
            @foreach($featuredSchools as $school)<button type="button" data-home-school-dot="{{ $loop->index }}" class="{{ $loop->first ? 'on' : '' }}" aria-label="Go to featured school {{ $loop->iteration }}"></button>@endforeach
          </div>
        @endif
      @else
        <div class="card empty-card"><h3>School listings are being prepared</h3><p>Verified school profiles will appear here as they are published.</p><a class="more" href="{{ route('schools.index') }}">Browse all schools</a></div>
      @endif

      @if($spotlightSchool)
        <x-school-tile-card
          :school="$spotlightSchool"
          :saved="in_array((int) $spotlightSchool->id, $savedSchoolIds, true)"
          :show-save="true"
          :show-compare="true"
          :show-fee="true"
          :show-description="true"
          variant="spotlight"
        />
      @endif

      <div class="strip"><div><h4>Is your school on FindURSCH?</h4><p>Parents across Uganda search here for schools every day.</p></div><a class="btn btn-gold" href="{{ route('register') }}">List your school</a></div>
    </div>

    <aside>
      <x-site-ad-banner placement="side" />
      <div class="card box"><h5>Browse by category</h5><ul>
        @forelse($schoolTypes as $schoolType)
          <li><a href="{{ route('schools.index', ['type' => $schoolType->id]) }}"><span class="ico"></span>{{ $schoolType->name }}<em>{{ number_format($schoolType->public_listing_count) }}</em></a></li>
        @empty
          <li><a href="{{ route('schools.index') }}">Browse all schools</a></li>
        @endforelse
      </ul><a class="foot" href="{{ route('schools.index') }}">All school categories</a></div>

      <div class="card box"><h5>Upcoming events</h5><ul>
        @forelse($upcomingEvents as $event)
          <li><a href="{{ route('events.show', $event->id) }}"><span class="date">{{ $event->starts_at?->format('d') }}<small>{{ strtoupper($event->starts_at?->format('M') ?? '') }}</small></span><div><strong>{{ $event->title }}</strong><span>{{ $event->school?->name ?? $event->venue ?? 'School event' }}</span></div></a></li>
        @empty
          <li class="empty-list-item">No upcoming events have been published yet.</li>
        @endforelse
      </ul><a class="foot" href="{{ route('events.index') }}">View all events</a></div>
    </aside>
  </div>

  <section class="sec">
    <div class="head"><h2>Education <span>news headlines</span></h2><a class="more" href="{{ route('news.index') }}">All news</a></div>
    <div class="news">
      <div class="lead ph c" data-home-news-lead>
        @forelse($newsArticles->take(4) as $article)
          <a class="lead-story" data-home-news-slide href="{{ route('news.show', $article) }}" @if($article->cover_image) style="background-image:url('{{ $article->cover_image }}')" @endif @if(!$loop->first) hidden @endif>
            <div class="cap"><small>{{ $article->category?->name ?? 'Education news' }} · {{ $article->published_at?->diffForHumans() }}</small><h3>{{ $article->title }}</h3>
              @if($article->excerpt)<p>{{ \Illuminate\Support\Str::limit($article->excerpt, 145) }}</p>@endif
              <span class="lead-read-link">Read full story →</span>
            </div>
          </a>
        @empty
          <div class="cap lead-empty"><small>FindURSCH newsroom</small><h3>Education news and school updates</h3><p>Published stories from schools and education communities will appear here.</p><a class="lead-read-link" href="{{ route('news.index') }}">Browse news →</a></div>
        @endforelse
        @if($newsArticles->count() > 1)
          <button class="arrow l" type="button" data-home-news-prev aria-label="Previous news story">‹</button><button class="arrow r" type="button" data-home-news-next aria-label="Next news story">›</button>
          <div class="dots lead-dots" aria-label="News story slides">
            @foreach($newsArticles->take(4) as $article)<button type="button" data-home-news-dot="{{ $loop->index }}" class="{{ $loop->first ? 'on' : '' }}" aria-label="Show news story {{ $loop->iteration }}"></button>@endforeach
          </div>
        @endif
      </div>

      <div class="card rows" data-home-row-rotator>
        @forelse($newsArticles->skip(1)->take(5) as $article)
          <a class="row" href="{{ route('news.show', $article) }}">
            <div class="ph {{ ['b','d','c',''][$loop->index % 4] }}" @if($article->cover_image) style="background-image:url('{{ $article->cover_image }}');background-size:cover;background-position:center" @endif>@unless($article->cover_image)<span>{{ strtoupper(mb_substr($article->title, 0, 1)) }}</span>@endunless</div>
            <div><h4>{{ $article->title }}</h4><small>{{ $article->published_at?->format('j M Y') }}</small></div>
          </a>
        @empty
          <p class="empty-list-item">New education headlines will appear here when published.</p>
        @endforelse
        @if($newsArticles->count() > 4)<div class="mini"><button type="button" data-home-row-prev aria-label="Previous headlines">↑</button><button type="button" data-home-row-next aria-label="Next headlines">↓</button></div>@endif
      </div>

      @if($partnerSchoolNews)
        <a class="card prem" href="{{ route('school-news.show', $partnerSchoolNews->id) }}">
          <div class="ph d" @if($partnerSchoolNews->image_url) style="background-image:url('{{ $partnerSchoolNews->image_url }}');background-size:cover;background-position:center" @endif>@unless($partnerSchoolNews->image_url)<span>{{ strtoupper(mb_substr($partnerSchoolNews->title, 0, 2)) }}</span>@endunless</div>
          <span class="flag">Partner school news</span>
          <div class="b"><h3>{{ $partnerSchoolNews->title }}</h3><p>{{ \Illuminate\Support\Str::limit($partnerSchoolNews->description, 140) }}</p><small><span>{{ $partnerSchoolNews->published_at?->format('j F Y') }}</span><span>{{ $partnerSchoolNews->school?->name }}</span></small></div>
        </a>
      @else
        <div class="card prem"><div class="ph d"></div><div class="b"><h3>Share a story from your school</h3><p>Tell the FindURSCH newsroom about a school announcement, achievement or community update.</p><a class="more" href="{{ route('contact', ['subject' => 'School news submission']) }}">Contact the newsroom →</a></div></div>
      @endif
    </div>
  </section>

  <div class="cols sec">
    <div class="stack">
      <div class="band"><div><small>Advertise with us</small><h3>Reach parents and students every month</h3><p>Promote your school, business or service to families across Uganda.</p></div>
        <div class="acts"><a class="btn btn-gold" href="{{ route('contact', ['subject' => 'Advertising enquiry']) }}">Advertise</a><a class="btn btn-ghost" href="{{ route('contact', ['subject' => 'Advertising enquiry']) }}">Send inquiry</a></div>
      </div>

      <div><div class="head"><h2>Featured <span>school videos</span></h2><a class="more" href="{{ route('news.index', ['view' => 'videos']) }}">All videos</a></div>
        @if($videos->isNotEmpty())
          <div class="vids">
            @foreach($videos->take(2) as $video)
              <a class="card vid" href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer">
                <div class="ph {{ ['c','b','d',''][$loop->index % 4] }}" style="background-image:url('https://img.youtube.com/vi/{{ rawurlencode($video->youtube_video_id) }}/hqdefault.jpg');background-size:cover;background-position:center"><i class="play" aria-hidden="true"></i></div>
                <div class="b"><small>{{ $video->school?->name ?? 'School community' }}</small><h4>{{ $video->title }}</h4><span>Watch on YouTube ↗</span></div>
              </a>
            @endforeach
          </div>
        @else
          <div class="card empty-card"><h3>School videos will appear here</h3><p>Approved videos shared by participating schools will be listed here.</p><a class="more" href="{{ route('news.index', ['view' => 'videos']) }}">Explore school videos</a></div>
        @endif
      </div>

      <div class="band edge">
        <div><h3>Scholarships &amp; bursaries</h3><p>Browse funding opportunities for Ugandan students at every level. <strong>{{ number_format($scholarshipCount) }}</strong> current {{ \Illuminate\Support\Str::plural('opportunity', $scholarshipCount) }}.</p>
          <div class="chips"><a href="{{ route('scholarships.index', ['category' => 'government']) }}">Government</a><a href="{{ route('scholarships.index', ['category' => 'foundations']) }}">Private bursaries</a><a href="{{ route('scholarships.index', ['category' => 'university']) }}">University grants</a><a href="{{ route('scholarships.index', ['category' => 'international']) }}">International</a></div>
        </div>
        <a class="btn btn-ghost" href="{{ route('scholarships.index') }}">Browse</a>
      </div>
    </div>

    <aside>
      <div class="card box jobs"><h5>Latest jobs</h5><ul>
        @forelse($latestJobs as $job)
          <li><a href="{{ route('jobs.show', $job->slug) }}">{{ $job->title }}<span>{{ $job->employer }} · {{ $job->location }}</span></a></li>
        @empty
          <li class="empty-list-item">No open education vacancies are published yet.</li>
        @endforelse
      </ul><a class="foot" href="{{ route('jobs.index') }}">View all jobs</a></div>

      <div class="promo"><small>Advertise</small><h3>Put your brand in front of parents</h3><p>Reach families across Uganda every month.</p><a class="btn btn-gold" href="{{ route('contact', ['subject' => 'Advertising enquiry']) }}">Advertise with us</a></div>

      @if($reviews->isNotEmpty())
        <div class="card box"><h5>Stories from schools &amp; families</h5><div class="quote" data-home-reviews>
          @foreach($reviews as $review)
            <div data-home-review-slide @if(!$loop->first) hidden @endif>
              <div class="st" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', max(0, min(5, (int) $review->rating))) }}{{ str_repeat('☆', 5 - max(0, min(5, (int) $review->rating))) }}</div>
              <p>{{ \Illuminate\Support\Str::limit($review->body, 230) }}</p>
              <div class="who"><i>{{ strtoupper(mb_substr($review->user?->name ?? 'F', 0, 1)) }}</i><span><b>{{ $review->user?->name ? \Illuminate\Support\Str::before($review->user->name, ' ') : 'FindURSCH reviewer' }}</b><span>{{ ucfirst($review->reviewer_type) }} · {{ $review->school?->name }}</span></span></div>
            </div>
          @endforeach
          @if($reviews->count() > 1)<div class="dots" aria-label="Review slides">@foreach($reviews as $review)<button type="button" data-home-review-dot="{{ $loop->index }}" class="{{ $loop->first ? 'on' : '' }}" aria-label="Show review {{ $loop->iteration }}"></button>@endforeach</div>@endif
        </div></div>
      @endif
    </aside>
  </div>
</div>

@if($insightArticles->isNotEmpty())
  <section class="tinted"><div class="wrap">
    <div class="head"><h2>Educational <span>insights</span></h2><a class="more" href="{{ route('articles.index') }}">All articles</a></div>
    <div class="ins">
      @php($featuredInsight = $insightArticles->first())
      @php($insightCover = $featuredInsight->cover_image)
      <a class="card big" href="{{ route('articles.show', $featuredInsight) }}">
        <div class="ph d" @if($insightCover) style="background-image:url('{{ $insightCover }}');background-size:cover;background-position:center" @endif>@unless($insightCover)<span>{{ strtoupper(mb_substr($featuredInsight->title, 0, 2)) }}</span>@endunless</div>
        <div class="b"><h3>{{ $featuredInsight->title }}</h3><p>{{ \Illuminate\Support\Str::limit($featuredInsight->excerpt ?: strip_tags($featuredInsight->body), 145) }}</p><small><span>{{ $featuredInsight->published_at?->format('M Y') }}</span><span>{{ $featuredInsight->read_time_minutes }} min read</span></small></div>
      </a>
      <div class="card rows" data-home-row-rotator>
        @forelse($insightArticles->skip(1) as $article)
          <a class="row" href="{{ route('articles.show', $article) }}">
            <div class="ph {{ ['b','','c','d'][$loop->index % 4] }}" @if($article->cover_image) style="background-image:url('{{ $article->cover_image }}');background-size:cover;background-position:center" @endif>@unless($article->cover_image)<span>{{ strtoupper(mb_substr($article->title, 0, 1)) }}</span>@endunless</div>
            <div><h4>{{ $article->title }}</h4><small>{{ $article->published_at?->format('M Y') }}</small></div>
          </a>
        @empty
          <p class="empty-list-item">More articles will appear here as they are published.</p>
        @endforelse
        @if($insightArticles->count() > 4)<div class="mini"><button type="button" data-home-row-prev aria-label="Previous articles">↑</button><button type="button" data-home-row-next aria-label="Next articles">↓</button></div>@endif
      </div>
      @if($spotlightSchool)
        @php($spotlightImage = $spotlightSchool->getFirstMediaUrl('cover'))
        <div class="spl" @if($spotlightImage) style="background-image:linear-gradient(135deg,rgba(5,30,24,.5),rgba(5,30,24,.85)),url('{{ $spotlightImage }}');background-size:cover;background-position:center" @endif>
          <div><small>School spotlight</small><h3>{{ $spotlightSchool->name }}</h3><p>{{ \Illuminate\Support\Str::limit($spotlightSchool->tagline ?: $spotlightSchool->description ?: 'Explore this school profile and the information shared with families.', 100) }}</p><a class="btn btn-gold btn-block" href="{{ route('schools.show', $spotlightSchool) }}">Learn more about this school</a></div>
        </div>
      @else
        <div class="spl"><div><small>School spotlight</small><h3>Discover schools across Uganda</h3><p>Browse verified school profiles and compare your options.</p><a class="btn btn-gold btn-block" href="{{ route('schools.index') }}">Explore schools</a></div></div>
      @endif
    </div>
  </div></section>
@endif

<div class="banner" aria-label="Sponsored advertising banners"><x-site-ad-banner placement="foot1" /><x-site-ad-banner placement="foot2" /></div>

<section class="cta"><h2>Is your school listed?</h2><p>Join schools across Uganda reaching parents, students and educators every day.</p>
  <div class="acts"><a class="btn btn-white" href="{{ route('register') }}">Register your school</a><a class="btn btn-ghost" href="{{ route('about') }}">View benefits</a></div>
</section>

<footer class="ref-footer">
  <div class="wrap">
    <div class="fgrid">
      <div>
        <a class="logo" href="{{ route('home') }}" style="color:#fff">{{ $footerSettings['site_name'] ?? 'FindURSCH' }}</a>
        <p>{{ $footerSettings['site_description'] ?? 'Uganda’s school discovery, comparison and admissions platform.' }}</p>
        @if($footerSettings['contact_address'] ?? null)<p>{{ $footerSettings['contact_address'] }}</p>@endif
        @if($footerSettings['support_phone'] ?? null)<p><a href="tel:{{ preg_replace('/[^0-9+]/', '', $footerSettings['support_phone']) }}">{{ $footerSettings['support_phone'] }}</a></p>@endif
        @if($footerSettings['support_email'] ?? null)<p><a href="mailto:{{ $footerSettings['support_email'] }}">{{ $footerSettings['support_email'] }}</a></p>@endif
      </div>
      <div><h6>Explore schools</h6><ul><li><a href="{{ route('schools.index') }}">Find a school</a></li><li><a href="{{ route('compare') }}">Compare schools</a></li><li><a href="{{ route('schools.index', ['sort' => 'newest']) }}">Recently added</a></li><li><a href="{{ route('schools.index', ['verified_only' => 1]) }}">Verified schools</a></li></ul></div>
      <div><h6>Resources</h6><ul><li><a href="{{ route('news.index') }}">Latest news</a></li><li><a href="{{ route('articles.index') }}">Articles &amp; insights</a></li><li><a href="{{ route('scholarships.index') }}">Scholarships</a></li><li><a href="{{ route('jobs.index') }}">School jobs</a></li><li><a href="{{ route('events.index') }}">Events calendar</a></li></ul></div>
      <div><h6>Support</h6><ul><li><a href="{{ route('register') }}">List your school</a></li><li><a href="{{ route('contact', ['subject' => 'Advertising enquiry']) }}">Advertise with us</a></li><li><a href="{{ route('about') }}">About</a></li><li><a href="{{ route('contact') }}">Contact</a></li><li><a href="{{ route('legal.terms') }}">Terms and conditions</a></li><li><a href="{{ route('legal.privacy') }}">Privacy policy</a></li></ul></div>
    </div>
    <div class="fbar"><span>{{ $footerSettings['site_footer_copyright_text'] ?: ('© '.date('Y').' '.($footerSettings['site_name'] ?? 'FindURSCH').'. All rights reserved.') }}</span><div class="box2"><span>Want to reach parents and students every month?</span><a class="btn btn-ghost" href="{{ route('contact', ['subject' => 'Advertising enquiry']) }}">Advertise with us</a></div></div>
  </div>
</footer>
</div>
