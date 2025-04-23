<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Level extends Component
{
    // Threshold for Level 1 -> 2
    private const BASE_LEVEL_UP_SCORE_THRESHOLD = 20; 
    // How many more points are needed for each subsequent level compared to the previous one
    private const LEVEL_UP_THRESHOLD_ADDITION = 10; 
    private const INITIAL_LEVEL = 1;

    public int $level = self::INITIAL_LEVEL;

    /**
     * Check if the current score triggers a level up.
     * Uses an increasing threshold for each level.
     * Launched by the "check-level" event.
     *
     * @param $score
     * @return void
     */
    #[On('check-level')]
    public function checkLevel($score): void
    {
        // Use a while loop to handle potentially multiple level-ups at once
        while (true) {
            $scoreNeededForNextLevel = $this->getTotalScoreRequiredForLevel($this->level + 1);
            
            if ($score >= $scoreNeededForNextLevel) {
                $this->level += 1;
                Log::debug("Level up! Reached level {$this->level} at score {$score}. Score needed: {$scoreNeededForNextLevel}");
                $this->dispatch('increase-ball-speed', level: $this->level)->to(Game::class);
            } else {
                // Current score is not enough for the next level, stop checking
                break; 
            }
        }
    }
    
    /**
     * Calculate the total score required to reach a specific target level.
     */
    private function getTotalScoreRequiredForLevel(int $targetLevel): int
    {
        if ($targetLevel <= self::INITIAL_LEVEL) {
            return 0; // No score required to reach the initial level
        }
        
        $totalScoreRequired = 0;
        // Sum the thresholds for all levels from INITIAL_LEVEL up to (targetLevel - 1)
        for ($level = self::INITIAL_LEVEL; $level < $targetLevel; $level++) {
            // Calculate the points needed to go from $level to $level + 1
            $thresholdForThisLevelUp = self::BASE_LEVEL_UP_SCORE_THRESHOLD + (self::LEVEL_UP_THRESHOLD_ADDITION * ($level - self::INITIAL_LEVEL));
            $totalScoreRequired += $thresholdForThisLevelUp;
        }
        
        return $totalScoreRequired;
    }

    /**
     * Reset level to initial value.
     * Listener for the 'reset-level' event from Game.
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
