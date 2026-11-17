<section class="mx-auto max-w-md px-4 py-14 sm:py-20">
    <div class="rounded-2xl border border-gray-200 bg-white p-7 shadow-sm sm:p-9">
        <h1 class="text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Choose a new password</h1>
        <p class="mt-2 text-sm text-gray-600">Set a new password for your FindURSCH account.</p>

        <form wire:submit="resetPassword" class="mt-7 space-y-5">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email address</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">New password</label>
                <input id="password" type="password" wire:model="password" autocomplete="new-password" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700">Confirm new password</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full justify-center py-3">
                <span wire:loading.remove>Update password</span>
                <span wire:loading>Updating...</span>
            </button>
        </form>
    </div>
</section>
