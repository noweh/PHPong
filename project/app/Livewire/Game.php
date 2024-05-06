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

    public function pauseGame(): void
    {
        $this->gameStarted = false;
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

        $this->racketPosition = min(50, max(0, $this->racketPosition));
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

        if ($this->checkWallCollision()) {
            return;
        }

        if ($this->checkRacketCollision()) {
            return;
        }

        if ($this->checkRacketSideCollision()) {
            return;
        }


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
     * @return bool
     */
    private function checkWallCollision(): bool
    {
        if ($this->ballPosition['x'] >= 106) {
            $this->ballDirection['x'] *= -1;
            return true;
        }

        if ($this->ballPosition['y'] <= 0 ||
            $this->ballPosition['y'] >= 56
        ) {
            $this->ballDirection['y'] *= -1;
            return true;
        }

        return false;
    }

    /**
     * Check if the ball collides with the racket.
     *
     * @return bool
     */
    private function checkRacketCollision(): bool
    {
        if ($this->ballPosition['x'] === 1 &&
            $this->ballPosition['y'] >= $this->racketPosition -4 &&
            $this->ballPosition['y'] <= $this->racketPosition +4
        ) {
            // If the ball is not in the center of the racket, it will bounce off in the opposite direction.
            if ($this->ballPosition['y'] !== $this->racketPosition) {
                $this->ballDirection['y'] *= -1;
            }
            $this->ballDirection['x'] *= -1;
            $this->dispatch('increase-score');
            return true;
        }

        return false;
    }

    /**
     * Check if the ball collides with the racket side.
     *
     * @return bool
     */
    private function checkRacketSideCollision(): bool
    {
        if ($this->ballPosition['x'] === 0 &&
            $this->ballPosition['y'] >= $this->racketPosition -4 &&
            $this->ballPosition['y'] <= $this->racketPosition +7
        ) {
            $this->ballDirection['x'] *= -1;
            $this->ballDirection['y'] *= -1;
            $this->dispatch('increase-score');
            return true;
        }

        return false;
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.game');
    }
}
