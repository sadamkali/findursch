<section class="mx-auto max-w-xl px-4 py-14 sm:py-20">
    <div class="rounded-2xl border border-gray-200 bg-white p-7 shadow-sm sm:p-9">
        <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-primary">Join FindURSCH</p>
        <h1 class="text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Create your account</h1>
        <p class="mt-2 text-sm text-gray-600">Create an account to save schools, send inquiries and manage your activity.</p>

        <form wire:submit="register" class="mt-7 space-y-5">
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Full name</label>
                <input id="name" type="text" wire:model="name" autocomplete="name" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email address</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="account_type" class="mb-1.5 block text-sm font-medium text-gray-700">I am joining as</label>
                <select id="account_type" wire:model="account_type" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="parent">Parent / Guardian</option>
                    <option value="school_owner">School representative</option>
                </select>
                @error('account_type') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-gray-500">Administrator access is granted separately and cannot be selected here.</p>
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" wire:model="password" autocomplete="new-password" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-gray-500">Use at least 8 characters.</p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700">Confirm password</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full justify-center py-3">
                <span wire:loading.remove>Create account</span>
                <span wire:loading>Creating account...</span>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-gray-600">
            Already registered?
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Sign in</a>
        </p>
    </div>
</section>
