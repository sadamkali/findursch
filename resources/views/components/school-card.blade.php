@props(['school'])

<a href="{{ route('schools.show', $school) }}" class="group block bg-white rounded-card border border-gray-100 overflow-hidden hover:shadow-card-hover transition">
    <div class="aspect-[16/10] bg-gradient-to-br from-primary-50 to-accent-50 relative">
        @if($school->getFirstMediaUrl('cover'))
            <img src="{{ $school->getFirstMediaUrl('cover') }}" alt="{{ $school->name }}" class="w-full h-full object-cover" />
        @else
            <div class="w-full h-full flex items-center justify-center">
                <span class="text-3xl font-bold text-primary-200" style="font-family: 'DM Serif Display', serif;">
                    {{ strtoupper(mb_substr($school->name, 0, 2)) }}
                </span>
            </div>
        @endif

        @if($school->is_featured)
            <span class="absolute top-3 right-3 bg-amber-50 text-amber-700 border border-amber-200 rounded-badge px-2.5 py-0.5 text-xs font-bold">Featured</span>
        @endif
    </div>
    <div class="p-5">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-semibold text-primary-700 uppercase tracking-wider">{{ $school->type?->name }}</span>
            @if($school->verification_status === 'verified')
                <span class="bg-green-50 text-green-700 border border-green-200 rounded-badge px-2 py-0.5 text-xs font-bold">✓ Verified</span>
            @endif
        </div>
        <h3 class="font-semibold text-lg mb-1 group-hover:text-primary transition line-clamp-1">{{ $school->name }}</h3>
        @if($school->tagline)
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $school->tagline }}</p>
        @endif
        <div class="flex items-center text-sm text-gray-500 mb-3">
            <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>{{ $school->district?->name }}</span>
        </div>
        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <div class="flex items-center">
                @if($school->rating_average > 0)
                    <span class="text-amber-500 mr-1">★</span>
                    <span class="font-semibold text-sm">{{ number_format($school->rating_average, 1) }}</span>
                    <span class="text-gray-400 text-xs ml-1">({{ $school->reviews_count }})</span>
                @else
                    <span class="text-gray-400 text-sm">No reviews yet</span>
                @endif
            </div>
            @if($school->curricula->count() > 0)
                <div class="flex flex-wrap gap-1">
                    @foreach($school->curricula->take(2) as $cur)
                        <span class="bg-primary-50 text-primary-700 rounded-badge px-2 py-0.5 text-xs font-semibold">{{ $cur->abbreviation }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</a>
