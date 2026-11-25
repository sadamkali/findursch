@props([
    'school',
    'showSave' => true,
    'saved' => false,
    'showFee' => true,
    'showDescription' => true,
    'showActions' => true,
    'variant' => 'tile',
])

@php
    $coverUrl = $school->getFirstMediaUrl('cover');
    $locationText = collect([
        $school->town ?: $school->parish?->name,
        $school->subCounty?->name,
        $school->district?->name,
    ])->filter()->unique()->implode(', ');

    $isFeatured = (bool) $school->is_featured
        && (!$school->featured_until || $school->featured_until->isFuture());

    $feeFrom = is_numeric($school->fees_from) && (float) $school->fees_from > 0 ? (float) $school->fees_from : null;
    $feeTo = is_numeric($school->fees_to) && (float) $school->fees_to > 0 ? (float) $school->fees_to : null;
    $feeLabel = null;
    if ($feeFrom !== null && $feeTo !== null && $feeFrom < $feeTo) {
        $feeLabel = 'Estimated UGX '.number_format($feeFrom).' – UGX '.number_format($feeTo).' per term';
    } elseif ($showFee) {
        $feeLabel = 'Fee range not provided';
    }

    $description = $school->tagline
        ?: \Illuminate\Support\Str::limit(strip_tags((string) $school->description), 145, '…');

    $boardingLabel = match ($school->boarding_type) {
        'boarding' => 'Boarding',
        'mixed' => 'Day & boarding',
        'day' => 'Day',
        default => $school->boarding_type
            ? ucwords(str_replace(['_', '-'], ' ', $school->boarding_type))
            : null,
    };
@endphp

<article {{ $attributes->class(['findursch-school-tile', 'findursch-school-tile--'.$variant]) }}>
    <a class="findursch-school-tile__media" href="{{ route('schools.show', $school) }}" aria-label="View {{ $school->name }}">
        @if($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $school->name }} school photo" loading="lazy" decoding="async">
        @else
            <span class="findursch-school-tile__placeholder" aria-hidden="true">{{ strtoupper(mb_substr($school->name, 0, 2)) }}</span>
        @endif
        @if($isFeatured)
            <span class="findursch-school-tile__featured">Featured</span>
        @endif
    </a>

    <div class="findursch-school-tile__body">
        <h3 class="findursch-school-tile__title">
            <a href="{{ route('schools.show', $school) }}">{{ $school->name }}</a>
        </h3>

        @if($feeLabel && $showFee)
            <div class="findursch-school-tile__fee-line">{{ $feeLabel }}</div>
        @endif

        @if($showDescription && $description)
            <p class="findursch-school-tile__description">{{ $description }}</p>
        @endif

        <div class="findursch-school-tile__chips">
            <span class="is-type">{{ $school->type?->name ?? 'School' }}</span>
            @foreach($school->curricula->take(3) as $curriculum)
                <span>{{ $curriculum->abbreviation ?: $curriculum->name }}</span>
            @endforeach
            @if($boardingLabel)
                <span>{{ $boardingLabel }}</span>
            @endif
            @if($school->admissions_open)
                <span class="is-admissions-open">Admissions open</span>
            @endif
        </div>

        <div class="findursch-school-tile__meta">
            <span class="findursch-school-tile__location">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                <span>{{ $locationText ?: 'Uganda' }}</span>
            </span>

            @if((float) $school->rating_average > 0)
                <span class="findursch-school-tile__rating" title="Average school rating">
                    <span aria-hidden="true">★</span> {{ number_format((float) $school->rating_average, 1) }}
                </span>
            @else
                <span class="findursch-school-tile__no-rating">No reviews yet</span>
            @endif

            @if($showSave)
                @if(auth()->check())
                    <form method="POST" action="{{ route('school.save.toggle', $school) }}" class="findursch-school-tile__save-form">
                        @csrf
                        <button type="submit" class="{{ $saved ? 'is-saved' : '' }}" aria-label="{{ $saved ? 'Remove '.$school->name.' from saved schools' : 'Save '.$school->name }}" aria-pressed="{{ $saved ? 'true' : 'false' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4.5h12v16l-6-3.5-6 3.5z"/></svg>
                        </button>
                    </form>
                @else
                    <a class="findursch-school-tile__save" href="{{ route('login') }}" aria-label="Sign in to save {{ $school->name }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4.5h12v16l-6-3.5-6 3.5z"/></svg>
                    </a>
                @endif
            @endif
        </div>

        @if($showActions)
            <div class="findursch-school-tile__actions">
                <a href="{{ route('schools.show', $school) }}">View school</a>
            </div>
        @endif
    </div>
</article>
