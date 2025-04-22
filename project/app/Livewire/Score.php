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
            Log::debug('[Score::increaseScore] Blocked by showFinalMessage = true');
            return;
        }
        Log::debug('[Score::increaseScore] Incrementing score.');
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
        // Optionnel : Réinitialiser le score ici aussi ?
        // Pour l'instant, on garde le score visible jusqu'au prochain point marqué.
        // Si on veut reset à 0 dès le début du nouveau jeu, décommenter :
        // $this->score = 0;
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.score');
    }
}
