@php
    $coverMedia = $school->getFirstMedia('cover');
    $galleryMedia = $school->getMedia('gallery');
    $profileImages = collect([$coverMedia])->filter()->concat($galleryMedia)->unique('id')->values();
    $lightboxImages = $profileImages->map(fn ($media) => [
        'url' => $media->getUrl(),
        'alt' => $school->name.' school photograph',
    ])->values()->all();

    $primaryPhone = $school->phone ?: $school->contacts->firstWhere('type', 'phone')?->value;
    $primaryEmail = $school->email ?: $school->contacts->firstWhere('type', 'email')?->value;
    $primaryWhatsapp = $school->whatsapp ?: $school->contacts->firstWhere('type', 'whatsapp')?->value;
    $whatsappDigits = preg_replace('/\D+/', '', (string) $primaryWhatsapp);
    $website = $school->website ?: $school->contacts->firstWhere('type', 'website')?->value;
    $websiteUrl = $website
        ? (\Illuminate\Support\Str::startsWith($website, ['http://', 'https://']) ? $website : 'https://'.$website)
        : null;

    $regionName = $school->district?->region?->name;
    $districtName = $school->district?->name;
    $subCountyName = $school->subCounty?->name;
    $locationText = collect([$districtName, $regionName])->filter()->unique()->implode(', ');
    $displayPhone = $primaryPhone ?: 'Not provided';
    $displayEmail = $primaryEmail ?: 'Not provided';
    $displayLocation = $locationText !== '' ? $locationText.', Uganda' : 'Not provided';
    $mapQuery = filled($school->latitude) && filled($school->longitude)
        ? $school->latitude.','.$school->longitude
        : trim(collect([$school->address, $subCountyName, $districtName, $regionName, 'Uganda'])->filter()->implode(', '));
    $mapUrl = $mapQuery !== ''
        ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapQuery)
        : null;

    $facebook = $school->contacts->firstWhere('type', 'facebook')?->value;
    $twitter = $school->contacts->firstWhere('type', 'twitter')?->value;
    $instagram = $school->contacts->firstWhere('type', 'instagram')?->value;
    $activeFees = $school->fees->where('is_active', true);
    $tuitionFees = $activeFees->where('category', 'tuition')->sortBy('amount');
    $lowestTuition = $tuitionFees->first();

    $quickFacts = [
        ['label' => 'Type', 'value' => $school->type?->name ?: 'Not provided', 'color' => '#d33'],
        ['label' => 'Ownership', 'value' => 'Not provided', 'color' => '#d33'],
        ['label' => 'Gender', 'value' => match($school->gender_policy) { 'boys' => 'Boys only', 'girls' => 'Girls only', default => 'Mixed (boys & girls)' }, 'color' => '#2f9e5a'],
        ['label' => 'Religion', 'value' => 'Not provided', 'color' => '#6b5bd2'],
        ['label' => 'Residence', 'value' => match($school->boarding_type) { 'boarding' => 'Boarding', 'mixed' => 'Day & boarding', default => 'Day school' }, 'color' => '#f0b030'],
        ['label' => 'Curriculum', 'value' => $school->curricula->pluck('name')->filter()->implode(', ') ?: 'Not provided', 'color' => '#f0b030'],
    ];
@endphp

<div class="school-detail-reference" x-data="lightbox" x-init="images = @js($lightboxImages)" x-on:inquiry-sent.window="$refs.enquiryDialog?.close()">
    {{-- Reference photo mosaic: one large image and three stacked holders. Empty slots keep the same geometry. --}}
    <?php
        $registerSchoolUrl = auth()->check()
            ? (auth()->user()->hasRole('admin') ? url('/admin') : (auth()->user()->hasRole('school_owner') ? route('owner.schools.create') : route('contact')))
            : route('register');
    ?>
    <a class="sref-register-tab" href="{{ $registerSchoolUrl }}">REGISTER YOUR SCHOOL</a>
    <section class="sref-mosaic" aria-label="School photos">
        <?php if ($profileImages->isNotEmpty()): ?>
            <button type="button" class="sref-mosaic__main" x-on:click="show(images, 0)" aria-label="View school photos">
                <img src="{{ $profileImages->first()->getUrl() }}" alt="{{ $school->name }} school photograph" fetchpriority="high">
                <span class="sref-chip">View all {{ $profileImages->count() }} {{ \Illuminate\Support\Str::plural('photo', $profileImages->count()) }}</span>
            </button>
        <?php else: ?>
            <div class="sref-mosaic__main sref-photo-placeholder sref-photo-placeholder--c" aria-label="School photographs coming soon">
                <span>{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                <small>School photos coming soon</small>
            </div>
        <?php endif; ?>
        <div class="sref-mosaic__side">
            <?php for ($tileIndex = 1; $tileIndex < 4; $tileIndex++): ?>
                <?php $media = $profileImages->get($tileIndex); ?>
                <?php if ($media): ?>
                    <button type="button" class="sref-mosaic__tile" x-on:click="show(images, {{ $tileIndex }})" aria-label="View school photograph {{ $tileIndex + 1 }}">
                        <img src="{{ $media->getUrl() }}" alt="{{ $school->name }} photograph {{ $tileIndex + 1 }}" loading="lazy">
                        <?php if ($tileIndex === 3 && $profileImages->count() > 4): ?>
                            <span class="sref-more-photo">+{{ $profileImages->count() - 4 }} more</span>
                        <?php endif; ?>
                    </button>
                <?php else: ?>
                    <div class="sref-mosaic__tile sref-photo-placeholder sref-photo-placeholder--{{ ['d', 'b', 'a'][$tileIndex - 1] }}" aria-hidden="true"></div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </section>

    <div class="sref-crumbs">
        <div class="site-wrap">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('schools.index') }}">Schools</a>
            <?php if ($school->type): ?><a href="{{ route('schools.index', ['type' => $school->type->id]) }}">{{ $school->type->name }}</a><?php endif; ?>
            <span>{{ $school->name }}</span>
        </div>
    </div>

    <main class="site-wrap sref-page">
        <div class="sref-cols">
            {{-- LEFT COLUMN: exact order and card arrangement from the supplied HTML --}}
            <div class="sref-stack">
                <div class="sref-school-heading">
                    <div class="sref-school-top">
                        <div class="sref-school-logo {{ ($school->getFirstMediaUrl('badge') || $school->getFirstMediaUrl('logo')) ? '' : 'sref-school-logo--placeholder' }}">
                            <?php $schoolIdentityImage = $school->getFirstMediaUrl('badge') ?: $school->getFirstMediaUrl('logo'); ?>
                            <?php if ($schoolIdentityImage): ?>
                                <img src="{{ $schoolIdentityImage }}" alt="{{ $school->name }} school badge">
                            <?php else: ?>
                                <span>School badge</span>
                            <?php endif; ?>
                        </div>
                        <div class="sref-school-copy">
                            <h1>{{ $school->name }}</h1>
                            <div class="sref-tags">
                                <?php if ($school->type): ?><span class="sref-tag sref-tag--gold">{{ $school->type->name }}</span><?php endif; ?>
                                <span class="sref-tag sref-tag--green">✓ Verified</span>
                                <?php foreach ($school->curricula->take(2) as $curriculum): ?>
                                    <span class="sref-tag sref-tag--green">{{ $curriculum->abbreviation }}</span>
                                <?php endforeach; ?>
                                <span class="sref-tag">{{ match($school->boarding_type) { 'boarding' => 'Boarding', 'mixed' => 'Day & boarding', default => 'Day school' } }}</span>
                                <span class="sref-tag">{{ match($school->gender_policy) { 'boys' => 'Boys', 'girls' => 'Girls', default => 'Mixed' } }}</span>
                            </div>
                            <div class="sref-contact-line">
                                <?php if ($primaryPhone): ?><span>{{ $primaryPhone }}</span><?php endif; ?>
                                <?php if ($primaryEmail): ?><span>{{ $primaryEmail }}</span><?php endif; ?>
                                <?php if ($locationText !== ''): ?><span>{{ $locationText }}<?php if ($regionName): ?>, Uganda<?php endif; ?></span><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="sref-actions">
                        <button type="button" class="sref-btn sref-btn--brand sref-btn--sm" x-on:click="$refs.enquiryDialog.showModal()">Apply for admission</button>
                        <?php if ($whatsappDigits !== ''): ?>
                            <a class="sref-btn sref-btn--wa sref-btn--sm" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">WhatsApp</a>
                        <?php endif; ?>
                        <button type="button" class="sref-btn sref-btn--line sref-btn--sm" x-data="{copied:false}" x-on:click="navigator.clipboard.writeText(window.location.href).then(() => copied = true).catch(() => copied = false)" x-text="copied ? 'Link copied' : 'Share'">Share</button>
                        <?php if (auth()->check()): ?>
                            <button type="button" class="sref-btn sref-btn--line sref-btn--sm" wire:click="toggleSave">{{ $this->isSaved() ? '♥ Saved' : '♡ Save' }}</button>
                        <?php else: ?>
                            <a href="{{ route('login') }}" class="sref-btn sref-btn--line sref-btn--sm">♡ Save</a>
                        <?php endif; ?>
                    </div>

                    <nav class="sref-tabs" aria-label="Profile sections" x-data="{active:'about'}">
                        <a href="#about" x-on:click="active='about'" :class="active === 'about' ? 'is-active' : ''">About</a>
                        <a href="#facilities" x-on:click="active='facilities'" :class="active === 'facilities' ? 'is-active' : ''">Facilities</a>
                        <a href="#events" x-on:click="active='events'" :class="active === 'events' ? 'is-active' : ''">Events</a>
                        <a href="#gallery" x-on:click="active='gallery'" :class="active === 'gallery' ? 'is-active' : ''">Gallery</a>
                        <a href="#news" x-on:click="active='news'" :class="active === 'news' ? 'is-active' : ''">News</a>
                    </nav>
                </div>

                <section class="sref-card sref-pad sref-about" id="about">
                    <h2>About {{ $school->name }}</h2>
                    <?php if ($school->tagline): ?>
                        <div class="sref-quote">{{ $school->tagline }}</div>
                    <?php endif; ?>
                    <?php if ($school->description): ?>
                        <div class="sref-description">{{ $school->description }}</div>
                    <?php else: ?>
                        <p class="sref-empty">The school has not added an introduction yet.</p>
                    <?php endif; ?>
                    <?php if ($school->founded_year || $school->student_count_approx): ?>
                        <div class="sref-about-meta">
                            <?php if ($school->founded_year): ?><span>Established {{ $school->founded_year }}</span><?php endif; ?>
                            <?php if ($school->student_count_approx): ?><span>Approximately {{ number_format($school->student_count_approx) }} learners</span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>

                @if($admissionsVisible && ($school->admissions_open || filled($school->admission_classes) || filled($school->admission_requirements) || filled($school->how_to_apply)))
                    <section class="sref-card sref-pad" id="admissions">
                        <h2>Admissions <span>information</span></h2>
                        <div class="sref-about-meta">
                            <span>{{ $school->admissions_open ? 'Admissions are open' : 'Admissions are currently closed' }}</span>
                            @if($school->admission_deadline)<span>Deadline: {{ $school->admission_deadline->format('d M Y') }}</span>@endif
                            @if(filled($school->application_fee))<span>Application / interview fee: {{ $school->application_fee }}</span>@endif
                        </div>
                        @if(filled($school->admission_classes))
                            <p class="sref-description"><strong>Classes admitting:</strong> {{ $school->admission_classes }}</p>
                        @endif
                        @if(filled($school->admissions_contact))
                            <p class="sref-description"><strong>Admissions contact:</strong> {{ $school->admissions_contact }}</p>
                        @endif
                        @if(filled($school->admission_requirements))
                            <h3>Requirements</h3>
                            <ul class="sref-facilities">
                                @foreach(preg_split('/\r\n|\r|\n/', $school->admission_requirements) as $requirement)
                                    @if(trim($requirement))<li>{{ trim($requirement) }}</li>@endif
                                @endforeach
                            </ul>
                        @endif
                        @if(filled($school->how_to_apply))
                            <h3>How to apply</h3>
                            <div class="sref-description">{{ $school->how_to_apply }}</div>
                        @endif
                        @if($school->admissions_open)
                            <button type="button" class="sref-btn sref-btn--brand sref-btn--sm" x-on:click="$refs.enquiryDialog.showModal()">Apply for admission</button>
                        @endif
                    </section>
                @endif

                <section class="sref-card sref-pad" id="facilities">
                    <h2>Our <span>facilities</span></h2>
                    <?php if ($school->facilities->isNotEmpty()): ?>
                        <div class="sref-facilities">
                            <?php foreach ($school->facilities as $facility): ?>
                                <span><i></i>{{ $facility->name }}</span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="sref-empty">No facilities have been added to this school's profile yet.</p>
                    <?php endif; ?>
                </section>

                <section class="sref-card sref-pad" id="gallery">
                    <h2>Photo <span>gallery</span></h2>
                    <div class="sref-gallery-primary">
                        <?php for ($galleryIndex = 0; $galleryIndex < 3; $galleryIndex++): ?>
                            <?php $media = $profileImages->get($galleryIndex); ?>
                            <?php if ($media): ?>
                                <button type="button" class="{{ $galleryIndex === 0 ? 'is-large' : '' }}" x-on:click="show(images, {{ $galleryIndex }})" aria-label="Open photo {{ $galleryIndex + 1 }}">
                                    <img src="{{ $media->getUrl() }}" alt="{{ $school->name }} photograph {{ $galleryIndex + 1 }}" loading="lazy">
                                </button>
                            <?php else: ?>
                                <div class="sref-gallery-placeholder sref-photo-placeholder sref-photo-placeholder--{{ ['c', 'b', 'd'][$galleryIndex] }} {{ $galleryIndex === 0 ? 'is-large' : '' }}" aria-hidden="true">
                                    <?php if ($galleryIndex === 0 && $profileImages->isEmpty()): ?><span>Gallery photos will appear here</span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <div class="sref-gallery-secondary">
                        <?php for ($galleryIndex = 3; $galleryIndex < 7; $galleryIndex++): ?>
                            <?php $media = $profileImages->get($galleryIndex); ?>
                            <?php if ($media): ?>
                                <button type="button" x-on:click="show(images, {{ $galleryIndex }})" aria-label="Open photo {{ $galleryIndex + 1 }}">
                                    <img src="{{ $media->getUrl() }}" alt="{{ $school->name }} photograph {{ $galleryIndex + 1 }}" loading="lazy">
                                    <?php if ($galleryIndex === 6 && $profileImages->count() > 7): ?>
                                        <span>+{{ $profileImages->count() - 7 }} more</span>
                                    <?php endif; ?>
                                </button>
                            <?php else: ?>
                                <div class="sref-gallery-placeholder sref-photo-placeholder sref-photo-placeholder--{{ ['a', 'd', 'b', 'c'][$galleryIndex - 3] }}" aria-hidden="true"></div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </section>

                <section class="sref-card sref-pad" id="events">
                    <h2>Upcoming <span>and past events</span></h2>
                    @if($schoolEvents->isNotEmpty())
                        <div class="sref-post-list">
                            @foreach($schoolEvents as $event)
                                <a class="sref-post-row" href="{{ route('events.show', $event) }}">
                                    <span class="sref-post-date">{{ $event->starts_at?->format('d M Y') ?? 'Event' }}</span>
                                    <span class="sref-post-copy"><strong>{{ $event->title }}</strong><small>{{ $event->venue ?: 'School event' }} @if($event->starts_at) · {{ $event->starts_at->format('g:i A') }} @endif</small><span>{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 150) }}</span></span>
                                    <b aria-hidden="true">→</b>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="sref-empty sref-empty--center">No events have been published by this school yet. Check back for open days and school activities.</p>
                    @endif
                </section>

                <section class="sref-card sref-pad" id="news">
                    <h2>School <span>news</span></h2>
                    @if($schoolNews->isNotEmpty())
                        <div class="sref-post-list">
                            @foreach($schoolNews as $newsPost)
                                <a class="sref-post-row" href="{{ route('school-news.show', $newsPost) }}">
                                    <span class="sref-post-date">{{ $newsPost->published_at?->format('d M Y') }}</span>
                                    <span class="sref-post-copy"><strong>{{ $newsPost->title }}</strong><span>{{ \Illuminate\Support\Str::limit(strip_tags($newsPost->description), 170) }}</span></span>
                                    <b aria-hidden="true">→</b>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="sref-empty">No school news has been published yet. <a href="{{ route('news.index') }}">Browse education news and updates</a>.</p>
                    @endif
                </section>

                @if($schoolBursaries->isNotEmpty())
                    <div class="sref-band">
                        <div>
                            <small>Student opportunities</small>
                            <h3>{{ $schoolBursaries->count() }} scholarship or bursary {{ \Illuminate\Support\Str::plural('opportunity', $schoolBursaries->count()) }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($schoolBursaries->first()->title, 100) }}. Check the deadline and details on the opportunity page.</p>
                        </div>
                        <a class="sref-btn sref-btn--gold" href="{{ route('scholarships.show', $schoolBursaries->first()) }}">View opportunity</a>
                    </div>
                @else
                    <div class="sref-band">
                        <div>
                            <small>Opportunities</small>
                            <h3>Scholarships and bursaries</h3>
                            <p>Browse funding opportunities published by participating schools.</p>
                        </div>
                        <a class="sref-btn sref-btn--gold" href="{{ route('scholarships.index') }}">Browse scholarships</a>
                    </div>
                @endif

                {{-- Existing moderated review feature is retained after the reference layout. --}}
                    <section class="sref-card sref-pad sref-reviews" id="reviews">
                        <h2>Parent and student <span>reviews</span></h2>
                        <p class="sref-review-count">{{ number_format($school->reviews_count) }} approved reviews</p>
                        <?php if ((float) $school->rating_average > 0): ?>
                            <p class="sref-review-average">★ {{ number_format((float) $school->rating_average, 1) }} / 5</p>
                        <?php endif; ?>
                        <?php if ($school->approvedReviews->isNotEmpty()): ?>
                        <?php foreach ($school->approvedReviews as $review): ?>
                            <article class="sref-review">
                                <div><strong>{{ $review->title }}</strong><b>★ {{ $review->rating }}/5</b></div>
                                <p>{{ $review->body }}</p>
                                <small>{{ $review->user?->name ?? 'FindurSch user' }} · {{ ucfirst($review->reviewer_type) }}</small>
                                <?php if ($review->owner_response): ?><blockquote><strong>School response</strong>{{ $review->owner_response }}</blockquote><?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <p class="sref-empty">No approved reviews have been published yet.</p>
                        <?php endif; ?>

                        <?php if (session('review_submitted')): ?><p class="sref-success">{{ session('review_submitted') }}</p><?php endif; ?>
                        <?php if ($alreadyReviewed): ?>
                            <p class="sref-empty">You have already submitted a review for this school.</p>
                        <?php elseif (auth()->check() && auth()->user()->hasVerifiedEmail()): ?>
                            <form wire:submit="submitReview" class="sref-form">
                                <div class="sref-form-grid">
                                    <label>I am a<select wire:model="reviewerType"><option value="parent">Parent / Guardian</option><option value="student">Student</option><option value="alumni">Alumni</option><option value="staff">Staff</option><option value="other">Other</option></select></label>
                                    <label>Rating<select wire:model="reviewRating"><?php for ($rating = 5; $rating >= 1; $rating--): ?><option value="{{ $rating }}">{{ $rating }} out of 5</option><?php endfor; ?></select></label>
                                    <label class="full">Review title<input wire:model="reviewTitle" required maxlength="255"><?php if ($errors->has('reviewTitle')): ?><small class="sref-error">{{ $message }}</small><?php endif; ?></label>
                                    <label class="full">Your experience<textarea wire:model="reviewBody" required minlength="20" maxlength="5000" rows="4"></textarea><?php if ($errors->has('reviewBody')): ?><small class="sref-error">{{ $message }}</small><?php endif; ?></label>
                                </div>
                                <button class="sref-btn sref-btn--brand" type="submit" wire:loading.attr="disabled" wire:target="submitReview">Submit review</button>
                            </form>
                        <?php elseif (auth()->check()): ?>
                            <p class="sref-empty">Verify your email before submitting a review. <a href="{{ route('verification.notice') }}">Verify email</a>.</p>
                        <?php else: ?>
                            <p class="sref-empty"><a href="{{ route('login') }}">Sign in</a> to leave a review.</p>
                        <?php endif; ?>
                    </section>
            </div>

            {{-- RIGHT SIDEBAR: same card types and item order as the uploaded HTML --}}
            <aside class="sref-sidebar">
                <section class="sref-join">
                    <h3>Ready to join {{ $school->name }}?</h3>
                    <p>Applications are open. Apply today and secure a place for your child.</p>
                    <button type="button" class="sref-btn sref-btn--gold sref-btn--block" x-on:click="$refs.enquiryDialog.showModal()">Apply for admission</button>
                    <button type="button" class="sref-btn sref-btn--ghost sref-btn--block" x-on:click="$refs.enquiryDialog.showModal()">Send enquiry</button>
                </section>

                <section class="sref-contact">
                    <h3>Contact the school</h3>
                    <div class="sref-contact-item"><i>☎</i><div><small>Phone</small><b>{{ $displayPhone }}</b></div></div>
                    <div class="sref-contact-item"><i>✉</i><div><small>Email</small><b>{{ $displayEmail }}</b></div></div>
                    <div class="sref-contact-item"><i>⌂</i><div><small>Location</small><b>{{ $displayLocation }}</b></div></div>
                    <div class="sref-contact-actions">
                        <button type="button" class="sref-btn sref-btn--gold sref-btn--sm" x-on:click="$refs.enquiryDialog.showModal()">Apply now</button>
                        <?php if ($whatsappDigits !== ''): ?>
                            <a class="sref-btn sref-btn--ghost sref-btn--sm" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">WhatsApp us</a>
                        <?php else: ?>
                            <span class="sref-btn sref-btn--ghost sref-btn--sm is-disabled">WhatsApp us</span>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="sref-box">
                    <h5>Quick facts</h5>
                    <div class="sref-quick-facts">
                        <?php foreach ($quickFacts as $fact): ?>
                            <div><small>{{ $fact['label'] }}</small><b><i style="--fact-color:{{ $fact['color'] }}"></i>{{ $fact['value'] }}</b></div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="sref-box">
                    <h5>School fees</h5>
                    <?php if ($lowestTuition): ?>
                        <div class="sref-fee">
                            <b>{{ $lowestTuition->currency }} {{ number_format((float) $lowestTuition->amount) }}</b>
                            <small>Lowest listed tuition · {{ str_replace('_', ' ', $lowestTuition->period) }}</small>
                        </div>
                    <?php else: ?>
                        <div class="sref-fee"><b>Contact school</b><small>for current fee structure</small></div>
                    <?php endif; ?>
                </section>

                <x-site-ad-banner placement="side" />

                <section class="sref-box">
                    <h5>Find us on social media</h5>
                    <div class="sref-social">
                        <?php if ($facebook): ?><a class="sref-btn sref-btn--facebook" href="{{ $facebook }}" target="_blank" rel="noopener">Facebook</a><?php else: ?><span class="sref-btn sref-btn--inactive">Facebook</span><?php endif; ?>
                        <?php if ($twitter): ?><a class="sref-btn sref-btn--x" href="{{ $twitter }}" target="_blank" rel="noopener">Twitter / X</a><?php else: ?><span class="sref-btn sref-btn--inactive">Twitter / X</span><?php endif; ?>
                        <?php if ($whatsappDigits): ?><a class="sref-btn sref-btn--wa" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">WhatsApp</a><?php else: ?><span class="sref-btn sref-btn--inactive">WhatsApp</span><?php endif; ?>
                        <button type="button" class="sref-btn sref-btn--line" x-data="{copied:false}" x-on:click="navigator.clipboard.writeText(window.location.href).then(() => copied = true)" x-text="copied ? 'Link copied' : 'Copy link'">Copy link</button>
                    </div>
                </section>

                <section class="sref-box">
                    <h5>Location</h5>
                    <dl class="sref-location-list">
                        <div><dt>Region</dt><dd class="is-link">{{ $regionName ?: 'Not provided' }}</dd></div>
                        <div><dt>District</dt><dd class="is-link">{{ $districtName ?: 'Not provided' }}</dd></div>
                        <div><dt>County</dt><dd>Not provided</dd></div>
                        <div><dt>Sub-county</dt><dd>{{ $subCountyName ?: 'Not provided' }}</dd></div>
                        <div><dt>Village</dt><dd>Not provided</dd></div>
                    </dl>
                    <?php if ($mapUrl): ?>
                        <div class="sref-map"><div><a href="{{ $mapUrl }}" target="_blank" rel="noopener">View on Google Maps</a>{{ $locationText ?: 'Uganda' }}</div></div>
                    <?php else: ?>
                        <div class="sref-map"><div>Map location has not been provided.</div></div>
                    <?php endif; ?>
                </section>
            </aside>
        </div>
    </main>

    {{-- Admission dialog: preserves FindurSch sign-in, verification and inquiry rules --}}
        <dialog x-ref="enquiryDialog" class="sref-dialog">
            <div class="sref-dialog-heading">
                <div><h3>Admission enquiry</h3><p>{{ $school->name }}. Send your details to the school.</p></div>
                <button type="button" x-on:click="$refs.enquiryDialog.close()" aria-label="Close">×</button>
            </div>
            <?php if (session('inquiry_sent')): ?><p class="sref-success">{{ session('inquiry_sent') }}</p><?php endif; ?>
            <?php if (auth()->check()): ?>
                <?php if (auth()->user()->hasVerifiedEmail()): ?>
                    <p class="sref-dialog-note">Your name and email are taken from your verified FindurSch account.</p>
                    <div class="sref-account"><b>{{ auth()->user()->name }}</b><span>{{ auth()->user()->email }}</span></div>
                    <form wire:submit="submitInquiry" class="sref-form">
                        <div class="sref-form-grid">
                            <label>Phone<input type="tel" wire:model="inquiryPhone" required maxlength="30"><?php if ($errors->has('inquiryPhone')): ?><small class="sref-error">{{ $message }}</small><?php endif; ?></label>
                            <label>Student name<input wire:model="inquiryStudentName" maxlength="255"></label>
                            <label>Class applying for<input wire:model="inquiryLevel" maxlength="120" placeholder="e.g. Primary 1"></label>
                            <label>Student age<input type="number" min="1" max="100" wire:model="inquiryStudentAge"></label>
                            <label class="full">Preferred intake
                                <select wire:model="inquiryIntake"><option value="">Select intake</option><option value="Term 1">Term 1</option><option value="Term 2">Term 2</option><option value="Term 3">Term 3</option><option value="Next available intake">Next available intake</option></select>
                            </label>
                            <label class="full">Message<textarea rows="3" wire:model="inquiryMessage" required minlength="10" maxlength="4800"></textarea><?php if ($errors->has('inquiryMessage')): ?><small class="sref-error">{{ $message }}</small><?php endif; ?></label>
                        </div>
                        <div class="sref-dialog-actions">
                            <button type="button" class="sref-btn sref-btn--line" x-on:click="$refs.enquiryDialog.close()">Cancel</button>
                            <button type="submit" class="sref-btn sref-btn--brand" wire:loading.attr="disabled" wire:target="submitInquiry"><span wire:loading.remove wire:target="submitInquiry">Send enquiry</span><span wire:loading wire:target="submitInquiry">Sending…</span></button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="sref-dialog-note">Verify your email address before sending an admission enquiry.</p>
                    <a class="sref-btn sref-btn--brand" href="{{ route('verification.notice') }}">Verify email</a>
                <?php endif; ?>
            <?php else: ?>
                <p class="sref-dialog-note">Sign in to send an admission enquiry. Your name and email will be shared with the school from your account.</p>
                <div class="sref-dialog-actions"><a class="sref-btn sref-btn--brand" href="{{ route('login') }}">Sign in</a><a class="sref-btn sref-btn--line" href="{{ route('register') }}">Create an account</a></div>
            <?php endif; ?>
        </dialog>

    <div x-cloak x-show="open" x-transition.opacity class="sref-lightbox" role="dialog" aria-modal="true" aria-label="School photo gallery" x-on:keydown.escape.window="close()" x-on:click.self="close()">
        <button type="button" class="sref-lightbox__close" x-on:click="close()" aria-label="Close gallery">×</button>
        <button type="button" class="sref-lightbox__prev" x-on:click="prev()" aria-label="Previous photo">‹</button>
        <template x-if="images.length"><figure><img :src="images[activeIndex].url" :alt="images[activeIndex].alt"><figcaption><span x-text="images[activeIndex].alt"></span><small><span x-text="activeIndex + 1"></span> / <span x-text="images.length"></span></small></figcaption></figure></template>
        <button type="button" class="sref-lightbox__next" x-on:click="next()" aria-label="Next photo">›</button>
    </div>
</div>
