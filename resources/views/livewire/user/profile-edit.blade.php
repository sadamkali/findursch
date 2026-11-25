<section class="parent-account-page parent-account-profile-page">
    <div class="parent-account-page__intro">
        <a href="{{ route('user.home') }}" class="parent-account-back-link">← Account overview</a>
        <p class="parent-account-eyebrow">YOUR DETAILS</p>
        <p class="parent-account-page__description">Keep your name and contact information up to date.</p>
    </div>

    @if(session('message'))
        <div class="parent-account-message" role="status">{{ session('message') }}</div>
    @endif

    <form wire:submit="save" class="parent-account-form">
        <div class="parent-account-form__heading">
            <h2>Personal details</h2>
            <p>These details help schools respond to your enquiries.</p>
        </div>

        <div class="parent-account-form__grid">
            <div class="parent-account-field">
                <label for="name">Full name</label>
                <input id="name" wire:model="name" autocomplete="name" required>
                @error('name')<p class="parent-account-field__error">{{ $message }}</p>@enderror
            </div>

            <div class="parent-account-field">
                <label for="email">Email address</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" required>
                @error('email')<p class="parent-account-field__error">{{ $message }}</p>@enderror
            </div>

            <div class="parent-account-field">
                <label for="phone">Phone number</label>
                <input id="phone" wire:model="phone" maxlength="30" autocomplete="tel">
                @error('phone')<p class="parent-account-field__error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="parent-account-form__footer">
            <button type="submit" wire:loading.attr="disabled" class="parent-account-button parent-account-button--brand">
                <span wire:loading.remove wire:target="save">Save changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</section>
