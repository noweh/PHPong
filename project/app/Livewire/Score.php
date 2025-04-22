<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Score extends Component
{
    private const SCORE_INCREMENT = 10;

    public int $score = 0;
    public bool $showFinalMessage = false;

    /**
     * Increase the score.
     * Launched by the "increase-score" event.
     *
     * @return void
     */
    #[On('increase-score')]
    public function increaseScore(): void
    {
        if ($this->showFinalMessage) {
            return;
        }
        $this->score += self::SCORE_INCREMENT;
        $this->dispatch('check-level', score: $this->score)->to(Level::class);
    }

    /**
     * Listener pour l'événement 'show-final-score' venant de Game.
     */
    #[On('show-final-score')]
    public function handleFinalScore(): void
    {
        Log::debug('[Score::handleFinalScore] Setting showFinalMessage = true');
        $this->showFinalMessage = true;
    }

    /**
     * Listener pour l'événement 'reset-score-display' venant de Game.
     */
    #[On('reset-score-display')]
    public function resetDisplay(): void
    {
        Log::debug('[Score::resetDisplay] Setting showFinalMessage = false');
        $this->showFinalMessage = false;
        $this->score = 0;
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.score');
    }
}
