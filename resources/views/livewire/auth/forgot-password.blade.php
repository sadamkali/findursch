<section class="mx-auto max-w-md px-4 py-14 sm:py-20">
    <div class="rounded-2xl border border-gray-200 bg-white p-7 shadow-sm sm:p-9">
        <h1 class="text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Reset your password</h1>
        <p class="mt-2 text-sm text-gray-600">Enter the email address linked to your account. We will send a password reset link.</p>

        @if (session('status'))
            <div class="mt-5 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <form wire:submit="sendResetLink" class="mt-7 space-y-5">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email address</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full justify-center py-3">
                <span wire:loading.remove>Send reset link</span>
                <span wire:loading>Sending...</span>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-gray-600">
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Return to sign in</a>
        </p>
    </div>
</section>
