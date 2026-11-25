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
            ->with([
                'school.type',
                'school.district.region',
                'school.county',
                'school.subCounty',
                'school.parish',
                'school.media',
            ])
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
            ->with(['school.media', 'school.district'])
            ->latest('published_at')
            ->limit(4)
            ->get();

        $relatedHeading = 'More from this school';

        if ($related->isEmpty()) {
            $related = SchoolPost::query()
                ->where('type', 'news')
                ->where('school_id', '!=', $this->post->school_id)
                ->whereIn('status', ['approved', 'published'])
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('school', fn ($query) => $query->publiclyVisible())
                ->with(['school.media', 'school.district'])
                ->latest('published_at')
                ->limit(3)
                ->get();

            $relatedHeading = 'Latest school news';
        }

        $wordCount = str_word_count(strip_tags((string) $this->post->description));
        $readingMinutes = max(1, (int) ceil($wordCount / 220));

        return view('livewire.opportunities.school-post-show', compact(
            'related',
            'relatedHeading',
            'readingMinutes',
        ))->layout('layouts.app', [
            'title' => $this->post->title.' — '.$this->post->school?->name.' — FindURSCH',
            'description' => \Illuminate\Support\Str::limit(strip_tags($this->post->description), 155),
        ]);
    }
}
