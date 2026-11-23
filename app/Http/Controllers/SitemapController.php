<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\District;
use App\Models\School;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];
        $urls[] = ['url' => route('home'), 'priority' => '1.0'];
        $urls[] = ['url' => route('schools.index'), 'priority' => '0.9'];
        $urls[] = ['url' => route('articles.index'), 'priority' => '0.8'];
        $urls[] = ['url' => route('jobs.index'), 'priority' => '0.8'];

        foreach (School::publiclyVisible()->get() as $s) {
            $urls[] = ['url' => route('schools.show', $s), 'priority' => '0.7'];
        }
        foreach (District::all() as $d) {
            $urls[] = ['url' => route('district.show', $d), 'priority' => '0.6'];
        }
        foreach (JobListing::query()->publiclyVisible()->get() as $job) {
            $urls[] = ['url' => route('jobs.show', $job->slug), 'priority' => '0.6'];
        }

        foreach (Article::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get() as $a) {
            $urls[] = ['url' => route('articles.show', $a), 'priority' => '0.5'];
        }

        return response()->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}
