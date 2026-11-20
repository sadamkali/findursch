<?php

namespace App\Livewire\Opportunities;

use App\Models\School;
use App\Models\SchoolPost;
use Illuminate\Database\Eloquent\Builder;
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
            ->with(['school.district.region', 'school.media'])
            ->firstOrFail();
    }

    public function render()
    {
        $base = SchoolPost::query()
            ->where('type', 'event')
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        $similarEvents = (clone $base)
            ->where('id', '!=', $this->event->id)
            ->where(function (Builder $query) {
                $query->where('school_id', $this->event->school_id)
                    ->orWhere('starts_at', '>=', now());
            })
            ->with(['school.district', 'school.media'])
            ->orderByRaw('CASE WHEN starts_at >= ? THEN 0 ELSE 1 END', [now()])
            ->orderBy('starts_at')
            ->limit(4)
            ->get();

        $upcomingEvents = (clone $base)
            ->where('id', '!=', $this->event->id)
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now())
            ->with('school')
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        $spotlightSchool = School::query()
            ->publiclyVisible()
            ->featured()
            ->with('district')
            ->first();

        return view('livewire.opportunities.event-show', compact(
            'similarEvents',
            'upcomingEvents',
            'spotlightSchool',
        ))->layout('layouts.app', [
            'title' => $this->event->title.' — Events — FindURSCH',
            'description' => \Illuminate\Support\Str::limit(strip_tags($this->event->description), 155),
        ]);
    }
}
