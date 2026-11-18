@php
    $coverMedia = $school->getFirstMedia('cover');
    $galleryMedia = $school->getMedia('gallery');
    $profileImages = collect([$coverMedia])
        ->filter()
        ->concat($galleryMedia)
        ->unique('id')
        ->values();
    $lightboxImages = $profileImages->map(fn ($media) => [
        'url' => $media->getUrl(),
        'alt' => $school->name.' school photograph',
    ])->values()->all();

    $primaryWhatsapp = $school->whatsapp
        ?: $school->contacts->firstWhere('type', 'whatsapp')?->value;
    $whatsappDigits = preg_replace('/\D+/', '', (string) $primaryWhatsapp);

    $locationText = collect([
        $school->subCounty?->name,
        $school->district?->name,
        $school->district?->region?->name,
    ])->filter()->unique()->implode(', ');

    $mapQuery = filled($school->latitude) && filled($school->longitude)
        ? $school->latitude.','.$school->longitude
        : trim(collect([$school->address, $locationText])->filter()->implode(', '));
    $mapUrl = $mapQuery !== ''
        ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapQuery)
        : null;

    $socialLinks = $school->contacts->whereIn('type', ['facebook', 'twitter', 'instagram']);
    $activeFees = $school->fees->where('is_active', true);
    $tuitionFees = $activeFees->where('category', 'tuition');
@endphp

<div class="school-profile-page" x-data="lightbox" x-init="images = @js($lightboxImages)">
    {{-- School photo mosaic --}}
    <section class="profile-mosaic {{ $profileImages->count() > 1 ? 'has-side' : 'is-single' }}" aria-label="School photographs">
        @if($profileImages->isNotEmpty())
            <button type="button" class="profile-mosaic__main" @click="show(images, 0)" aria-label="Open school photograph gallery">
                <img src="{{ $profileImages->first()->getUrl() }}" alt="{{ $school->name }} school" fetchpriority="high">
                <span class="profile-mosaic__chip">View all {{ $profileImages->count() }} {{ \Illuminate\Support\Str::plural('photo', $profileImages->count()) }}</span>
            </button>
            @if($profileImages->count() > 1)
                <div class="profile-mosaic__side">
                    @foreach($profileImages->skip(1)->take(3) as $media)
                        <button type="button" class="profile-mosaic__tile" @click="show(images, {{ $loop->index + 1 }})" aria-label="Open school photograph {{ $loop->iteration + 1 }}">
                            <img src="{{ $media->getUrl() }}" alt="{{ $school->name }} school photograph {{ $loop->iteration + 1 }}" loading="lazy">
                            @if($loop->last && $profileImages->count() > 4)
                                <span class="profile-mosaic__more">+{{ $profileImages->count() - 4 }} more</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        @else
            <div class="profile-mosaic__placeholder">
                <span>{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                <small>School photographs have not been added yet</small>
            </div>
        @endif
    </section>

    <nav class="profile-crumbs" aria-label="Breadcrumb">
        <div class="site-wrap">
            <a href="{{ route('home') }}">Home</a><span aria-hidden="true">›</span>
            <a href="{{ route('schools.index') }}">Schools</a><span aria-hidden="true">›</span>
            @if($school->type)<a href="{{ route('schools.index', ['type' => $school->type->id]) }}">{{ $school->type->name }}</a><span aria-hidden="true">›</span>@endif
            <span aria-current="page">{{ $school->name }}</span>
        </div>
    </nav>

    <main class="site-wrap profile-page-wrap">
        <div class="profile-layout">
            <div class="profile-main">
                {{-- School identity and primary actions --}}
                <section class="profile-identity">
                    <div class="profile-identity__top">
                        <div class="profile-logo">
                            @if($school->getFirstMediaUrl('logo'))
                                <img src="{{ $school->getFirstMediaUrl('logo') }}" alt="{{ $school->name }} logo">
                            @else
                                <span>{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="profile-identity__copy">
                            <div class="profile-tags">
                                @if($school->type)<span class="profile-tag profile-tag--type">{{ $school->type->name }}</span>@endif
                                @if($school->is_demo)
                                    <span class="profile-tag profile-tag--sample">Demo sample · Not verified</span>
                                @else
                                    <span class="profile-tag profile-tag--verified">✓ Verified listing</span>
                                @endif
                                @if($school->is_featured)<span class="profile-tag profile-tag--featured">Featured</span>@endif
                                @foreach($school->curricula->take(2) as $curriculum)
                                    <span class="profile-tag">{{ $curriculum->abbreviation }}</span>
                                @endforeach
                            </div>
                            <h1>{{ $school->name }}</h1>
                            @if($school->tagline)<p class="profile-tagline">{{ $school->tagline }}</p>@endif
                            <div class="profile-contact-line">
                                @if($school->phone)<a href="tel:{{ $school->phone }}">☎ {{ $school->phone }}</a>@endif
                                @if($school->email)<a href="mailto:{{ $school->email }}">✉ {{ $school->email }}</a>@endif
                                @if($locationText !== '')<span>⌖ {{ $locationText }}</span>@endif
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        @if($school->is_demo)
                            <span class="profile-action-note">Sample profile — admission enquiries and reviews are disabled.</span>
                        @else
                            <button type="button" class="btn btn-brand" @click="$refs.admissionDialog.showModal()">Apply for admission</button>
                            @if($whatsappDigits !== '')
                                <a class="btn btn-wa" href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">WhatsApp</a>
                            @endif
                        @endif
                        <button type="button" class="btn btn-line" x-data="{copied:false}" @click="navigator.clipboard.writeText(window.location.href).then(() => copied = true)" x-text="copied ? 'Link copied' : 'Share school'">Share school</button>
                        @auth
                            @if(!$school->is_demo)
                                <button type="button" wire:click="toggleSave" class="btn btn-line">{{ $this->isSaved() ? '♥ Saved' : '♡ Save school' }}</button>
                            @endif
                        @else
                            @if(!$school->is_demo)<a class="btn btn-line" href="{{ route('login') }}">♡ Save school</a>@endif
                        @endauth
                    </div>
                </section>

                {{-- In-page profile navigation --}}
                <nav class="profile-tabs" aria-label="School profile sections">
                    <a class="is-active" href="#about">About</a>
                    <a href="#programmes">Programmes</a>
                    <a href="#facilities">Facilities</a>
                    <a href="#fees">Fees</a>
                    <a href="#gallery">Gallery</a>
                    <a href="#events">Events</a>
                    <a href="#news">News</a>
                    <a href="#reviews">Reviews</a>
                </nav>

                {{-- About --}}
                <section class="profile-card profile-pad profile-section" id="about">
                    <h2>About <span>{{ $school->name }}</span></h2>
                    @if($school->tagline)
                        <div class="profile-quote">{{ $school->tagline }}</div>
                    @endif
                    @if($school->description)
                        <div class="profile-prose">{{ $school->description }}</div>
                    @else
                        <p class="profile-empty-inline">This school has not added an about description yet.</p>
                    @endif
                    <div class="profile-about-facts">
                        @if($school->founded_year)
                            <div><small>Established</small><strong>{{ $school->founded_year }}</strong></div>
                        @endif
                        @if($school->student_count_approx)
                            <div><small>Approximate learners</small><strong>{{ number_format($school->student_count_approx) }}</strong></div>
                        @endif
                        @if($school->registration_number)
                            <div><small>Registration number</small><strong>{{ $school->registration_number }}</strong></div>
                        @endif
                        <div><small>Boarding arrangement</small><strong>{{ match($school->boarding_type) { 'boarding' => 'Boarding', 'mixed' => 'Day and boarding', default => 'Day school' } }}</strong></div>
                        <div><small>Gender</small><strong>{{ match($school->gender_policy) { 'boys' => 'Boys', 'girls' => 'Girls', default => 'Mixed' } }}</strong></div>
                    </div>
                </section>

                {{-- Curriculum --}}
                <section class="profile-card profile-pad profile-section" id="curriculum">
                    <h2>Learning <span>curriculum</span></h2>
                    @if($school->curricula->isNotEmpty())
                        <div class="profile-chip-list">
                            @foreach($school->curricula as $curriculum)
                                <span class="profile-chip">{{ $curriculum->name }} <b>{{ $curriculum->abbreviation }}</b></span>
                            @endforeach
                        </div>
                    @else
                        <p class="profile-empty-inline">Curriculum information has not been added yet.</p>
                    @endif
                </section>

                {{-- Programmes --}}
                <section class="profile-card profile-pad profile-section" id="programmes">
                    <h2>School <span>programmes</span></h2>
                    @if($school->programs->isNotEmpty())
                        <div class="profile-programme-grid">
                            @foreach($school->programs as $program)
                                <article class="profile-programme">
                                    <h3>{{ $program->name }}</h3>
                                    <p class="profile-programme__meta">
                                        @if($program->level){{ $program->level }}@endif
                                        @if($program->duration) · {{ $program->duration }}@endif
                                    </p>
                                    @if($program->curriculum)<p class="profile-programme__curriculum">{{ $program->curriculum->name }}</p>@endif
                                    @if($program->description)<p>{{ $program->description }}</p>@endif
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="profile-empty-inline">This school has not published its programmes yet.</p>
                    @endif
                </section>

                {{-- Facilities --}}
                <section class="profile-card profile-pad profile-section" id="facilities">
                    <h2>Our <span>facilities</span></h2>
                    @if($school->facilities->isNotEmpty())
                        <div class="profile-facilities">
                            @foreach($school->facilities as $facility)
                                <span><i aria-hidden="true"></i>{{ $facility->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="profile-empty-inline">Facility information has not been added yet.</p>
                    @endif
                </section>

                {{-- School fees --}}
                <section class="profile-card profile-pad profile-section" id="fees">
                    <h2>School <span>fees</span></h2>
                    @if($activeFees->isNotEmpty())
                        <p class="profile-section-intro">Fee information is supplied by the school. Confirm the current fee structure directly before making an admission decision.</p>
                        <div class="profile-table-wrap">
                            <table class="profile-fee-table">
                                <thead><tr><th>Description</th><th>Period</th><th class="text-right">Amount</th></tr></thead>
                                <tbody>
                                    @foreach($activeFees as $fee)
                                        <tr>
                                            <td><strong>{{ $fee->label }}</strong><small>{{ ucfirst($fee->category) }}@if($fee->program) · {{ $fee->program->name }}@endif</small></td>
                                            <td>{{ str_replace('_', ' ', $fee->period) }}</td>
                                            <td class="text-right"><strong>{{ $fee->currency }} {{ number_format((float) $fee->amount) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="profile-fee-contact"><strong>Contact the school</strong><span>for its current fee structure.</span></div>
                        <p class="profile-section-intro">No active fee schedule has been published on this profile.</p>
                    @endif
                </section>

                {{-- Photo gallery --}}
                <section class="profile-card profile-pad profile-section" id="gallery">
                    <div class="profile-section-heading">
                        <h2>Photo <span>gallery</span></h2>
                        @if($profileImages->isNotEmpty())<span>{{ $profileImages->count() }} {{ \Illuminate\Support\Str::plural('photo', $profileImages->count()) }}</span>@endif
                    </div>
                    @if($profileImages->isNotEmpty())
                        <div class="profile-gallery-grid">
                            @foreach($profileImages as $media)
                                <button type="button" @class(['profile-gallery-image', 'profile-gallery-image--big' => $loop->first]) @click="show(images, {{ $loop->index }})" aria-label="View photograph {{ $loop->iteration }}">
                                    <img src="{{ $media->getUrl() }}" alt="{{ $school->name }} photograph {{ $loop->iteration }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @else
                        <p class="profile-empty-inline">This school has not uploaded gallery photographs yet.</p>
                    @endif
                </section>

                {{-- Events have no school-event data model in the current application --}}
                <section class="profile-card profile-pad profile-section" id="events">
                    <h2>Upcoming and past <span>events</span></h2>
                    <div class="profile-empty-state">
                        <span class="profile-empty-state__icon">▦</span>
                        <strong>No school events posted yet</strong>
                        <p>Events will appear here when this school publishes them on FindurSch.</p>
                    </div>
                </section>

                {{-- School-specific news is kept separate from general education articles --}}
                <section class="profile-card profile-pad profile-section" id="news">
                    <h2>School <span>news</span></h2>
                    <div class="profile-empty-state">
                        <span class="profile-empty-state__icon">▤</span>
                        <strong>No school news posted yet</strong>
                        <p>Updates published specifically for this school will appear here.</p>
                        <a href="{{ route('articles.index') }}" class="profile-text-link">Browse education news and guides →</a>
                    </div>
                </section>

                {{-- Approved reviews --}}
                <section class="profile-card profile-pad profile-section" id="reviews">
                    <div class="profile-section-heading">
                        <h2>Parent and student <span>reviews</span></h2>
                        <span>{{ number_format($school->reviews_count) }} approved</span>
                    </div>
                    @if((float) $school->rating_average > 0)
                        <div class="profile-rating-summary"><strong>★ {{ number_format((float) $school->rating_average, 1) }}</strong><span>out of 5 based on {{ number_format($school->reviews_count) }} approved reviews</span></div>
                    @endif

                    @forelse($school->approvedReviews as $review)
                        <article class="profile-review">
                            <div class="profile-review__top">
                                <div><h3>{{ $review->title }}</h3><small>{{ $review->user?->name ?? 'FindurSch user' }} · {{ ucfirst($review->reviewer_type) }}</small></div>
                                <strong>★ {{ $review->rating }}/5</strong>
                            </div>
                            <p>{{ $review->body }}</p>
                            @if($review->owner_response)
                                <div class="profile-owner-response"><strong>School response</strong><p>{{ $review->owner_response }}</p></div>
                            @endif
                        </article>
                    @empty
                        <p class="profile-empty-inline">No approved reviews have been published yet.</p>
                    @endforelse
                </section>

                {{-- Review submission --}}
                @if($school->is_demo)
                    <section class="profile-card profile-pad"><h2>Reviews are disabled for <span>sample profiles</span></h2><p class="profile-empty-inline">Reviews are available on eligible real school listings.</p></section>
                @else
                    <section class="profile-card profile-pad profile-section" id="write-review">
                        <h2>Share your <span>experience</span></h2>
                        @if(session('review_submitted'))<p class="profile-success-message">{{ session('review_submitted') }}</p>@endif
                        @if($alreadyReviewed)
                            <p class="profile-empty-inline">You have already submitted a review for this school.</p>
                        @elseif(auth()->check() && auth()->user()->hasVerifiedEmail())
                            <form wire:submit="submitReview" class="profile-form">
                                <div class="profile-form-grid">
                                    <label>I am a
                                        <select wire:model="reviewerType">
                                            <option value="parent">Parent / Guardian</option><option value="student">Student</option><option value="alumni">Alumni</option><option value="staff">Staff</option><option value="other">Other</option>
                                        </select>
                                    </label>
                                    <label>Rating
                                        <select wire:model="reviewRating">@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} out of 5</option>@endfor</select>
                                    </label>
                                    <label class="profile-form-full">Review title
                                        <input wire:model="reviewTitle" required maxlength="255">
                                        @error('reviewTitle')<small class="profile-field-error">{{ $message }}</small>@enderror
                                    </label>
                                    <label class="profile-form-full">Your experience
                                        <textarea wire:model="reviewBody" required minlength="20" maxlength="5000" rows="5"></textarea>
                                        @error('reviewBody')<small class="profile-field-error">{{ $message }}</small>@enderror
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-brand" wire:loading.attr="disabled" wire:target="submitReview">Submit review</button>
                            </form>
                        @elseif(auth()->check())
                            <p class="profile-empty-inline">Verify your email address before submitting a review. <a href="{{ route('verification.notice') }}" class="profile-text-link">Verify email →</a></p>
                        @else
                            <p class="profile-empty-inline"><a href="{{ route('login') }}" class="profile-text-link">Sign in</a> to leave a review.</p>
                        @endif
                    </section>
                @endif
            </div>

            {{-- Profile sidebar --}}
            <aside class="profile-sidebar">
                @if($school->is_demo)
                    <section class="profile-join profile-join--sample">
                        <h2>Sample school profile</h2>
                        <p>This is demonstration data. Admission enquiries, reviews and direct school actions are disabled.</p>
                        <span class="profile-sample-pill">For preview only</span>
                    </section>
                @else
                    <section class="profile-join">
                        <small>Admissions</small>
                        <h2>Ready to join {{ $school->name }}?</h2>
                        <p>Send an admission enquiry to ask the school about availability, requirements and the next intake.</p>
                        <button type="button" class="btn btn-gold btn-block" @click="$refs.admissionDialog.showModal()">Apply for admission</button>
                        <button type="button" class="btn btn-ghost btn-block" @click="$refs.admissionDialog.showModal()">Send an enquiry</button>
                    </section>
                @endif

                <section class="profile-contact-card">
                    <h2>Contact the school</h2>
                    @if($school->phone)
                        <div class="profile-contact-item"><i>☎</i><div><small>Phone</small><a href="tel:{{ $school->phone }}">{{ $school->phone }}</a></div></div>
                    @endif
                    @if($school->email)
                        <div class="profile-contact-item"><i>✉</i><div><small>Email</small><a href="mailto:{{ $school->email }}">{{ $school->email }}</a></div></div>
                    @endif
                    @if($school->website)
                        <div class="profile-contact-item"><i>↗</i><div><small>Website</small><a href="{{ \Illuminate\Support\Str::startsWith($school->website, ['http://', 'https://']) ? $school->website : 'https://'.$school->website }}" target="_blank" rel="noopener">{{ $school->website }}</a></div></div>
                    @endif
                    @if($primaryWhatsapp && $whatsappDigits !== '')
                        <div class="profile-contact-item"><i>◉</i><div><small>WhatsApp</small><a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">{{ $primaryWhatsapp }}</a></div></div>
                    @endif
                    @if($school->address)
                        <div class="profile-contact-address">{{ $school->address }}</div>
                    @endif
                    @if($school->is_demo)
                        <p class="profile-sidebar-note">Contact details are sample data and are not active.</p>
                    @elseif(!$school->phone && !$school->email && !$school->website && !$primaryWhatsapp)
                        <p class="profile-sidebar-note">Contact information has not been provided yet.</p>
                    @endif
                </section>

                <section class="profile-facts-card">
                    <h2>Quick facts</h2>
                    <div class="profile-facts-grid">
                        <div><small>School type</small><b>{{ $school->type?->name ?? 'Not provided' }}</b></div>
                        <div><small>Gender</small><b>{{ match($school->gender_policy) { 'boys' => 'Boys', 'girls' => 'Girls', default => 'Mixed' } }}</b></div>
                        <div><small>Boarding</small><b>{{ match($school->boarding_type) { 'boarding' => 'Boarding', 'mixed' => 'Day & boarding', default => 'Day' } }}</b></div>
                        <div><small>Curriculum</small><b>{{ $school->curricula->pluck('abbreviation')->filter()->implode(', ') ?: 'Not provided' }}</b></div>
                        @if($school->founded_year)<div><small>Established</small><b>{{ $school->founded_year }}</b></div>@endif
                        @if($school->student_count_approx)<div><small>Learners</small><b>{{ number_format($school->student_count_approx) }}</b></div>@endif
                    </div>
                    @if($tuitionFees->isNotEmpty())
                        <div class="profile-fee-highlight"><strong>{{ $tuitionFees->sortBy('amount')->first()->currency }} {{ number_format((float) $tuitionFees->min('amount')) }}</strong><small>Lowest listed tuition amount · {{ str_replace('_', ' ', $tuitionFees->sortBy('amount')->first()->period) }}</small></div>
                    @endif
                </section>

                <section class="profile-contact-card">
                    <h2>Find the school</h2>
                    <dl class="profile-location-list">
                        @if($school->district?->region)<div><dt>Region</dt><dd>{{ $school->district->region->name }}</dd></div>@endif
                        @if($school->district)<div><dt>District</dt><dd>{{ $school->district->name }}</dd></div>@endif
                        @if($school->subCounty)<div><dt>Sub-county</dt><dd>{{ $school->subCounty->name }}</dd></div>@endif
                        @if($school->address)<div><dt>Address</dt><dd>{{ $school->address }}</dd></div>@endif
                    </dl>
                    @if($mapUrl)
                        <a class="profile-map-link" href="{{ $mapUrl }}" target="_blank" rel="noopener"><span>⌖</span><span><strong>View on Google Maps</strong><small>Open directions to this school</small></span></a>
                    @else
                        <p class="profile-sidebar-note">A map location has not been added to this profile.</p>
                    @endif
                </section>

                @if($socialLinks->isNotEmpty())
                    <section class="profile-contact-card">
                        <h2>Find us on social media</h2>
                        <div class="profile-social-links">
                            @foreach($socialLinks as $social)
                                <a href="{{ $social->value }}" target="_blank" rel="noopener">{{ $social->label ?: ucfirst($social->type) }}</a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="profile-listing-note">
                    <h2>About this listing</h2>
                    @if($school->is_demo)
                        <p>This is a fictional demonstration profile created to preview FindurSch. It is not a real school, is not verified and has no real admissions information.</p>
                    @else
                        <p>This profile is marked as verified by FindurSch. School details and fees can change, so confirm current information directly with the school.</p>
                        @if($school->verified_at)<small>Verified {{ $school->verified_at->format('d M Y') }}</small>@endif
                    @endif
                </section>
            </aside>
        </div>
    </main>

    {{-- Admission enquiry dialog keeps the existing verified-account workflow --}}
    @if(!$school->is_demo)
        <dialog x-ref="admissionDialog" class="profile-enquiry-dialog" aria-labelledby="profile-enquiry-title">
            <div class="profile-dialog-heading">
                <div><small>Admissions enquiry</small><h2 id="profile-enquiry-title">Contact {{ $school->name }}</h2></div>
                <button type="button" @click="$refs.admissionDialog.close()" aria-label="Close enquiry form">×</button>
            </div>
            @if(session('inquiry_sent'))<p class="profile-success-message">{{ session('inquiry_sent') }}</p>@endif

            @auth
                @if(auth()->user()->hasVerifiedEmail())
                    <p class="profile-dialog-intro">Your name and email will be shared with the school using your FindurSch account.</p>
                    <div class="profile-account-summary"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></div>
                    <form wire:submit="submitInquiry" class="profile-form">
                        <div class="profile-form-grid">
                            <label>Phone number
                                <input type="tel" wire:model="inquiryPhone" maxlength="30" placeholder="+256…">
                                @error('inquiryPhone')<small class="profile-field-error">{{ $message }}</small>@enderror
                            </label>
                            <label>Student name
                                <input wire:model="inquiryStudentName" maxlength="255" placeholder="Optional">
                                @error('inquiryStudentName')<small class="profile-field-error">{{ $message }}</small>@enderror
                            </label>
                            <label>Student age
                                <input type="number" min="1" max="100" wire:model="inquiryStudentAge" placeholder="Optional">
                                @error('inquiryStudentAge')<small class="profile-field-error">{{ $message }}</small>@enderror
                            </label>
                            <label>Class or level applying for
                                <input wire:model="inquiryLevel" maxlength="120" placeholder="e.g. Primary 1">
                                @error('inquiryLevel')<small class="profile-field-error">{{ $message }}</small>@enderror
                            </label>
                            <label class="profile-form-full">Preferred intake
                                <select wire:model="inquiryIntake">
                                    <option value="">Not sure yet</option>
                                    <option value="Next available intake">Next available intake</option>
                                    <option value="Term 1">Term 1</option>
                                    <option value="Term 2">Term 2</option>
                                    <option value="Term 3">Term 3</option>
                                </select>
                                @error('inquiryIntake')<small class="profile-field-error">{{ $message }}</small>@enderror
                            </label>
                            <label class="profile-form-full">Message
                                <textarea wire:model="inquiryMessage" required minlength="10" maxlength="4800" rows="4" placeholder="Tell the school what you would like to know…"></textarea>
                                @error('inquiryMessage')<small class="profile-field-error">{{ $message }}</small>@enderror
                            </label>
                        </div>
                        <div class="profile-dialog-actions">
                            <button type="button" class="btn btn-line" @click="$refs.admissionDialog.close()">Cancel</button>
                            <button type="submit" class="btn btn-brand" wire:loading.attr="disabled" wire:target="submitInquiry">
                                <span wire:loading.remove wire:target="submitInquiry">Send enquiry</span>
                                <span wire:loading wire:target="submitInquiry">Sending…</span>
                            </button>
                        </div>
                    </form>
                @else
                    <p class="profile-dialog-intro">Verify your email address before sending an admission enquiry.</p>
                    <a class="btn btn-brand btn-block" href="{{ route('verification.notice') }}">Verify email address</a>
                @endif
            @else
                <p class="profile-dialog-intro">Sign in to send an admission enquiry. Your contact details will be shared with the school from your FindurSch account.</p>
                <div class="profile-dialog-actions"><a class="btn btn-brand" href="{{ route('login') }}">Sign in</a><a class="btn btn-line" href="{{ route('register') }}">Create an account</a></div>
            @endauth
        </dialog>
    @endif

    {{-- Accessible photo lightbox --}}
    <div x-cloak x-show="open" x-transition.opacity class="profile-lightbox" role="dialog" aria-modal="true" aria-label="School photo gallery" @keydown.escape.window="close()" @click.self="close()">
        <button type="button" class="profile-lightbox__close" @click="close()" aria-label="Close gallery">×</button>
        <button type="button" class="profile-lightbox__prev" @click="prev()" aria-label="Previous photograph">‹</button>
        <template x-if="images.length">
            <figure class="profile-lightbox__figure">
                <img :src="images[activeIndex].url" :alt="images[activeIndex].alt">
                <figcaption><span x-text="images[activeIndex].alt"></span><small><span x-text="activeIndex + 1"></span> / <span x-text="images.length"></span></small></figcaption>
            </figure>
        </template>
        <button type="button" class="profile-lightbox__next" @click="next()" aria-label="Next photograph">›</button>
    </div>
</div>
