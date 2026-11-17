<section class="mx-auto max-w-md px-4 py-14 sm:py-20">
    <div class="rounded-2xl border border-gray-200 bg-white p-7 shadow-sm sm:p-9">
        <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-primary">FindURSCH account</p>
        <h1 class="text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Welcome back</h1>
        <p class="mt-2 text-sm text-gray-600">Sign in to continue to your school discovery account.</p>

        @if (session('status'))
            <div class="mt-5 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <form wire:submit="authenticate" class="mt-7 space-y-5">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email address</label>
                <input id="email" type="email" wire:model="email" autocomplete="username" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary hover:underline">Forgot password?</a>
                </div>
                <input id="password" type="password" wire:model="password" autocomplete="current-password" required
                    class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-primary focus:ring-primary">
                Keep me signed in
            </label>

            <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full justify-center py-3">
                <span wire:loading.remove>Sign in</span>
                <span wire:loading>Signing in...</span>
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">Create one</a>
        </p>
    </div>
</section>
