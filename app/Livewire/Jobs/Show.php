<?php

namespace App\Livewire\Jobs;

use App\Models\JobListing;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public JobListing $job;

    public function mount(JobListing $job): void
    {
        abort_unless(
            in_array($job->status, ['published', 'closed'], true)
                && $job->published_at
                && $job->published_at->lessThanOrEqualTo(now()),
            404
        );

        $this->job = $job;
    }

    public function render()
    {
        $relatedJobs = JobListing::query()
            ->publiclyVisible()
            ->where('id', '!=', $this->job->id)
            ->where(function (Builder $query) {
                $query->where('category', $this->job->category)
                    ->orWhere('location', $this->job->location);
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('livewire.jobs.show', compact('relatedJobs'))->layout('layouts.app', [
            'title' => $this->job->title.' — '.$this->job->employer.' | FindURSCH Jobs',
            'description' => $this->job->excerpt ?: 'View this education job opportunity from '.$this->job->employer.' on FindURSCH.',
        ]);
    }
}
