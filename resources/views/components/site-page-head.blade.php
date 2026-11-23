@props([
    'breadcrumbs' => [],
    'eyebrow' => null,
    'showAd' => true,
])

<section class="site-page-head">
    <div class="site-wrap">
        <div class="site-page-head__inner">
            <div class="site-page-head__copy">
                @if(!empty($breadcrumbs))
                    <nav class="site-page-head__breadcrumbs" aria-label="Breadcrumb">
                        @foreach($breadcrumbs as $item)
                            @if($loop->first)
                                @if(!empty($item['url']))
                                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                                @else
                                    <span>{{ $item['label'] }}</span>
                                @endif
                            @else
                                <span class="site-page-head__separator" aria-hidden="true">›</span>
                                @if(!empty($item['url']))
                                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                                @else
                                    <span aria-current="page">{{ $item['label'] }}</span>
                                @endif
                            @endif
                        @endforeach
                    </nav>
                @endif

                @if($eyebrow)
                    <p class="site-page-head__eyebrow">{{ $eyebrow }}</p>
                @endif

                @if(isset($title))
                    <h1>{{ $title }}</h1>
                @endif

                @if(isset($description))
                    <div class="site-page-head__description">{{ $description }}</div>
                @endif

                {{ $slot }}
            </div>

            @if($showAd)
                <aside class="site-page-head__ad" aria-label="Advertisement">
                    <x-site-ad-banner placement="top" />
                </aside>
            @endif
        </div>

        @if(isset($footer))
            <div class="site-page-head__footer">{{ $footer }}</div>
        @endif
    </div>
</section>
