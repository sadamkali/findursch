<?php

namespace App\Livewire\Owner;

use App\Models\Review;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Reviews extends Component
{
    use WithPagination;

    public array $responses = [];

    public function respond(int $reviewId): void
    {
        $review = Review::query()
            ->whereKey($reviewId)
            ->where('status', 'approved')
            ->whereHas('school', fn ($query) => $query->where('owner_id', auth()->id()))
            ->firstOrFail();

        $validated = Validator::make([
            'response' => $this->responses[$reviewId] ?? '',
        ], [
            'response' => ['required', 'string', 'min:2', 'max:5000'],
        ])->validate();

        $review->forceFill([
            'owner_response' => $validated['response'],
            'owner_responded_at' => now(),
        ])->save();

        unset($this->responses[$reviewId]);

        session()->flash('message', 'Your response has been saved.');
    }

    public function render()
    {
        $reviews = Review::query()
            ->where('status', 'approved')
            ->whereHas('school', fn ($query) => $query->where('owner_id', auth()->id()))
            ->with(['school', 'user'])
            ->latest()
            ->paginate(15);

        return view('livewire.owner.reviews', compact('reviews'));
    }
}
