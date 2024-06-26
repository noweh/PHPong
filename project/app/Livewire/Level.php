<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Level extends Component
{
    public int $level = 1;

    /**
     * Check the level.
     * Launched by the "check-level" event.
     *
     * @param $score
     * @return void
     */
    #[On('check-level')]
    public function checkLevel($score): void
    {
        if ($score % 20 === 0) {
            $this->level += 1;
            $this->dispatch('increase-ball-speed', level: $this->level)->to(Game::class);
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.level');
    }
}
