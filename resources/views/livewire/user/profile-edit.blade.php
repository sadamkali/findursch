<section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8"><a href="{{ route('user.dashboard') }}" class="text-sm font-medium text-primary hover:underline">← Account overview</a><h1 class="mt-5 text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Profile settings</h1><p class="mt-2 text-sm text-gray-600">Keep your contact details up to date.</p></div>
    @if(session('message'))<div class="mb-5 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('message') }}</div>@endif
    <form wire:submit="save" class="space-y-5 rounded-xl border border-gray-200 bg-white p-6">
        <div><label for="name" class="mb-1 block text-sm font-medium text-gray-700">Full name</label><input id="name" wire:model="name" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email address</label><input id="email" type="email" wire:model="email" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="phone" class="mb-1 block text-sm font-medium text-gray-700">Phone number</label><input id="phone" wire:model="phone" maxlength="30" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <button type="submit" wire:loading.attr="disabled" class="btn-primary">Save changes</button>
    </form>
</section>
