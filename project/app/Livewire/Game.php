<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Game extends Component
{
    public bool $gameStarted = false;
    public int $racketPosition = 24;
    public array $ballPosition = ['x' => 55, 'y' => 28];
    public array $ballDirection = ['x' => 1, 'y' => 1];

    public int $ballSpeed = 1;

    /**
     * Start the game.
     * Launched by the "keydown" event.
     *
     * @return void
     */
    public function startGame(): void
    {
        $this->gameStarted = true;
    }

    /**
     * Move the racket up or down.
     * Launched by the "keydown" event.
     *
     * @param $direction
     * @return void
     */
    #[On('move-racket')]
    public function moveRacket($direction): void
    {
        switch ($direction) {
            case 'up':
                $this->racketPosition -= 1;
                break;
            case 'down':
                $this->racketPosition += 1;
                break;
            case 'up-fast':
                $this->racketPosition -= 3;
                break;
            case 'down-fast':
                $this->racketPosition += 3;
                break;
        }

        $this->racketPosition = min(48, max(0, $this->racketPosition));
    }

    /**
     * Increase the ball speed.
     * Launched by the "check-level" event.
     *
     * @param $level
     * @return void
     */
    #[On('increase-ball-speed')]
    public function increaseBallSpeed($level): void
    {
        $attenuationFactor = 10;
        // The ball speed is increased by 1 every 10 levels.
        $this->ballSpeed = 1 + ($level / $attenuationFactor);
    }

    /**
     * Move the ball.
     * Launched by the "game-loop" event.
     *
     * @return void
     */
    public function moveBall(): void
    {
        $this->updateBallPosition();
        $this->checkWallCollision();
        $this->checkRacketCollision();
        $this->checkRacketSideCollision();
    }

    /**
     * Update the ball position.
     *
     * @return void
     */
    private function updateBallPosition(): void
    {
        $this->ballPosition['x'] += $this->ballDirection['x'] * $this->ballSpeed;
        $this->ballPosition['y'] += $this->ballDirection['y'] * $this->ballSpeed;
    }

    /**
     * Check if the ball collides with the wall.
     *
     * @return void
     */
    private function checkWallCollision(): void
    {
        if ($this->ballPosition['x'] >= 106) {
            $this->ballDirection['x'] *= -1;
        }

        if ($this->ballPosition['y'] <= 0 ||
            $this->ballPosition['y'] >= 56
        ) {
            $this->ballDirection['y'] *= -1;
        }
    }

    /**
     * Check if the ball collides with the racket.
     *
     * @return void
     */
    private function checkRacketCollision(): void
    {
        if ($this->ballPosition['x'] === 1 &&
            $this->ballPosition['y'] >= $this->racketPosition -2 &&
            $this->ballPosition['y'] <= $this->racketPosition +2
        ) {
            $this->ballDirection['x'] *= -1;
            $this->dispatch('increase-score');
        }
    }

    /**
     * Check if the ball collides with the racket side.
     *
     * @return void
     */
    private function checkRacketSideCollision(): void
    {
        if ($this->ballPosition['x'] === 0 &&
            !($this->ballPosition['y'] >= $this->racketPosition -1 &&
            $this->ballPosition['y'] <= $this->racketPosition +1)
        ) {
            $this->ballDirection['x'] *= -1;
            $this->ballDirection['y'] *= -1;
            $this->dispatch('increase-score');
        }
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.game');
    }
}
