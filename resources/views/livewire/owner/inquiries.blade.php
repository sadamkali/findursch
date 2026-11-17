<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-7">
        <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-primary hover:underline">← Back to your listings</a>
        <p class="mt-5 text-sm font-semibold uppercase tracking-wider text-primary">School owner area</p>
        <h1 class="mt-2 text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">School inquiries</h1>
        <p class="mt-2 text-sm text-gray-600">Review messages sent to the schools you manage and record your responses.</p>
    </div>

    @if (session('message'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('message') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        @forelse ($inquiries as $inquiry)
            <article class="border-b border-gray-100 p-5 last:border-b-0">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-gray-900">{{ $inquiry->parent_name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $inquiry->parent_email }} @if ($inquiry->parent_phone) · {{ $inquiry->parent_phone }} @endif</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $inquiry->school?->name }} · {{ $inquiry->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium capitalize text-gray-700">{{ $inquiry->status }}</span>
                </div>

                <div class="mt-4 rounded-lg bg-gray-50 p-4 text-sm leading-6 text-gray-700">
                    {{ $inquiry->message }}
                </div>

                @if ($inquiry->owner_response)
                    <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">Your recorded response</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-blue-950">{{ $inquiry->owner_response }}</p>
                    </div>
                @endif

                @if ($inquiry->status !== 'archived')
                    <div class="mt-4">
                        <label for="response-{{ $inquiry->id }}" class="mb-1.5 block text-sm font-medium text-gray-700">Response to the parent or guardian</label>
                        <textarea id="response-{{ $inquiry->id }}" wire:model="responses.{{ $inquiry->id }}" rows="3" maxlength="5000" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Write a clear response..."></textarea>
                        @error('response') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('responses.'.$inquiry->id) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" wire:click="respond({{ $inquiry->id }})" wire:loading.attr="disabled" class="btn-primary">Save response</button>
                            @if ($inquiry->status === 'new')
                                <button type="button" wire:click="markRead({{ $inquiry->id }})" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Mark as read</button>
                            @endif
                            <button type="button" wire:click="archive({{ $inquiry->id }})" wire:confirm="Archive this inquiry?" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Archive</button>
                        </div>
                    </div>
                @endif
            </article>
        @empty
            <div class="px-5 py-12 text-center text-sm text-gray-500">There are no inquiries for your schools yet.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $inquiries->links() }}</div>
</section>
