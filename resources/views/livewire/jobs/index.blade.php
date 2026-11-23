<div class="findursch-jobs">
    <a class="rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>
    <x-site-page-head :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Jobs']]" eyebrow="FindURSCH career board">
        <x-slot:title>All <em>Education Jobs</em> in Uganda</x-slot:title>
        <x-slot:description>Browse teaching, school administration, university, NGO and education support vacancies in one place. Search current opportunities and check application deadlines directly with the employer.</x-slot:description>
        <div class="jobs-hero-count"><span class="jobs-dot"></span>{{ number_format($totalOpenJobs) }} {{ \Illuminate\Support\Str::plural('open opportunity', $totalOpenJobs) }}</div>
    </x-site-page-head>

    <main class="wrap jobs-main">
        <div class="pills" role="tablist" aria-label="Job categories">
            <button type="button" class="{{ $category === 'all' ? 'on' : '' }}" wire:click="setCategory('all')" aria-selected="{{ $category === 'all' ? 'true' : 'false' }}">All jobs ({{ number_format($totalOpenJobs) }})</button>
            @foreach($categories as $item)
                <button type="button" class="{{ $category === $item['key'] ? 'on' : '' }}" wire:click="setCategory('{{ $item['key'] }}')" aria-selected="{{ $category === $item['key'] ? 'true' : 'false' }}">{{ $item['label'] }} <span>{{ number_format($item['count']) }}</span></button>
            @endforeach
        </div>

        <form class="bar jobs-search" wire:submit="applyFilters">
            <label class="jobs-query"><span aria-hidden="true">⌕</span><input type="search" wire:model.live.debounce.350ms="search" placeholder="Search job title, school or employer" aria-label="Search jobs"></label>
            <label class="jobs-filter"><span class="sr-only">Location</span>
                <select wire:model.live="location" aria-label="Filter by location">
                    <option value="">All locations</option>
                    @foreach($locations as $place)<option value="{{ $place }}">{{ $place }}</option>@endforeach
                </select>
            </label>
            <label class="jobs-filter"><span class="sr-only">Job type</span>
                <select wire:model.live="employmentType" aria-label="Filter by job type">
                    <option value="">All job types</option>
                    @foreach($employmentTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </select>
            </label>
            <button class="btn btn-brand" type="submit" wire:loading.attr="disabled" wire:target="applyFilters">
                <span wire:loading.remove wire:target="applyFilters">Search</span>
                <span wire:loading wire:target="applyFilters">Searching…</span>
            </button>
            <a class="btn btn-gold" href="{{ route('contact') }}">+ Post a job</a>
        </form>

        <div class="job-safety" role="note">
            <div class="job-safety-icon">!</div>
            <div><small>Job safety notice</small><b>Never pay anyone to get a job.</b>
                <p>FindURSCH does not charge job seekers to apply. Be careful with anyone who asks for application, interview, placement or processing fees. Confirm opportunities directly with the employer before sharing money or sensitive information.</p>
            </div>
        </div>

        <div class="jobs-countbar">
            <div><small>CAREER OPPORTUNITIES</small><strong>Showing {{ number_format($filteredCount) }} {{ \Illuminate\Support\Str::plural('job', $filteredCount) }}</strong></div>
            <div class="jobs-count-actions">
                @if($search !== '' || $category !== 'all' || $location !== '' || $employmentType !== '')
                    <button type="button" class="jobs-clear" wire:click="clearFilters">Clear filters</button>
                @endif
                <label class="jobs-sort"><span>Sort:</span>
                    <select wire:model.live="sort" aria-label="Sort jobs">
                        <option value="newest">Newest first</option><option value="closing">Closing soonest</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="cols jobs-layout">
            <section class="jobs-results" aria-label="Education job listings">
                @if($jobs->isNotEmpty())
                    <div class="jobs">
                        @foreach($jobs as $job)
                            <article class="card job {{ $job->is_featured ? 'is-featured' : '' }}" wire:key="job-{{ $job->id }}">
                                <div class="b">
                                    <div class="job-topline"><span class="cat">{{ $job->category_label }}</span>@if($job->is_featured)<span class="job-featured">Featured</span>@endif</div>
                                    <h2><a href="{{ route('jobs.show', $job->slug) }}">{{ $job->title }}</a></h2>
                                    <p class="emp">{{ $job->employer }}</p>
                                    @if($job->excerpt)<p class="ex">{{ \Illuminate\Support\Str::limit($job->excerpt, 190) }}</p>@endif
                                    <div class="tg"><span>{{ $job->location }}</span><span>{{ $job->employment_type_label }}</span>@if($job->salary)<span>{{ $job->salary }}</span>@endif</div>
                                </div>
                                <div class="f">
                                    <span class="cl {{ $job->deadline ? 'op' : '' }}">
                                        @if($job->deadline)Apply by <b>{{ $job->deadline->format('j M Y') }}</b>@else<b>Open until filled</b>@endif
                                    </span>
                                    <a class="btn btn-brand sm" href="{{ route('jobs.show', $job->slug) }}">View &amp; apply</a>
                                </div>
                            </article>
                            @if($loop->iteration === 6)<div class="span"><x-site-ad-banner placement="inlist" /></div>@endif
                        @endforeach
                    </div>
                    <div class="pg jobs-pagination">{{ $jobs->onEachSide(1)->links() }}</div>
                @else
                    <div class="jobs-empty">
                        <span class="jobs-empty-mark">⌕</span><p class="jobs-eyebrow">NO MATCHING VACANCIES</p>
                        <h2>No open jobs found</h2>
                        <p>There are no published vacancies matching these filters right now. Try another location or job category, or clear your search.</p>
                        <button type="button" class="btn btn-brand" wire:click="clearFilters">Clear search filters</button>
                    </div>
                @endif
            </section>

            <aside class="jobs-sidebar" aria-label="Job resources">
                <section class="card box"><h5>Browse by category</h5><div class="cats">
                    <button type="button" class="{{ $category === 'all' ? 'on' : '' }}" wire:click="setCategory('all')">All jobs <em>{{ number_format($totalOpenJobs) }}</em></button>
                    @foreach($categories as $item)
                        <button type="button" class="{{ $category === $item['key'] ? 'on' : '' }}" wire:click="setCategory('{{ $item['key'] }}')">{{ $item['label'] }} <em>{{ number_format($item['count']) }}</em></button>
                    @endforeach
                </div></section>

                <div class="note" role="note"><small>Job safety notice</small><b>Never pay anyone to get a job.</b><p>Legitimate employers should explain the recruitment process clearly. Verify the employer and job details before applying.</p></div>

                <section class="card box soon"><h5>Closing soon</h5>
                    @if($closingSoon->isNotEmpty())
                        <ul>@foreach($closingSoon as $closingJob)
                            <li><a href="{{ route('jobs.show', $closingJob->slug) }}">{{ $closingJob->title }}<span>{{ $closingJob->employer }} · closes {{ $closingJob->deadline->format('j M') }}</span></a></li>
                        @endforeach</ul>
                    @else<p>No closing dates are currently listed.</p>@endif
                </section>

                <x-site-ad-banner placement="side" />

                <section class="band"><h3>Get job alerts</h3><p>Contact FindURSCH about updates when new education vacancies are published.</p><a class="btn btn-gold btn-block" href="{{ route('contact') }}">Ask about job alerts</a></section>
                <section class="jobs-post-card"><small>FOR EMPLOYERS</small><h3>Have a vacancy to share?</h3><p>Schools and education organisations can contact our team to discuss publishing a job opportunity.</p><a class="btn btn-brand btn-block" href="{{ route('contact') }}">Post a job</a></section>
            </aside>
        </div>
    </main>
</div>
