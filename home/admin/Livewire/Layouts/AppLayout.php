<?php

namespace App\Livewire\Layouts;

use Livewire\Component;

class AppLayout extends Component
{
    public function render()
    {
        return view('livewire.layouts.app-layout')
            ->layout('layouts.app');
    }
}
