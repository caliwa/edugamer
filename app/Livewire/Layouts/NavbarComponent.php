<?php

namespace App\Livewire\Layouts;

use Livewire\Component;
use Livewire\Attributes\Isolate;

#[Isolate]
class NavbarComponent extends Component
{
    public function render()
    {
        return view('livewire.layouts.navbar-component');
    }
}
