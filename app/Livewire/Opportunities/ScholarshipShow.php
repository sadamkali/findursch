<?php

namespace App\Livewire\Opportunities;

use App\Models\SchoolPost;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ScholarshipShow extends Component
{
    public SchoolPost $scholarship;

    public function mount(SchoolPost $post): void
    {
        $this->scholarship = SchoolPost::query()
            ->whereKey($post->id)
            ->where('type', 'bursary')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['school.district.region'])
            ->firstOrFail();
    }

    public function render()
    {
        $related = SchoolPost::query()
            ->where('type', 'bursary')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereKeyNot($this->scholarship->id)
            ->with('school')
            ->orderBy('deadline')
            ->limit(3)
            ->get();

        return view('livewire.opportunities.scholarship-show', compact('related'))
            ->layout('layouts.app', [
                'title' => $this->scholarship->title.' — Scholarships — FindURSCH',
                'description' => \Illuminate\Support\Str::limit(strip_tags($this->scholarship->description), 155),
            ]);
    }
}
