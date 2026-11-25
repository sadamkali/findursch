<section class="parent-account-page parent-account-list-page">
    <div class="parent-account-page__intro">
        <a href="{{ route('user.home') }}" class="parent-account-back-link">← Account overview</a>
        <p class="parent-account-eyebrow">YOUR FEEDBACK</p>
        <p class="parent-account-page__description">Read the reviews you have submitted and check their publication status.</p>
    </div>

    <div class="parent-account-record-list">
        @forelse($reviews as $review)
            <article class="parent-account-record">
                <div class="parent-account-record__heading">
                    <div>
                        <h2>{{ $review->school?->name ?? 'School listing' }}</h2>
                        <p>Submitted {{ $review->created_at?->format('d M Y') }}</p>
                    </div>
                    <span class="parent-account-status">{{ str($review->status)->replace('_', ' ')->title() }}</span>
                </div>
                <div class="parent-account-review-title">
                    <span aria-label="{{ $review->rating }} out of 5 stars">★ {{ $review->rating }}/5</span>
                    @if($review->title)<strong>{{ $review->title }}</strong>@endif
                </div>
                <p class="parent-account-record__message">{{ $review->body }}</p>
                @if($review->owner_response)
                    <div class="parent-account-response">
                        <p class="parent-account-eyebrow">SCHOOL RESPONSE</p>
                        <p>{{ $review->owner_response }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="parent-account-empty parent-account-empty--page">
                <strong>You have not submitted any reviews</strong>
                <p>Your feedback can help other families understand a school's experience.</p>
                <a href="{{ route('user.inquiries') }}">View your enquiries</a>
            </div>
        @endforelse
    </div>

    @if($reviews->hasPages())
        <div class="parent-account-pagination">{{ $reviews->links() }}</div>
    @endif
</section>
