<section class="parent-account-page parent-account-list-page">
    <div class="parent-account-page__intro">
        <a href="{{ route('user.home') }}" class="parent-account-back-link">← Account overview</a>
        <p class="parent-account-eyebrow">YOUR SCHOOL CONVERSATIONS</p>
        <p class="parent-account-page__description">Follow the questions you have sent and read replies from schools.</p>
    </div>

    <div class="parent-account-record-list">
        @forelse($inquiries as $inquiry)
            <article class="parent-account-record">
                <div class="parent-account-record__heading">
                    <div>
                        <h2>{{ $inquiry->school?->name ?? 'School listing' }}</h2>
                        <p>Sent {{ $inquiry->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="parent-account-status">{{ str($inquiry->status)->replace('_', ' ')->title() }}</span>
                </div>
                <p class="parent-account-record__message">{{ $inquiry->message }}</p>
                @if($inquiry->owner_response)
                    <div class="parent-account-response">
                        <p class="parent-account-eyebrow">SCHOOL RESPONSE</p>
                        <p>{{ $inquiry->owner_response }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="parent-account-empty parent-account-empty--page">
                <strong>You have not sent any enquiries</strong>
                <p>Contact a school to ask about available places, fees or arranging a visit.</p>
                <a href="{{ route('schools.index') }}">Find schools</a>
            </div>
        @endforelse
    </div>

    @if($inquiries->hasPages())
        <div class="parent-account-pagination">{{ $inquiries->links() }}</div>
    @endif
</section>
