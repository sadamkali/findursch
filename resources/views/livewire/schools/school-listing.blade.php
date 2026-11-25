<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Find a school in Uganda — FindURSCH</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<style>
.im img{display:block;width:100%;height:100%;object-fit:cover}
.im img.logo-mark{width:76px;height:76px;object-fit:contain;margin:auto}
.im.n0{min-height:150px}
.results-empty{grid-column:1/-1;background:#fff;border:1px dashed #bcd9cf;border-radius:var(--r);padding:48px 24px;text-align:center}
.results-empty h3{font:700 22px var(--serif);margin-bottom:6px}
.results-empty p{color:var(--mute);margin-bottom:16px}
.sort-form{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.sort-form select{height:38px;border:1px solid var(--line);border-radius:8px;background:#fff;padding:0 12px;color:var(--ink);font:600 13px var(--sans)}
.result-meta{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:16px 0 12px;color:var(--mute);font-size:13px}
.result-meta b{color:var(--ink)}
.view-links{display:flex;gap:6px}
.view-links a{width:38px;height:38px;display:grid;place-items:center;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--brand)}
.view-links a.on{background:var(--brand);color:#fff;border-color:var(--brand)}
.school-pagination{display:flex;justify-content:center;gap:7px;flex-wrap:wrap;margin-top:24px}
.school-pagination a,.school-pagination span{min-width:36px;height:36px;padding:0 10px;display:grid;place-items:center;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--ink);font:600 13px var(--sans)}
.school-pagination .active{background:var(--brand);border-color:var(--brand);color:#fff}
.school-pagination .disabled{opacity:.4}
.school-card-save{display:inline-flex}
.school-card-save button,.school-card-save a{width:28px;height:26px;border-radius:6px;background:#eef1ef;display:grid;place-items:center;border:0;color:var(--brand);cursor:pointer}
.school-card-save button.on{background:#e6f2ed}
.school-card-save .i{width:16px;height:16px}
.filter-select{width:100%;height:40px;border:1px solid var(--line);border-radius:7px;background:#fff;padding:0 10px;color:var(--ink);font:13px var(--sans)}
@media(max-width:1000px){.sort-form{justify-content:space-between}.sort-form label{flex:1}.sort-form select{width:100%}}
</style>
</head>
<body>
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs>
<symbol id="i-bm" viewBox="0 0 24 24"><path d="M6 3h12v18l-6-4.5L6 21z"/></symbol>
<symbol id="i-chat" viewBox="0 0 24 24"><path d="M4 4h16v12H9l-5 4zM8 9h8M8 12h5"/></symbol>
<symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></symbol>
<symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 10-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></symbol>
<symbol id="i-check" viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></symbol>
<symbol id="i-grid" viewBox="0 0 24 24"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></symbol>
<symbol id="i-list" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
<symbol id="i-chev" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></symbol>
<symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></symbol>
<symbol id="i-cap" viewBox="0 0 24 24"><path d="M12 3 1 9l11 6 9-4.9V17h2V9L12 3zM5 13.2v4L12 21l7-3.8v-4L12 17l-7-3.8z" fill="#b9c8c1" stroke="none"/></symbol>
</defs></svg>

<div class="topbar"><div class="wrap">
  <a href="{{ route('scholarships.index') }}">Scholarships</a>
  <a href="{{ route('events.index') }}">Events</a>
  <a href="{{ route('articles.index') }}">Articles</a>
  <a href="{{ route('contact') }}">Contact</a>
  @guest<a href="{{ route('login') }}">Log in</a><a href="{{ route('register') }}">Sign up</a>@else<a href="{{ route('user.home') }}">My account</a>@endguest
</div></div>

<header><div class="wrap">
  <a class="logo" href="{{ route('home') }}">Find<b>UR</b>SCH</a>
  <nav aria-label="Main navigation">
    <a href="{{ route('home') }}">Home</a>
    <a class="on" href="{{ route('schools.index') }}">Schools</a>
    <a href="{{ route('compare') }}">Compare</a>
    <a href="{{ route('scholarships.index') }}">Scholarships</a>
    <a href="{{ route('news.index') }}">News</a>
    <a href="{{ route('jobs.index') }}">Jobs</a>
    <a href="{{ route('events.index') }}">Events</a>
  </nav>
  <div class="hic">
    <a class="hb" href="{{ auth()->check() ? route('user.saved-schools') : route('login') }}" aria-label="Saved schools"><svg class="i"><use href="#i-bm"/></svg>@if(auth()->check() && $savedSchoolCount > 0)<em>{{ $savedSchoolCount }}</em>@endif</a>
    <a class="hb" href="{{ auth()->check() ? route('user.home') : route('login') }}" aria-label="Account"><svg class="i"><use href="#i-user"/></svg></a>
  </div>
  <a class="btn btn-ghost" href="{{ route('register') }}">List your school</a>
  <button class="burger" id="burger" type="button" aria-label="Menu" aria-expanded="false">☰</button>
</div></header>

<a class="rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

<section class="sband"><div class="wrap">
  <form class="sform" method="GET" action="{{ route('schools.index') }}" role="search">
    @foreach(request()->except(['q', 'page']) as $key => $value)
      @if(is_array($value))
        @foreach($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
      @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
    @endforeach
    <svg class="i ic"><use href="#i-search"/></svg>
    <input class="q" type="search" name="q" value="{{ $search }}" placeholder="Search school name, town or district" aria-label="Search schools">
    <button class="go" type="submit">Search</button>
  </form>
</div></section>

<main class="wrap">
  <div class="crumb"><a href="{{ route('home') }}">Home</a><span>{{ number_format($schools->total()) }} verified schools in Uganda</span></div>
  <div class="cols">
    <aside id="filters" aria-label="Filters">
      <div class="sh"><span>Filters</span><button type="button" id="sx" aria-label="Close filters">✕</button></div>
      <form id="filterForm" method="GET" action="{{ route('schools.index') }}">
        <input type="hidden" name="q" value="{{ $search }}">
        <input type="hidden" name="region" value="{{ $regionId }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="view" value="{{ $viewMode }}">
        <input type="hidden" name="type" value="{{ $typeId }}">

        <div class="card box"><h5>Categories</h5><div class="fb"><b class="pl">Schools</b><ul class="cl short" id="cl">
          <li class="{{ !$typeId ? 'on' : '' }}"><a href="{{ route('schools.index', request()->except(['type','page'])) }}">All schools <small>{{ number_format($allSchoolCount) }}</small></a></li>
          @foreach($schoolTypes as $schoolType)
            <li class="{{ (string)$typeId === (string)$schoolType->id ? 'on' : '' }}"><a href="{{ route('schools.index', array_merge(request()->except(['type','page']), ['type' => $schoolType->id])) }}"><i style="--c:{{ ['#2f6fd6','#2f9e5a','#e08a1e','#8a5bd6','#d6479a','#6b7f78','#c9a46a'][$loop->index % 7] }}"></i>{{ $schoolType->name }}<small>{{ number_format($schoolType->public_listing_count) }}</small></a></li>
          @endforeach
        </ul><button class="more" id="more" type="button">Show all categories</button></div></div>

        <section class="card fs" id="fs-loc"><button class="lrow" id="lbtn" type="button" aria-expanded="true"><span>Location<small id="ltx">{{ count($selectedDistrictIds) ? count($selectedDistrictIds).' selected' : 'All Uganda' }}</small></span><svg class="i"><use href="#i-chev"/></svg></button><div class="fb" id="dl">
          @foreach($districts as $districtOption)
            <label class="chk"><input type="checkbox" name="district[]" value="{{ $districtOption->id }}" @checked(in_array((int)$districtOption->id, $selectedDistrictIds, true))>{{ $districtOption->name }} <small>• {{ number_format($districtOption->public_listing_count) }}</small></label>
          @endforeach
        </div></section>

        <section class="card fs" id="fs-fee"><button class="fh" type="button">Fee per term, UGX<span>—</span></button><div class="fb">
          <div class="mm2"><input id="lo" name="fee_min" type="number" min="0" step="10000" value="{{ $feeMin !== null ? (int)$feeMin : '' }}" placeholder="min" aria-label="Minimum fee"><span>–</span><input id="hi" name="fee_max" type="number" min="0" step="10000" value="{{ $feeMax !== null ? (int)$feeMax : '' }}" placeholder="max" aria-label="Maximum fee"></div>
          <div class="rs"><button id="fclr" type="button">CLEAR</button><button type="submit">APPLY</button></div>
          <small>Only active tuition fees published in UGX per term are included.</small>
        </div></section>

        <section class="card fs" id="fs-cu"><button class="fh" type="button">Curriculum<span>—</span></button><div class="fb" id="cu">
          @foreach($curricula as $curriculumOption)
            <label class="chk"><input type="checkbox" name="curriculum[]" value="{{ $curriculumOption->id }}" @checked(in_array((int)$curriculumOption->id, $selectedCurriculumIds, true))>{{ $curriculumOption->name }} <small>• {{ number_format($curriculumOption->public_listing_count) }}</small></label>
          @endforeach
        </div></section>

        <section class="card fs"><button class="fh" type="button">Boarding type<span>—</span></button><div class="fb" id="md">
          @foreach($boardingOptions as $boardingOption)
            <label class="chk"><input type="checkbox" name="boarding[]" value="{{ $boardingOption }}" @checked(in_array($boardingOption, $selectedBoardingTypes, true))>{{ ucwords(str_replace(['_', '-'], ' ', $boardingOption)) }}</label>
          @endforeach
        </div></section>

        <section class="card fs"><button class="fh" type="button">Admissions<span>—</span></button><div class="fb"><label class="chk"><input type="checkbox" name="admissions_open" value="1" @checked($admissionsOpen)>Admissions currently open</label></div></section>

        <div class="ad side">Advertise with FindURSCH<small>Reach parents and students</small></div>
        <div class="promo2"><small>Advertise</small><h3>Put your school in front of parents and students</h3><p>Promote your institution to families searching across Uganda.</p>
          <ul><li>School profile promotion</li><li>Featured school placements</li><li>Reach parents across Uganda</li></ul><a class="btn btn-gold" href="{{ route('contact') }}">Advertise with us</a></div>
        <div class="sapply"><a class="btn line" href="{{ route('schools.index') }}">Clear all</a><button class="btn btn-brand" type="submit">Show schools</button></div>
      </form>
    </aside>

    <section id="top">
      <div class="qchips"><span class="qc on" aria-label="Publicly visible schools"><svg class="i"><use href="#i-check"/></svg>Verified public listings</span><a class="qc gd" href="{{ route('contact') }}">Ask about school fees</a></div>
      <h1 class="pt">Schools in Uganda</h1>
      <div class="mrow"><button type="button" data-o="">All filters</button><button type="button" data-o="fs-loc">Location</button><button type="button" data-o="fs-fee">Fee, UGX</button><button type="button" data-o="fs-cu">Curriculum</button></div>
      <div class="result-meta"><span>Showing <b>{{ number_format($schools->firstItem() ?? 0) }}–{{ number_format($schools->lastItem() ?? 0) }}</b> of <b>{{ number_format($schools->total()) }}</b> schools</span>
        <div class="view-links"><a class="{{ $viewMode === 'grid' ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['view'=>'grid','page'=>null]) }}" aria-label="Grid view"><svg class="i"><use href="#i-grid"/></svg></a><a class="{{ $viewMode === 'list' ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['view'=>'list','page'=>null]) }}" aria-label="List view"><svg class="i"><use href="#i-list"/></svg></a></div>
      </div>
      <form class="sort-form" id="sortForm" method="GET" action="{{ route('schools.index') }}">
        @foreach(request()->except(['region','sort','page']) as $key => $value)
          @if(is_array($value))@foreach($value as $item)<input type="hidden" name="{{ $key }}[]" value="{{ $item }}">@endforeach
          @else<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
        @endforeach
        <label><select name="region" aria-label="Filter by region" onchange="this.form.submit()"><option value="">All regions</option>@foreach($regions as $regionOption)<option value="{{ $regionOption->id }}" @selected((string)$regionId === (string)$regionOption->id)>{{ $regionOption->name }}</option>@endforeach</select></label>
        <label><select name="sort" aria-label="Sort schools" onchange="this.form.submit()"><option value="newest" @selected($sort==='newest')>Newest first</option><option value="featured" @selected($sort==='featured')>Featured first</option><option value="name" @selected($sort==='name')>Name A–Z</option><option value="fee_low" @selected($sort==='fee_low')>Fees: low to high</option><option value="fee_high" @selected($sort==='fee_high')>Fees: high to low</option><option value="rating" @selected($sort==='rating')>Highest rated</option></select></label>
      </form>

      <div class="act">@if($search !== '')<span class="qc">Search: “{{ $search }}”</span>@endif @if($typeId)<span class="qc">{{ optional($schoolTypes->firstWhere('id', $typeId))->name }}</span>@endif @if(count($selectedDistrictIds))<span class="qc">{{ count($selectedDistrictIds) }} district filter(s)</span>@endif @if($feeMin !== null || $feeMax !== null)<span class="qc">Tuition fee range applied</span>@endif @if($admissionsOpen)<span class="qc">Admissions open</span>@endif @if(request()->hasAny(['q','type','district','region','fee_min','fee_max','curriculum','boarding','admissions_open']))<a class="ca" href="{{ route('schools.index') }}">Clear all filters</a>@endif</div>

      <div class="res {{ $viewMode }}" id="res" aria-live="polite">
        @forelse($schools as $school)
          @php
            $coverUrl = $school->getFirstMediaUrl('cover');
            $logoUrl = $school->getFirstMediaUrl('logo');
            $locationText = collect([$school->town ?: $school->parish?->name, $school->district?->name])->filter()->unique()->implode(', ');
            $isFeatured = (bool) $school->is_featured && (!$school->featured_until || $school->featured_until->isFuture());
          @endphp
          <article class="lc{{ $isFeatured ? ' prm' : '' }}">
            <a class="im{{ $coverUrl ? '' : ' n0' }}" href="{{ route('schools.show', $school) }}" aria-label="View {{ $school->name }}">
              @if($coverUrl)<img src="{{ $coverUrl }}" alt="{{ $school->name }}" loading="lazy">@elseif($logoUrl)<img class="logo-mark" src="{{ $logoUrl }}" alt="{{ $school->name }} logo" loading="lazy">@else<svg class="i" style="width:48px;height:48px;stroke:none"><use href="#i-cap"/></svg>@endif
              @if($isFeatured)<div class="tags"><span>Featured</span></div>@endif
            </a>
            <div class="bd">
              <div class="tp"><div class="fe">
                @if($school->lowest_term_tuition !== null)<b>UGX {{ number_format((float)$school->lowest_term_tuition) }}</b> per term @elseif($school->fees_from !== null && (float)$school->fees_from > 0)<b>From UGX {{ number_format((float)$school->fees_from) }}</b>@if($school->fees_to !== null && (float)$school->fees_to > (float)$school->fees_from)<span> – UGX {{ number_format((float)$school->fees_to) }}</span>@endif @else<b>Fees not published</b>@endif
              </div><h3 class="nm"><a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a></h3></div>
              <p class="ds">{{ $school->tagline ?: \Illuminate\Support\Str::limit(strip_tags((string)$school->description), 160, '…') ?: ($school->type?->name.' in '.$locationText) }}</p>
              <div class="cp"><span>{{ $school->type?->name ?? 'School' }}</span>@foreach($school->curricula as $schoolCurriculum)<span>{{ $schoolCurriculum->abbreviation ?: $schoolCurriculum->name }}</span>@endforeach @if($school->boarding_type)<span>{{ ucwords(str_replace(['_', '-'], ' ', $school->boarding_type)) }}</span>@endif @if($school->admissions_open)<span class="nw">Admissions open</span>@endif</div>
              <div class="ft"><span class="loc"><svg class="i"><use href="#i-pin"/></svg>{{ $locationText ?: 'Uganda' }}</span>
                @if(auth()->check())
                  <form class="school-card-save" method="POST" action="{{ route('school.save.toggle', $school) }}">@csrf<button class="sv {{ in_array((int)$school->id, $savedSchoolIds, true) ? 'on' : '' }}" type="submit" aria-label="Save {{ $school->name }}" aria-pressed="{{ in_array((int)$school->id, $savedSchoolIds, true) ? 'true' : 'false' }}"><svg class="i"><use href="#i-bm"/></svg></button></form>
                @else
                  <span class="school-card-save"><a href="{{ route('login') }}" aria-label="Sign in to save {{ $school->name }}"><svg class="i"><use href="#i-bm"/></svg></a></span>
                @endif
                @if((float)$school->rating_average > 0)<span class="bg" title="Average rating">★ {{ number_format((float)$school->rating_average, 1) }}</span>@endif
              </div>
            </div>
          </article>
        @empty
          <div class="results-empty"><h3>No schools match these filters</h3><p>Try another school name, district, curriculum or fee range.</p><a class="btn btn-brand" href="{{ route('schools.index') }}">Clear all filters</a></div>
        @endforelse
      </div>

      @if($schools->hasPages())
        <nav class="school-pagination" aria-label="School result pages">
          @if($schools->onFirstPage())<span class="disabled" aria-disabled="true">‹</span>@else<a href="{{ $schools->previousPageUrl() }}" rel="prev" aria-label="Previous page">‹</a>@endif
          @foreach($schools->getUrlRange(max(1,$schools->currentPage()-2), min($schools->lastPage(),$schools->currentPage()+2)) as $page => $url)
            @if($page === $schools->currentPage())<span class="active" aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
          @endforeach
          @if($schools->hasMorePages())<a href="{{ $schools->nextPageUrl() }}" rel="next" aria-label="Next page">›</a>@else<span class="disabled" aria-disabled="true">›</span>@endif
        </nav>
      @endif
    </section>
  </div>
</main>

<footer>
  <div class="wrap">
    <div class="fgrid">
      <div><a class="logo" href="{{ route('home') }}">Find<b>UR</b>SCH</a><p>Uganda's school discovery, comparison and admissions platform, with verified school profiles, news, events, jobs and scholarships.</p><p>Kampala, Uganda<br><a href="{{ route('contact') }}">Contact FindURSCH</a></p></div>
      <div><h6>Explore schools</h6><ul><li><a href="{{ route('schools.index') }}">All schools</a></li><li><a href="{{ route('schools.index', ['sort'=>'newest']) }}">Recently added</a></li><li><a href="{{ route('compare') }}">Compare schools</a></li><li><a href="{{ route('contact') }}">List your school</a></li></ul></div>
      <div><h6>Resources</h6><ul><li><a href="{{ route('news.index') }}">Latest news</a></li><li><a href="{{ route('scholarships.index') }}">Scholarships</a></li><li><a href="{{ route('jobs.index') }}">School jobs</a></li><li><a href="{{ route('events.index') }}">Events calendar</a></li><li><a href="{{ route('articles.index') }}">Articles</a></li></ul></div>
      <div><h6>Support</h6><ul><li><a href="{{ route('register') }}">List your school</a></li><li><a href="{{ route('about') }}">About</a></li><li><a href="{{ route('contact') }}">Contact</a></li><li><a href="{{ route('legal.terms') }}">Terms and conditions</a></li><li><a href="{{ route('legal.privacy') }}">Privacy policy</a></li></ul></div>
    </div>
    <div class="fbar"><span>© {{ date('Y') }} FindURSCH. All rights reserved.</span><div class="box2"><span>Want to reach parents and students every month?</span><a class="btn btn-ghost" href="{{ route('contact') }}">Advertise with us</a></div></div>
  </div>
</footer>

<div class="sov" id="sov"></div>
<script>
(function(){
  const burger=document.getElementById('burger');
  const side=document.getElementById('filters');
  const overlay=document.getElementById('sov');
  const close=document.getElementById('sx');
  const more=document.getElementById('more');
  const categories=document.getElementById('cl');
  const locationToggle=document.getElementById('lbtn');
  const locationList=document.getElementById('dl');
  if(burger){
    const nav=document.querySelector('header nav');
    const extras=document.querySelectorAll('.topbar a');
    const drawer=document.createElement('div'); drawer.className='drawer'; drawer.setAttribute('role','dialog'); drawer.setAttribute('aria-modal','true'); drawer.setAttribute('aria-label','Menu');
    const shade=document.createElement('div'); shade.className='ov'; document.body.appendChild(shade);
    const links=nav?Array.from(nav.querySelectorAll('a')).map(a=>'<a href="'+a.href+'">'+a.textContent+'</a>').join(''):'';
    const other=Array.from(extras).map(a=>'<a href="'+a.href+'">'+a.textContent+'</a>').join('');
    drawer.innerHTML='<div class="dh"><a class="logo" href="{{ route('home') }}">Find<b>UR</b>SCH</a><button class="dx" type="button" aria-label="Close menu">✕</button></div><div class="dl">'+links+'<hr>'+other+'<a href="{{ route('register') }}">List your school</a></div><div class="df"><a href="{{ route('login') }}">Sign in</a><a class="b2" href="{{ route('register') }}">Register</a></div>';
    document.body.appendChild(drawer);
    const setOpen=open=>{drawer.classList.toggle('open',open);shade.classList.toggle('open',open);document.body.classList.toggle('nolock',open);burger.setAttribute('aria-expanded',String(open));(open?drawer.querySelector('.dx'):burger).focus()};
    burger.addEventListener('click',()=>setOpen(true)); shade.addEventListener('click',()=>setOpen(false)); drawer.querySelector('.dx').addEventListener('click',()=>setOpen(false)); drawer.addEventListener('click',e=>{if(e.target.closest('a'))setOpen(false)}); document.addEventListener('keydown',e=>{if(e.key==='Escape')setOpen(false)});
  }
  const openFilters=id=>{if(!side||!overlay)return;side.classList.add('open');overlay.classList.add('open');document.body.classList.add('nolock');if(id){const el=document.getElementById(id);if(el){el.classList.remove('shut');el.scrollIntoView({block:'start'})}}};
  const closeFilters=()=>{if(!side||!overlay)return;side.classList.remove('open');overlay.classList.remove('open');document.body.classList.remove('nolock')};
  if(close)close.addEventListener('click',closeFilters); if(overlay)overlay.addEventListener('click',closeFilters);
  if(more&&categories)more.addEventListener('click',()=>{const short=categories.classList.toggle('short');more.textContent=short?'Show all categories':'Show fewer categories'});
  if(locationToggle&&locationList)locationToggle.addEventListener('click',()=>{const hidden=locationList.hidden;locationList.hidden=!hidden;locationToggle.setAttribute('aria-expanded',String(hidden))});
  document.querySelectorAll('.mrow button').forEach(button=>button.addEventListener('click',()=>openFilters(button.dataset.o)));
  document.querySelectorAll('.fh').forEach(button=>button.addEventListener('click',()=>{const panel=button.parentElement;const hidden=panel.classList.toggle('shut');const label=button.querySelector('span');if(label)label.textContent=hidden?'+':'—'}));
  document.getElementById('fclr')?.addEventListener('click',()=>{document.getElementById('lo').value='';document.getElementById('hi').value='';document.getElementById('filterForm').requestSubmit()});
  document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(input=>input.addEventListener('change',()=>document.getElementById('filterForm').requestSubmit()));
  document.addEventListener('keydown',e=>{if(e.key==='Escape')closeFilters()});
})();
</script>
</body>
</html>