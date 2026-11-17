<div>
    <article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-gray-500"><a href="{{ route('articles.index') }}" class="hover:text-primary">Articles</a><span class="mx-2">/</span><span>{{ $article->category?->name ?? 'Education' }}</span></nav>
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $article->category?->name ?? 'Education' }}</p>
        <h1 class="mt-3 text-3xl font-semibold leading-tight text-gray-900 sm:text-5xl" style="font-family: 'DM Serif Display', serif;">{{ $article->title }}</h1>
        @if($article->excerpt)<p class="mt-5 text-lg leading-8 text-gray-600">{{ $article->excerpt }}</p>@endif
        <div class="mt-5 flex flex-wrap gap-3 text-xs text-gray-500"><span>{{ $article->author?->name ?? 'FindurSch Editorial' }}</span><span>·</span><span>{{ $article->published_at?->format('d M Y') }}</span><span>·</span><span>{{ $article->read_time_minutes }} min read</span></div>
        @if($article->cover_image)<img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="mt-8 max-h-[520px] w-full rounded-xl object-cover">@endif
        <div class="prose prose-gray mt-9 max-w-none prose-headings:font-semibold prose-a:text-primary">{!! $article->body !!}</div>
    </article>
    @if($related->isNotEmpty())<section class="border-t border-gray-100 bg-gray-50 py-12"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><h2 class="text-2xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Related articles</h2><div class="mt-6 grid gap-5 md:grid-cols-3">@foreach($related as $item)<a href="{{ route('articles.show', $item) }}" class="rounded-xl border border-gray-200 bg-white p-5 hover:border-primary"><p class="text-xs font-semibold uppercase text-primary">{{ $item->category?->name }}</p><h3 class="mt-2 font-semibold text-gray-900">{{ $item->title }}</h3><p class="mt-2 text-sm text-gray-500">{{ $item->published_at?->format('d M Y') }}</p></a>@endforeach</div></div></section>@endif
</div>
