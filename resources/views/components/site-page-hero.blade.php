@props(['title' => '', 'eyebrow' => null, 'description' => null, 'breadcrumbs' => [], 'meta' => null])

<section {{ $attributes->class(['site-page-hero']) }}>
    <div class="site-page-hero__inner">
        <div class="site-page-hero__copy">
            @if(count($breadcrumbs))
                <nav class="site-page-hero__breadcrumbs" aria-label="Breadcrumb">
                    @foreach($breadcrumbs as $index => $crumb)
                        @if($index > 0)<span aria-hidden="true">›</span>@endif
                        @if(!empty($crumb['url']))
                            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                        @else
                            <b aria-current="page">{{ $crumb['label'] }}</b>
                        @endif
                    @endforeach
                </nav>
            @endif
            @if($eyebrow)<p class="site-page-hero__eyebrow">{{ $eyebrow }}</p>@endif
            <h1>{{ $title }}</h1>
            @if($description)<p class="site-page-hero__description">{{ $description }}</p>@endif
            @if($meta)<p class="site-page-hero__meta"><span aria-hidden="true"></span>{{ $meta }}</p>@endif
            {{ $slot }}
        </div>
        <div class="site-page-hero__ad"><x-site-ad-banner placement="top" /></div>
    </div>
</section>
