<?php
namespace App\Livewire\Home;

use App\Models\Article;
use App\Models\District;
use App\Models\School;
use App\Models\SchoolType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class HomePage extends Component
{
    public function render()
    {
        $schoolTypes = SchoolType::where('is_active', true)
            ->orderBy('order')
            ->get();

        $featuredSchools = School::active()
            ->verified()
            ->featured()
            ->with(['district', 'type', 'curricula'])
            ->limit(6)
            ->get();

        $districts = District::with('region')
            ->inRandomOrder()
            ->limit(8)
            ->get();

        $articles = Article::where('status', 'published')
            ->where('published_at', '<=', now())
            ->where('is_featured', true)
            ->with('category')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $stats = [
            'schools' => School::active()->verified()->count(),
            'districts' => District::count(),
            'parents' => \App\Models\User::where('user_type', 'parent')->count(),
            'reviews' => \App\Models\Review::where('status', 'approved')->count(),
        ];

        return view('livewire.home.home-page', compact(
            'schoolTypes', 'featuredSchools', 'districts', 'articles', 'stats'
        ));
    }
}
