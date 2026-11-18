<?php

namespace App\Livewire\Owner;

use App\Models\Inquiry;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Inquiries extends Component
{
    use WithPagination;

    public array $responses = [];

    public function markRead(int $inquiryId): void
    {
        $inquiry = $this->ownedInquiry($inquiryId);

        if ($inquiry->status === 'new') {
            $inquiry->update(['status' => 'read']);
        }
    }

    public function respond(int $inquiryId): void
    {
        $inquiry = $this->ownedInquiry($inquiryId);

        $validated = Validator::make([
            'responses' => [
                $inquiryId => $this->responses[$inquiryId] ?? '',
            ],
        ], [
            'responses.'.$inquiryId => ['required', 'string', 'min:2', 'max:5000'],
        ])->validate();

        $inquiry->forceFill([
            'owner_response' => $validated['responses'][$inquiryId],
            'responded_at' => now(),
            'responded_by' => auth()->id(),
            'status' => 'responded',
        ])->save();

        unset($this->responses[$inquiryId]);

        session()->flash('message', 'Your response has been saved.');
    }

    public function archive(int $inquiryId): void
    {
        $inquiry = $this->ownedInquiry($inquiryId);
        $inquiry->update(['status' => 'archived']);

        session()->flash('message', 'Inquiry archived.');
    }

    private function ownedInquiry(int $inquiryId): Inquiry
    {
        return Inquiry::query()
            ->whereKey($inquiryId)
            ->whereHas('school', fn ($query) => $query->where('owner_id', auth()->id()))
            ->firstOrFail();
    }

    public function render()
    {
        $inquiries = Inquiry::query()
            ->whereHas('school', fn ($query) => $query->where('owner_id', auth()->id()))
            ->with(['school', 'user'])
            ->latest()
            ->paginate(15);

        return view('livewire.owner.inquiries', compact('inquiries'));
    }
}
