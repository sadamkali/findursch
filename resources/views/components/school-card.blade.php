@props(['school'])

<article data-reveal class="school-card group overflow-hidden rounded-card border border-gray-100 bg-white">
    <a href="{{ route('schools.show', $school) }}" class="block">
        <div class="school-card__media relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-primary-50 to-accent-50">
            @if($school->getFirstMediaUrl('cover'))
                <img src="{{ $school->getFirstMediaUrl('cover') }}" alt="{{ $school->name }}" class="h-full w-full object-cover" loading="lazy" />
            @else
                <div class="flex h-full w-full items-center justify-center">
                    <span class="text-3xl font-bold text-primary-200" style="font-family: 'DM Serif Display', serif;">
                        {{ strtoupper(mb_substr($school->name, 0, 2)) }}
                    </span>
                </div>
            @endif

            <span class="absolute bottom-3 left-3 flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border-2 border-white bg-white p-1 shadow-md" aria-label="{{ $school->name }} school badge">
                @if($school->getFirstMediaUrl('badge') || $school->getFirstMediaUrl('logo'))
                    <img src="{{ $school->getFirstMediaUrl('badge') ?: $school->getFirstMediaUrl('logo') }}" alt="{{ $school->name }} badge" class="h-full w-full object-contain" loading="lazy">
                @else
                    <span class="px-0.5 text-center text-[9px] font-bold uppercase tracking-wide text-primary">Badge</span>
                @endif
            </span>
            @if($school->is_featured)
                <span class="absolute right-3 top-3 rounded-badge border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700">Featured</span>
            @endif
        </div>
        <div class="p-5">
            <div class="mb-2 flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-primary-700">{{ $school->type?->name }}</span>
                @if($school->verification_status === 'verified')
                    <span class="rounded-badge border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-bold text-green-700">✓ Verified</span>
                @endif
            </div>
            <h3 class="mb-1 line-clamp-1 text-lg font-semibold transition group-hover:text-primary">{{ $school->name }}</h3>
            @if($school->tagline)
                <p class="mb-3 line-clamp-2 text-sm text-gray-600">{{ $school->tagline }}</p>
            @endif
            <div class="mb-3 flex items-center text-sm text-gray-500">
                <svg class="mr-1 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>{{ $school->district?->name }}</span>
            </div>
            <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                <div class="flex items-center">
                    @if($school->rating_average > 0)
                        <span class="mr-1 text-amber-500">★</span>
                        <span class="text-sm font-semibold">{{ number_format($school->rating_average, 1) }}</span>
                        <span class="ml-1 text-xs text-gray-400">({{ $school->reviews_count }})</span>
                    @else
                        <span class="text-sm text-gray-400">No reviews yet</span>
                    @endif
                </div>
                @if($school->curricula->count() > 0)
                    <div class="flex flex-wrap gap-1">
                        @foreach($school->curricula->take(2) as $cur)
                            <span class="rounded-badge bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700">{{ $cur->abbreviation }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </a>
    <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
        <span class="text-xs text-gray-500">Compare this school</span>
        <button
            type="button"
            x-data
            @click.stop.prevent="window.dispatchEvent(new CustomEvent('findursch:compare-add', { detail: @js($school->slug) }))"
            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-primary hover:text-primary"
        >Add to compare</button>
    </div>
</article>
