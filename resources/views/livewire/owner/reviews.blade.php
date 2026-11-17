<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-7">
        <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-primary hover:underline">← Back to your listings</a>
        <p class="mt-5 text-sm font-semibold uppercase tracking-wider text-primary">School owner area</p>
        <h1 class="mt-2 text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">School reviews</h1>
        <p class="mt-2 text-sm text-gray-600">Read approved feedback for your schools and add a professional response.</p>
    </div>

    @if (session('message'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('message') }}</div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        @forelse ($reviews as $review)
            <article class="border-b border-gray-100 p-5 last:border-b-0">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-gray-900">{{ $review->title }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $review->school?->name }} · {{ $review->user?->name ?? 'FindurSch user' }}</p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-800">★ {{ $review->rating }}/5</span>
                </div>
                <p class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $review->body }}</p>

                @if ($review->owner_response)
                    <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">Your response</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-blue-950">{{ $review->owner_response }}</p>
                    </div>
                @endif

                <div class="mt-4">
                    <label for="response-{{ $review->id }}" class="mb-1.5 block text-sm font-medium text-gray-700">Respond to this review</label>
                    <textarea id="response-{{ $review->id }}" wire:model="responses.{{ $review->id }}" rows="3" maxlength="5000" class="w-full rounded-lg border border-gray-300 px-3.5 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Thank the reviewer or clarify the school's response..."></textarea>
                    @error('responses.'.$review->id) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <button type="button" wire:click="respond({{ $review->id }})" wire:loading.attr="disabled" class="btn-primary mt-3">Save response</button>
                </div>
            </article>
        @empty
            <div class="px-5 py-12 text-center text-sm text-gray-500">There are no approved reviews for your schools yet.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $reviews->links() }}</div>
</section>
