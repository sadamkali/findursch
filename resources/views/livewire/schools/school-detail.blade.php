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
        ['label' => 'Curriculum', 'value' => $school->curricula->pluck('abbreviation')->filter()->implode(', ') ?: 'Not provided', 'color' => '#f0b030'],
    ];
@endphp

<div class="school-detail-reference" x-data="lightbox" x-init="images = @js($lightboxImages)" @inquiry-sent.window="$refs.enquiryDialog?.close()">
    {{-- Same full-width photo mosaic composition as findursch-school.html --}}
    <section class="sref-mosaic" aria-label="School photos">
        @if($profileImages->isNotEmpty())
            <button type="button" class="sref-mosaic__main" @click="show(images, 0)" aria-label="View school photos">
                <img src="{{ $profileImages->first()->getUrl() }}" alt="{{ $school->name }}" fetchpriority="high">
                <span class="sref-chip">View all {{ $profileImages->count() }} {{ \Illuminate\Support\Str::plural('photo', $profileImages->count()) }}</span>
            </button>
            @if($profileImages->count() > 1)
                <div class="sref-mosaic__side">
                    @foreach($profileImages->skip(1)->take(3) as $media)
                        <button type="button" class="sref-mosaic__tile" @click="show(images, {{ $loop->index + 1 }})" aria-label="View school photograph {{ $loop->iteration + 1 }}">
                            <img src="{{ $media->getUrl() }}" alt="{{ $school->name }} photograph {{ $loop->iteration + 1 }}" loading="lazy">
                            @if($loop->last && $profileImages->count() > 4)
                                <span class="sref-more-photo">+{{ $profileImages->count() - 4 }} more</span>
                            @endif
                        </button>
                    @endforeach
                    @for($placeholder = $profileImages->count(); $placeholder < 4; $placeholder++)
                        <div class="sref-mosaic__tile sref-mosaic__tile--empty" aria-hidden="true"></div>
                    @endfor
                </div>
            @endif
        @else
            <div class="sref-mosaic__placeholder">
                <span>{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                <small>School photos will appear here when uploaded</small>
            </div>
        @endif
    </section>

    <div class="sref-crumbs">
        <div class="site-wrap">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('schools.index') }}">Schools</a>
            @if($school->type)<a href="{{ route('schools.index', ['type' => $school->type->id]) }}">{{ $school->type->name }}</a>@endif
            <span>{{ $school->name }}</span>
        </div>
    </div>

    <main class="site-wrap sref-page">
        <div class="sref-cols">
            {{-- LEFT COLUMN: exact order and card arrangement from the supplied HTML --}}
            <div class="sref-stack">
                <div class="sref-school-heading">
                    <div class="sref-school-top">
                        <div class="sref-school-logo">
                            @if($school->getFirstMediaUrl('logo'))
                                <img src="{{ $school->getFirstMediaUrl('logo') }}" alt="{{ $school->name }} logo">
                            @else
                                {{ strtoupper(mb_substr($school->name, 0, 2)) }}
                            @endif
                        </div>
                        <div class="sref-school-copy">
                            <h1>{{ $school->name }}</h1>
                            <div class="sref-tags">
                                @if($school->type)<span class="sref-tag sref-tag--gold">{{ $school->type->name }}</span>@endif
                                @if($school->is_demo)
                                    <span class="sref-tag">Demo sample · Not verified</span>
                                @else
                                    <span class="sref-tag sref-tag--green">✓ Verified</span>
                                @endif
                                @foreach($school->curricula->take(2) as $curriculum)
                                    <span class="sref-tag sref-tag--green">{{ $curriculum->abbreviation }}</span>
                                @endforeach
                                <span class="sref-tag">{{ match($school->boarding_type) { 'boarding' => 'Boarding', 'mixed' => 'Day & boarding', default => 'Day school' } }}</span>
                                <span class="sref-tag">{{ match($school->gender_policy) { 'boys' => 'Boys', 'girls' => 'Girls', default => 'Mixed' } }}</span>
                            </div>
                            <div class="sref-contact-line">
                                @if($primaryPhone)<span>{{ $primaryPhone }}</span>@endif
                                @if($primaryEmail)<span>{{ $primaryEmail }}</span>@endif
                                @if($locationText !== '')<span>{{ $locationText }}@if($regionName), Uganda@endif</span>@endif
                            </div>
                        </div>
                    </div>

                    <div class="sref-actions">
                        @if($school->is_demo)
                            <button type="button" class="sref-btn sref-btn--brand" disabled>Admissions disabled for sample</button>
                        @else
                            <button type="button" class="sref-btn sref-btn--brand sref-btn--sm" @click="$refs.enquiryDialog.showModal()">Apply for admission</button>
                            @if($whatsappDigits !== '')
                                <a class="sref-btn sref-btn--wa sref-btn--sm" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">WhatsApp</a>
                            @endif
                        @endif
                        <button type="button" class="sref-btn sref-btn--line sref-btn--sm" x-data="{copied:false}" @click="navigator.clipboard.writeText(window.location.href).then(() => copied = true).catch(() => copied = false)" x-text="copied ? 'Link copied' : 'Share'">Share</button>
                        @auth
                            @if(!$school->is_demo)
                                <button type="button" class="sref-btn sref-btn--line sref-btn--sm" wire:click="toggleSave">{{ $this->isSaved() ? '♥ Saved' : '♡ Save' }}</button>
                            @endif
                        @else
                            @if(!$school->is_demo)<a href="{{ route('login') }}" class="sref-btn sref-btn--line sref-btn--sm">♡ Save</a>@endif
                        @endauth
                    </div>

                    <nav class="sref-tabs" aria-label="Profile sections" x-data="{active:'about'}">
                        <a href="#about" @click="active='about'" :class="active === 'about' ? 'is-active' : ''">About</a>
                        <a href="#facilities" @click="active='facilities'" :class="active === 'facilities' ? 'is-active' : ''">Facilities</a>
                        <a href="#events" @click="active='events'" :class="active === 'events' ? 'is-active' : ''">Events</a>
                        <a href="#gallery" @click="active='gallery'" :class="active === 'gallery' ? 'is-active' : ''">Gallery</a>
                        <a href="#news" @click="active='news'" :class="active === 'news' ? 'is-active' : ''">News</a>
                    </nav>
                </div>

                <section class="sref-card sref-pad sref-about" id="about">
                    <h2>About {{ $school->name }}</h2>
                    @if($school->tagline)
                        <div class="sref-quote">{{ $school->tagline }}</div>
                    @endif
                    @if($school->description)
                        <div class="sref-description">{{ $school->description }}</div>
                    @else
                        <p class="sref-empty">The school has not added an introduction yet.</p>
                    @endif
                    @if($school->founded_year || $school->student_count_approx)
                        <div class="sref-about-meta">
                            @if($school->founded_year)<span>Established {{ $school->founded_year }}</span>@endif
                            @if($school->student_count_approx)<span>Approximately {{ number_format($school->student_count_approx) }} learners</span>@endif
                        </div>
                    @endif
                </section>

                <section class="sref-card sref-pad" id="facilities">
                    <h2>Our <span>facilities</span></h2>
                    @if($school->facilities->isNotEmpty())
                        <div class="sref-facilities">
                            @foreach($school->facilities as $facility)
                                <span><i></i>{{ $facility->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="sref-empty">No facilities have been added to this school's profile yet.</p>
                    @endif
                </section>

                <section class="sref-card sref-pad" id="gallery">
                    <h2>Photo <span>gallery</span></h2>
                    @if($profileImages->isNotEmpty())
                        <div class="sref-gallery-primary">
                            @foreach($profileImages->take(3) as $media)
                                <button type="button" class="{{ $loop->first ? 'is-large' : '' }}" @click="show(images, {{ $loop->index }})" aria-label="Open photo {{ $loop->iteration }}">
                                    <img src="{{ $media->getUrl() }}" alt="{{ $school->name }} photograph {{ $loop->iteration }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                        @if($profileImages->count() > 3)
                            <div class="sref-gallery-secondary">
                                @foreach($profileImages->skip(3)->take(4) as $media)
                                    <button type="button" @click="show(images, {{ $loop->index + 3 }})" aria-label="Open photo {{ $loop->index + 4 }}">
                                        <img src="{{ $media->getUrl() }}" alt="{{ $school->name }} photograph {{ $loop->index + 4 }}" loading="lazy">
                                        @if($loop->last && $profileImages->count() > 7)
                                            <span>+{{ $profileImages->count() - 7 }} more</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="sref-empty">No photographs have been uploaded by this school yet.</p>
                    @endif
                </section>

                <section class="sref-card sref-pad" id="events">
                    <h2>Upcoming <span>and past events</span></h2>
                    <p class="sref-empty sref-empty--center">No events posted yet.</p>
                </section>

                <section class="sref-card sref-pad" id="news">
                    <h2>School <span>news</span></h2>
                    <p class="sref-empty">No school news posted yet. <a href="{{ route('articles.index') }}">Browse education news and guides</a>.</p>
                </section>

                <div class="sref-band">
                    <div>
                        <small>Opportunities</small>
                        <h3>Explore education news and school guides</h3>
                        <p>Read education updates, guidance and useful information for families.</p>
                    </div>
                    <a class="sref-btn sref-btn--gold" href="{{ route('articles.index') }}">Explore resources</a>
                </div>

                {{-- Existing moderated review feature is retained after the reference layout. --}}
                @if(!$school->is_demo)
                    <section class="sref-card sref-pad sref-reviews" id="reviews">
                        <h2>Parent and student <span>reviews</span></h2>
                        <p class="sref-review-count">{{ number_format($school->reviews_count) }} approved reviews</p>
                        @if((float) $school->rating_average > 0)
                            <p class="sref-review-average">★ {{ number_format((float) $school->rating_average, 1) }} / 5</p>
                        @endif
                        @forelse($school->approvedReviews as $review)
                            <article class="sref-review">
                                <div><strong>{{ $review->title }}</strong><b>★ {{ $review->rating }}/5</b></div>
                                <p>{{ $review->body }}</p>
                                <small>{{ $review->user?->name ?? 'FindurSch user' }} · {{ ucfirst($review->reviewer_type) }}</small>
                                @if($review->owner_response)<blockquote><strong>School response</strong>{{ $review->owner_response }}</blockquote>@endif
                            </article>
                        @empty
                            <p class="sref-empty">No approved reviews have been published yet.</p>
                        @endforelse

                        @if(session('review_submitted'))<p class="sref-success">{{ session('review_submitted') }}</p>@endif
                        @if($alreadyReviewed)
                            <p class="sref-empty">You have already submitted a review for this school.</p>
                        @elseif(auth()->check() && auth()->user()->hasVerifiedEmail())
                            <form wire:submit="submitReview" class="sref-form">
                                <div class="sref-form-grid">
                                    <label>I am a<select wire:model="reviewerType"><option value="parent">Parent / Guardian</option><option value="student">Student</option><option value="alumni">Alumni</option><option value="staff">Staff</option><option value="other">Other</option></select></label>
                                    <label>Rating<select wire:model="reviewRating">@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} out of 5</option>@endfor</select></label>
                                    <label class="full">Review title<input wire:model="reviewTitle" required maxlength="255">@error('reviewTitle')<small class="sref-error">{{ $message }}</small>@enderror</label>
                                    <label class="full">Your experience<textarea wire:model="reviewBody" required minlength="20" maxlength="5000" rows="4"></textarea>@error('reviewBody')<small class="sref-error">{{ $message }}</small>@enderror</label>
                                </div>
                                <button class="sref-btn sref-btn--brand" type="submit" wire:loading.attr="disabled" wire:target="submitReview">Submit review</button>
                            </form>
                        @elseif(auth()->check())
                            <p class="sref-empty">Verify your email before submitting a review. <a href="{{ route('verification.notice') }}">Verify email</a>.</p>
                        @else
                            <p class="sref-empty"><a href="{{ route('login') }}">Sign in</a> to leave a review.</p>
                        @endif
                    </section>
                @endif
            </div>

            {{-- RIGHT SIDEBAR: same card types and item order as the uploaded HTML --}}
            <aside class="sref-sidebar">
                <section class="sref-join">
                    @if($school->is_demo)
                        <h3>Sample school profile</h3>
                        <p>This is demonstration information. Admission enquiries and direct contact actions are disabled for this sample listing.</p>
                        <span class="sref-sample-note">Preview only · Not a real school</span>
                    @else
                        <h3>Ready to join {{ $school->name }}?</h3>
                        <p>Ask the school about available places, admission requirements and the next intake.</p>
                        <button type="button" class="sref-btn sref-btn--gold sref-btn--block" @click="$refs.enquiryDialog.showModal()">Apply for admission</button>
                        <button type="button" class="sref-btn sref-btn--ghost sref-btn--block" @click="$refs.enquiryDialog.showModal()">Send enquiry</button>
                    @endif
                </section>

                <section class="sref-contact">
                    <h3>Contact the school</h3>
                    @if($primaryPhone)
                        <div class="sref-contact-item"><i>☎</i><div><small>Phone</small><b>{{ $primaryPhone }}</b></div></div>
                    @endif
                    @if($primaryEmail)
                        <div class="sref-contact-item"><i>✉</i><div><small>Email</small><b>{{ $primaryEmail }}</b></div></div>
                    @endif
                    @if($locationText !== '')
                        <div class="sref-contact-item"><i>⌂</i><div><small>Location</small><b>{{ $locationText }}</b></div></div>
                    @endif
                    @if($school->is_demo)
                        <p class="sref-sample-contact">Sample details only. Contact links are disabled.</p>
                    @else
                        <div class="sref-contact-actions">
                            <button type="button" class="sref-btn sref-btn--gold sref-btn--sm" @click="$refs.enquiryDialog.showModal()">Apply now</button>
                            @if($whatsappDigits !== '')
                                <a class="sref-btn sref-btn--ghost sref-btn--sm" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">WhatsApp us</a>
                            @else
                                <span class="sref-btn sref-btn--ghost sref-btn--sm is-disabled">WhatsApp us</span>
                            @endif
                        </div>
                    @endif
                    @if(!$primaryPhone && !$primaryEmail && !$locationText)
                        <p class="sref-sample-contact">Contact details have not been provided yet.</p>
                    @endif
                </section>

                <section class="sref-box">
                    <h5>Quick facts</h5>
                    <div class="sref-quick-facts">
                        @foreach($quickFacts as $fact)
                            <div><small>{{ $fact['label'] }}</small><b><i style="--fact-color:{{ $fact['color'] }}"></i>{{ $fact['value'] }}</b></div>
                        @endforeach
                    </div>
                </section>

                <section class="sref-box">
                    <h5>School fees</h5>
                    @if($lowestTuition)
                        <div class="sref-fee">
                            <b>{{ $lowestTuition->currency }} {{ number_format((float) $lowestTuition->amount) }}</b>
                            <small>Lowest listed tuition · {{ str_replace('_', ' ', $lowestTuition->period) }}</small>
                        </div>
                    @else
                        <div class="sref-fee"><b>Contact school</b><small>for current fee structure</small></div>
                    @endif
                </section>

                <div class="sref-advert">
                    <div>Your advert here<small>300 × 250</small></div>
                </div>

                <section class="sref-box">
                    <h5>Find us on social media</h5>
                    <div class="sref-social">
                        @if($facebook)<a class="sref-btn sref-btn--facebook" href="{{ $facebook }}" target="_blank" rel="noopener">Facebook</a>@else<span class="sref-btn sref-btn--inactive">Facebook</span>@endif
                        @if($twitter)<a class="sref-btn sref-btn--x" href="{{ $twitter }}" target="_blank" rel="noopener">Twitter / X</a>@else<span class="sref-btn sref-btn--inactive">Twitter / X</span>@endif
                        @if($whatsappDigits && !$school->is_demo)<a class="sref-btn sref-btn--wa" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">WhatsApp</a>@else<span class="sref-btn sref-btn--inactive">WhatsApp</span>@endif
                        <button type="button" class="sref-btn sref-btn--line" x-data="{copied:false}" @click="navigator.clipboard.writeText(window.location.href).then(() => copied = true)" x-text="copied ? 'Link copied' : 'Copy link'">Copy link</button>
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
                    @if($mapUrl)
                        <div class="sref-map"><div><a href="{{ $mapUrl }}" target="_blank" rel="noopener">View on Google Maps</a>{{ $locationText ?: 'Uganda' }}</div></div>
                    @else
                        <div class="sref-map"><div>Map location has not been provided.</div></div>
                    @endif
                </section>
            </aside>
        </div>
    </main>

    {{-- Admission dialog: preserves FindurSch sign-in, verification and inquiry rules --}}
    @if(!$school->is_demo)
        <dialog x-ref="enquiryDialog" class="sref-dialog">
            <div class="sref-dialog-heading">
                <div><h3>Admission enquiry</h3><p>{{ $school->name }}. Send your details to the school.</p></div>
                <button type="button" @click="$refs.enquiryDialog.close()" aria-label="Close">×</button>
            </div>
            @if(session('inquiry_sent'))<p class="sref-success">{{ session('inquiry_sent') }}</p>@endif
            @auth
                @if(auth()->user()->hasVerifiedEmail())
                    <p class="sref-dialog-note">Your name and email are taken from your verified FindurSch account.</p>
                    <div class="sref-account"><b>{{ auth()->user()->name }}</b><span>{{ auth()->user()->email }}</span></div>
                    <form wire:submit="submitInquiry" class="sref-form">
                        <div class="sref-form-grid">
                            <label>Phone<input type="tel" wire:model="inquiryPhone" required maxlength="30">@error('inquiryPhone')<small class="sref-error">{{ $message }}</small>@enderror</label>
                            <label>Student name<input wire:model="inquiryStudentName" maxlength="255"></label>
                            <label>Class applying for<input wire:model="inquiryLevel" maxlength="120" placeholder="e.g. Primary 1"></label>
                            <label>Student age<input type="number" min="1" max="100" wire:model="inquiryStudentAge"></label>
                            <label class="full">Preferred intake
                                <select wire:model="inquiryIntake"><option value="">Select intake</option><option value="Term 1">Term 1</option><option value="Term 2">Term 2</option><option value="Term 3">Term 3</option><option value="Next available intake">Next available intake</option></select>
                            </label>
                            <label class="full">Message<textarea rows="3" wire:model="inquiryMessage" required minlength="10" maxlength="4800"></textarea>@error('inquiryMessage')<small class="sref-error">{{ $message }}</small>@enderror</label>
                        </div>
                        <div class="sref-dialog-actions">
                            <button type="button" class="sref-btn sref-btn--line" @click="$refs.enquiryDialog.close()">Cancel</button>
                            <button type="submit" class="sref-btn sref-btn--brand" wire:loading.attr="disabled" wire:target="submitInquiry"><span wire:loading.remove wire:target="submitInquiry">Send enquiry</span><span wire:loading wire:target="submitInquiry">Sending…</span></button>
                        </div>
                    </form>
                @else
                    <p class="sref-dialog-note">Verify your email address before sending an admission enquiry.</p>
                    <a class="sref-btn sref-btn--brand" href="{{ route('verification.notice') }}">Verify email</a>
                @endif
            @else
                <p class="sref-dialog-note">Sign in to send an admission enquiry. Your name and email will be shared with the school from your account.</p>
                <div class="sref-dialog-actions"><a class="sref-btn sref-btn--brand" href="{{ route('login') }}">Sign in</a><a class="sref-btn sref-btn--line" href="{{ route('register') }}">Create an account</a></div>
            @endauth
        </dialog>
    @endif

    <div x-cloak x-show="open" x-transition.opacity class="sref-lightbox" role="dialog" aria-modal="true" aria-label="School photo gallery" @keydown.escape.window="close()" @click.self="close()">
        <button type="button" class="sref-lightbox__close" @click="close()" aria-label="Close gallery">×</button>
        <button type="button" class="sref-lightbox__prev" @click="prev()" aria-label="Previous photo">‹</button>
        <template x-if="images.length"><figure><img :src="images[activeIndex].url" :alt="images[activeIndex].alt"><figcaption><span x-text="images[activeIndex].alt"></span><small><span x-text="activeIndex + 1"></span> / <span x-text="images.length"></span></small></figcaption></figure></template>
        <button type="button" class="sref-lightbox__next" @click="next()" aria-label="Next photo">›</button>
    </div>
</div>
