<section class="parent-account-page parent-account-list-page">
    <div class="parent-account-page__intro">
        <a href="{{ route('user.home') }}" class="parent-account-back-link">← Account overview</a>
        <p class="parent-account-eyebrow">YOUR SHORTLIST</p>
        <p class="parent-account-page__description">Schools you have bookmarked to revisit as you compare your options.</p>
    </div>

    @if(session('message'))
        <div class="parent-account-message" role="status">{{ session('message') }}</div>
    @endif

    @if($schools->count())
        <div class="parent-account-school-grid">
            @foreach($schools as $school)
                <article class="parent-account-school-item">
                    @include('components.school-card', ['school' => $school])
                    <button type="button" wire:click="unsave({{ $school->id }})" class="parent-account-remove-link">
                        Remove from saved schools
                    </button>
                </article>
            @endforeach
        </div>
        <div class="parent-account-pagination">{{ $schools->links() }}</div>
    @else
        <div class="parent-account-empty parent-account-empty--page">
            <strong>You have not saved any schools yet</strong>
            <p>Browse the directory and save schools you would like to come back to.</p>
            <a href="{{ route('schools.index') }}">Explore schools</a>
        </div>
    @endif
</section>
