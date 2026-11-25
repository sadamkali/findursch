@php
    $user = auth()->user();
    $nameParts = preg_split('/\s+/', trim((string) $user->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $firstName = $nameParts[0] ?? 'there';
    $initials = mb_strtoupper(
        mb_substr($nameParts[0] ?? 'U', 0, 1)
        . (count($nameParts) > 1 ? mb_substr($nameParts[count($nameParts) - 1], 0, 1) : '')
    );
@endphp

<section class="parent-account-page parent-account-overview">
    <div class="parent-account-welcome">
        <div class="parent-account-welcome__avatar" aria-hidden="true">{{ $initials }}</div>
        <div class="parent-account-welcome__copy">
            <p class="parent-account-eyebrow">PARENT ACCOUNT</p>
            <h2>Welcome back, {{ $firstName }}!</h2>
            <p>Your saved schools, enquiries and reviews in one place.</p>
            <div class="parent-account-welcome__meta">
                <span>{{ number_format($savedCount) }} saved {{ \Illuminate\Support\Str::plural('school', $savedCount) }}</span>
                <span>{{ number_format($inquiryCount) }} {{ \Illuminate\Support\Str::plural('enquiry', $inquiryCount) }}</span>
                <span>{{ number_format($reviewCount) }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
            </div>
        </div>
        <div class="parent-account-welcome__actions">
            <a class="parent-account-button parent-account-button--gold" href="{{ route('schools.index') }}">Find schools</a>
            <a class="parent-account-button parent-account-button--outline" href="{{ route('user.profile') }}">Edit profile</a>
        </div>
    </div>

    <div class="parent-account-stats" aria-label="Account summary">
        <a class="parent-account-stat" href="{{ route('user.saved-schools') }}">
            <span class="parent-account-stat__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M6 4.5A1.5 1.5 0 0 1 7.5 3h9A1.5 1.5 0 0 1 18 4.5V21l-6-4-6 4V4.5Z"/></svg>
            </span>
            <span class="parent-account-stat__copy">
                <strong>{{ number_format($savedCount) }}</strong>
                <span>Saved schools</span>
            </span>
            <span class="parent-account-stat__arrow" aria-hidden="true">↗</span>
        </a>

        <a class="parent-account-stat" href="{{ route('user.inquiries') }}">
            <span class="parent-account-stat__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 4h16v12h-5l-2 3h-2l-2-3H4V4Z"/><path d="M8 9h8M8 12h5"/></svg>
            </span>
            <span class="parent-account-stat__copy">
                <strong>{{ number_format($inquiryCount) }}</strong>
                <span>Enquiries sent</span>
            </span>
            <span class="parent-account-stat__arrow" aria-hidden="true">↗</span>
        </a>

        <a class="parent-account-stat" href="{{ route('user.reviews') }}">
            <span class="parent-account-stat__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
            </span>
            <span class="parent-account-stat__copy">
                <strong>{{ number_format($reviewCount) }}</strong>
                <span>Reviews written</span>
            </span>
            <span class="parent-account-stat__arrow" aria-hidden="true">↗</span>
        </a>
    </div>

    <div class="parent-account-overview-grid">
        <section class="parent-account-panel">
            <div class="parent-account-panel__heading">
                <div>
                    <p class="parent-account-eyebrow">YOUR ACTIVITY</p>
                    <h3>Recent enquiries</h3>
                </div>
                <a href="{{ route('user.inquiries') }}">View all</a>
            </div>

            @forelse($inquiries as $inquiry)
                <article class="parent-account-activity">
                    <span class="parent-account-activity__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 4h16v12h-5l-2 3h-2l-2-3H4V4Z"/></svg>
                    </span>
                    <div class="parent-account-activity__copy">
                        <strong>{{ $inquiry->school?->name ?? 'School listing' }}</strong>
                        <span>Sent {{ $inquiry->created_at?->format('d M Y') }}</span>
                        @if($inquiry->owner_response)
                            <small>There is a response from this school.</small>
                        @endif
                    </div>
                    <span class="parent-account-status">{{ str($inquiry->status)->replace('_', ' ')->title() }}</span>
                </article>
            @empty
                <div class="parent-account-empty">
                    <strong>No enquiries yet</strong>
                    <p>Contact a school to ask about places, fees or arranging a visit.</p>
                    <a href="{{ route('schools.index') }}">Explore schools</a>
                </div>
            @endforelse
        </section>

        <section class="parent-account-panel">
            <div class="parent-account-panel__heading">
                <div>
                    <p class="parent-account-eyebrow">YOUR FEEDBACK</p>
                    <h3>Recent reviews</h3>
                </div>
                <a href="{{ route('user.reviews') }}">View all</a>
            </div>

            @forelse($reviews as $review)
                <article class="parent-account-activity parent-account-activity--review">
                    <span class="parent-account-activity__icon parent-account-activity__icon--gold" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
                    </span>
                    <div class="parent-account-activity__copy">
                        <strong>{{ $review->school?->name ?? 'School listing' }}</strong>
                        <span class="parent-account-review-rating" aria-label="{{ $review->rating }} out of 5 stars">
                            <span aria-hidden="true">★</span> {{ $review->rating }}/5
                        </span>
                        <small>Submitted {{ $review->created_at?->format('d M Y') }}</small>
                    </div>
                    <span class="parent-account-status">{{ str($review->status)->replace('_', ' ')->title() }}</span>
                </article>
            @empty
                <div class="parent-account-empty">
                    <strong>No reviews yet</strong>
                    <p>Your feedback can help other families learn about a school.</p>
                    <a href="{{ route('user.inquiries') }}">View your enquiries</a>
                </div>
            @endforelse
        </section>
    </div>

    <section class="parent-account-panel parent-account-saved">
        <div class="parent-account-panel__heading">
            <div>
                <p class="parent-account-eyebrow">YOUR SHORTLIST</p>
                <h3>Saved schools</h3>
                <p class="parent-account-panel__description">Keep the schools you are considering close at hand.</p>
            </div>
            <a href="{{ route('user.saved-schools') }}">View all saved schools</a>
        </div>

        @if($saved->isNotEmpty())
            <div class="parent-account-saved__list">
                @foreach($saved as $school)
                    <x-school-tile-card
                        :school="$school"
                        variant="compact"
                        :show-save="false"
                        :show-compare="false"
                        :show-fee="false"
                        :show-description="false"
                        :show-actions="false"
                    />
                @endforeach
            </div>
        @else
            <div class="parent-account-empty parent-account-empty--saved">
                <strong>Your saved schools will appear here</strong>
                <p>Browse schools and save the ones you would like to revisit.</p>
                <a href="{{ route('schools.index') }}">Find a school</a>
            </div>
        @endif
    </section>
</section>
