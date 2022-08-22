<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Home extends Component
{
    public object $user; 

    public function render()
    {
        $this->user = Auth::user();

        return view('livewire.home');
    }
}
