<?php

namespace App\Livewire\Opportunities;

use App\Models\SchoolPost;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SchoolPostShow extends Component
{
    public SchoolPost $post;

    public function mount(SchoolPost $post): void
    {
        $this->post = SchoolPost::query()
            ->whereKey($post->id)
            ->where('type', 'news')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['school.district.region'])
            ->firstOrFail();
    }

    public function render()
    {
        $related = SchoolPost::query()
            ->where('type', 'news')
            ->where('school_id', $this->post->school_id)
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $this->post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('livewire.opportunities.school-post-show', compact('related'))
            ->layout('layouts.app', [
                'title' => $this->post->title.' — '.$this->post->school?->name.' — FindURSCH',
                'description' => \Illuminate\Support\Str::limit(strip_tags($this->post->description), 155),
            ]);
    }
}
