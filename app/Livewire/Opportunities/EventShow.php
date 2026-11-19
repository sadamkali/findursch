<?php

namespace App\Livewire\Opportunities;

use App\Models\SchoolPost;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class EventShow extends Component
{
    public SchoolPost $event;

    public function mount(SchoolPost $post): void
    {
        $this->event = SchoolPost::query()
            ->whereKey($post->id)
            ->where('type', 'event')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['school.district.region'])
            ->firstOrFail();
    }

    public function render()
    {
        $similarEvents = SchoolPost::query()
            ->where('type', 'event')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereKeyNot($this->event->id)
            ->where(function ($query) {
                $query->where('school_id', $this->event->school_id)
                    ->orWhereDate('starts_at', '>=', now()->toDateString());
            })
            ->with('school')
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        return view('livewire.opportunities.event-show', compact('similarEvents'))
            ->layout('layouts.app', [
                'title' => $this->event->title.' — Events — FindURSCH',
                'description' => \Illuminate\Support\Str::limit(strip_tags($this->event->description), 155),
            ]);
    }
}
