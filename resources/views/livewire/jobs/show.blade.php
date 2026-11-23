<div class="findursch-jobs jobs-detail">
    <a class="rtab" href="{{ route('register') }}">REGISTER YOUR SCHOOL</a>

    <x-site-ad-banner placement="top" />

    <main class="wrap jobs-detail-main">
        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('jobs.index') }}">Jobs</a>
            <span aria-hidden="true">/</span>
            <b>{{ $job->title }}</b>
        </nav>

        <div class="jobs-detail-layout">
            <article class="jobs-detail-content">
                <div class="jobs-detail-tags">
                    <span class="cat">{{ $job->category_label }}</span>
                    <span class="jobs-detail-type">{{ $job->employment_type_label }}</span>
                    @if($job->is_featured)<span class="job-featured">Featured opportunity</span>@endif
                </div>
                <h1>{{ $job->title }}</h1>
                <p class="jobs-detail-employer">{{ $job->employer }}</p>
                @if($job->excerpt)
                    <p class="jobs-detail-excerpt">{{ $job->excerpt }}</p>
                @endif

                <dl class="jobs-detail-facts">
                    <div><dt>Location</dt><dd>{{ $job->location }}</dd></div>
                    <div><dt>Employment</dt><dd>{{ $job->employment_type_label }}</dd></div>
                    @if($job->salary)<div><dt>Salary / pay</dt><dd>{{ $job->salary }}</dd></div>@endif
                    <div><dt>Published</dt><dd>{{ $job->published_at?->format('j F Y') }}</dd></div>
                    <div><dt>Application deadline</dt><dd>{{ $job->deadline?->format('j F Y') ?? 'Open until filled' }}</dd></div>
                </dl>

                <section class="jobs-description">
                    <h2>About this opportunity</h2>
                    <div>{!! nl2br(e($job->description)) !!}</div>
                </section>

                @if($job->application_instructions)
                    <section class="jobs-description">
                        <h2>How to apply</h2>
                        <div>{!! nl2br(e($job->application_instructions)) !!}</div>
                    </section>
                @endif

                @if($job->isOpen())
                    <section class="jobs-apply-panel">
                        <div>
                            <small>APPLICATION</small>
                            <h2>Interested in this opportunity?</h2>
                            <p>Review the employer's requirements and submit your application using the details provided below.</p>
                        </div>
                        <div class="jobs-apply-actions">
                            @if($job->application_url)
                                <a class="btn btn-brand" href="{{ $job->application_url }}" target="_blank" rel="noopener noreferrer">Apply on employer website ↗</a>
                            @endif
                            @if($job->application_email)
                                <a class="btn btn-line" href="mailto:{{ $job->application_email }}?subject={{ rawurlencode('Application: '.$job->title) }}">Apply by email</a>
                            @endif
                        </div>
                    </section>
                @else
                    <div class="jobs-closed-notice">
                        <b>This vacancy is closed.</b>
                        <p>The application deadline has passed or the employer has closed this opportunity. Please browse other open education jobs.</p>
                    </div>
                @endif

                <div class="jobs-detail-back">
                    <a href="{{ route('jobs.index') }}">← Back to all education jobs</a>
                </div>
            </article>

            <aside class="jobs-detail-sidebar">
                <div class="jobs-apply-summary">
                    <small>JOB SUMMARY</small>
                    <h2>{{ $job->title }}</h2>
                    <p>{{ $job->employer }}</p>
                    <dl>
                        <div><dt>Location</dt><dd>{{ $job->location }}</dd></div>
                        <div><dt>Job type</dt><dd>{{ $job->employment_type_label }}</dd></div>
                        @if($job->salary)<div><dt>Pay</dt><dd>{{ $job->salary }}</dd></div>@endif
                        <div><dt>Deadline</dt><dd>{{ $job->deadline?->format('j M Y') ?? 'Until filled' }}</dd></div>
                    </dl>
                    @if($job->isOpen())
                        @if($job->application_url)<a class="btn btn-brand btn-block" href="{{ $job->application_url }}" target="_blank" rel="noopener noreferrer">Apply now ↗</a>
                        @elseif($job->application_email)<a class="btn btn-brand btn-block" href="mailto:{{ $job->application_email }}?subject={{ rawurlencode('Application: '.$job->title) }}">Apply by email</a>@endif
                    @else
                        <div class="jobs-closed-pill">Applications closed</div>
                    @endif
                </div>

                <div class="note" role="note">
                    <small>Job safety notice</small>
                    <b>Never pay to apply.</b>
                    <p>FindURSCH does not collect job application or placement fees. Verify all requests directly with the employer.</p>
                </div>

                <x-site-ad-banner placement="side" />
            </aside>
        </div>

        @if($relatedJobs->isNotEmpty())
            <section class="jobs-related">
                <div class="jobs-section-heading">
                    <div><small>MORE OPPORTUNITIES</small><h2>Related education jobs</h2></div>
                    <a href="{{ route('jobs.index') }}">View all jobs →</a>
                </div>
                <div class="jobs-related-grid">
                    @foreach($relatedJobs as $related)
                        <a class="card job" href="{{ route('jobs.show', $related->slug) }}">
                            <div class="b">
                                <span class="cat">{{ $related->category_label }}</span>
                                <h3>{{ $related->title }}</h3>
                                <p class="emp">{{ $related->employer }}</p>
                                <div class="tg"><span>{{ $related->location }}</span><span>{{ $related->employment_type_label }}</span></div>
                            </div>
                            <div class="f"><span class="cl">View details</span><b>Open job →</b></div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>
