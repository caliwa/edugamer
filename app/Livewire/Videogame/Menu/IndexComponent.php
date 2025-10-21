<?php

namespace App\Livewire\Videogame\Menu;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Isolate;

#[Isolate]
#[Layout('components.layouts.blank')]
class IndexComponent extends Component
{
    public function advance()
    {
        return $this->redirect('/register', navigate: true);
    }
    public function render()
    {
        return view('livewire.videogame.menu.index-component');
    }
}
