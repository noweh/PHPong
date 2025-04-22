<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Score extends Component
{
    private const SCORE_INCREMENT = 10;

    public int $score = 0;

    /**
     * Increase the score.
     * Launched by the "increase-score" event.
     *
     * @return void
     */
    #[On('increase-score')]
    public function increaseScore(): void
    {
        $this->score += self::SCORE_INCREMENT;
        $this->dispatch('check-level', score: $this->score)->to(Level::class);
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.score');
    }
}
