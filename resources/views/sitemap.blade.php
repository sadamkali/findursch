<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($urls as $u)
<url>
    <loc>{{ $u['url'] }}</loc>
    <priority>{{ $u['priority'] }}</priority>
    <changefreq>weekly</changefreq>
</url>
@endforeach
</urlset>
