<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Level extends Component
{
    private const LEVEL_UP_SCORE_THRESHOLD = 20;
    private const INITIAL_LEVEL = 1;

    public int $level = self::INITIAL_LEVEL;

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
        if ($score > 0 && $score % self::LEVEL_UP_SCORE_THRESHOLD === 0) {
            $this->level += 1;
            $this->dispatch('increase-ball-speed', level: $this->level)->to(Game::class);
        }
    }

    /**
     * Reset level to initial value.
     * Listener pour l'événement 'reset-level' venant de Game.
     */
    #[On('reset-level')]
    public function resetLevel(): void
    {
        $this->level = self::INITIAL_LEVEL;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.level');
    }
}
