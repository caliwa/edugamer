<?php

namespace App\Livewire\Layouts;

use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\Isolate;

#[Isolate]
class SidebarComponent extends Component
{

    public function logout(){
        Flux::toast(
            heading: 'Equisol',
            text: 'Has salido de la plataforma',
            variant: 'danger');


        auth()->logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        $this->redirectRoute('login', navigate:true);
    }

    public function render()
    {
        return view('livewire.layouts.sidebar-component');
    }
}
