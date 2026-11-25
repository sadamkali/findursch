<?php

namespace App\Livewire\Home;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class HomePage extends Component
{
    public function render()
    {
        return view('livewire.home.home-page')
            ->layout('layouts.app', [
                'title' => 'FindURSCH — Find the right school in Uganda',
                'description' => 'Find the right school for your child. Explore schools, compare options, education news, events, videos, scholarships, jobs and insights across Uganda.',
            ]);
    }
}
