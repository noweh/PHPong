<?php

namespace App\Livewire;

use App\Livewire\Score; // Importer Score
use App\Livewire\Level; // Importer Level
use Livewire\Attributes\On;
use Livewire\Component;

class Game extends Component
{
    // --- Constants ---
    // Boundaries (Based on original values)
    private const WALL_RIGHT_X = 106;
    private const WALL_BOTTOM_Y = 56;
    private const WALL_TOP_Y = 0;
    private const RACKET_COLLISION_X = 1;
    private const RACKET_SIDE_COLLISION_X = 0;

    // Racket (Based on original values)
    private const RACKET_INITIAL_Y = 24;
    private const RACKET_MOVE_STEP = 1;
    private const RACKET_MOVE_STEP_FAST = 3;
    private const RACKET_MIN_Y = 1;
    private const RACKET_MAX_Y = 50;
    private const RACKET_COLLISION_MARGIN_TOP = -4; // Relative to racket center? No, seems absolute offset
    private const RACKET_COLLISION_MARGIN_BOTTOM = 4;
    private const RACKET_SIDE_COLLISION_MARGIN_BOTTOM = 7;

    // Ball (Based on original values)
    private const BALL_INITIAL_X = 55;
    private const BALL_INITIAL_Y = 28;
    private const BALL_INITIAL_SPEED = 1;
    private const BALL_INITIAL_DIR_X = 1;
    private const BALL_INITIAL_DIR_Y = 1;
    private const BALL_SPEED_LEVEL_FACTOR = 10;

    // Game State
    private const GAME_OVER_X = 0; // Ball passes this X coordinate = Game Over
    // --- End Constants ---

    // --- Properties ---
    public bool $gameStarted = false;
    public bool $isGameOver = false; // << NOUVELLE PROPRIÉTÉ
    public int $racketPosition = self::RACKET_INITIAL_Y;
    public array $ballPosition = ['x' => self::BALL_INITIAL_X, 'y' => self::BALL_INITIAL_Y];
    public array $ballDirection = ['x' => self::BALL_INITIAL_DIR_X, 'y' => self::BALL_INITIAL_DIR_Y];

    public int $ballSpeed = self::BALL_INITIAL_SPEED;

    /**
     * Start the game.
     * Launched by the "keydown" event.
     *
     * @return void
     */
    public function startGame(): void
    {
        $this->gameStarted = true;
        $this->isGameOver = false; // << RÉINITIALISER GAME OVER
        // Optional: Reset ball position/direction if needed
        // $this->resetBall();

        // Informer Score de réinitialiser son affichage et son score
        $this->dispatch('reset-score-display')->to(Score::class);
        // Informer Level de réinitialiser son niveau
        $this->dispatch('reset-level')->to(Level::class);
    }

    public function pauseGame(): void
    {
        // Only allow pausing if game is running and not over
        if ($this->gameStarted && !$this->isGameOver) {
            $this->gameStarted = false;
        }
        // Do nothing if game is already paused, starting, or game over
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
                $this->racketPosition -= self::RACKET_MOVE_STEP;
                break;
            case 'down':
                $this->racketPosition += self::RACKET_MOVE_STEP;
                break;
            case 'up-fast':
                $this->racketPosition -= self::RACKET_MOVE_STEP_FAST;
                break;
            case 'down-fast':
                $this->racketPosition += self::RACKET_MOVE_STEP_FAST;
                break;
        }

        $this->racketPosition = min(self::RACKET_MAX_Y, max(self::RACKET_MIN_Y, $this->racketPosition));
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
        $attenuationFactor = self::BALL_SPEED_LEVEL_FACTOR;
        // The ball speed is increased by 1 every N levels.
        $this->ballSpeed = self::BALL_INITIAL_SPEED + ($level / $attenuationFactor);
    }

    /**
     * Move the ball.
     * Launched by the "game-loop" event.
     *
     * @return void
     */
    public function moveBall(): void
    {
        // Only move if game is running
        if (!$this->gameStarted) {
            return;
        }

        $this->updateBallPosition();

        // Check for wall bounces first (top, bottom, right)
        if ($this->checkWallCollision()) {
            // Wall bounce handled, continue next tick potentially
            // We might return here IF we don't want other checks this tick,
            // but let's allow other checks for now.
        }

        // Check for racket collision (front face)
        if ($this->checkRacketCollision()) {
            return; // Stop further checks this tick after successful bounce
        }

        // Check for racket side collision (left edge)
        if ($this->checkRacketSideCollision()) {
            return; // Stop further checks this tick after successful bounce
        }

        // Check for Game Over AFTER checking bounces
        if ($this->checkGameOver()) {
            $this->gameStarted = false; // Stop the game
            $this->isGameOver = true; // << MARQUER COMME GAME OVER
            // Informer Score d'afficher le message final
            $this->dispatch('show-final-score')->to(Score::class);
            return; // Stop further checks this tick
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
        if ($this->ballPosition['x'] >= self::WALL_RIGHT_X) {
            $this->ballDirection['x'] *= -1;
            return true;
        }

        if ($this->ballPosition['y'] <= self::WALL_TOP_Y ||
            $this->ballPosition['y'] >= self::WALL_BOTTOM_Y
        ) {
            $this->ballDirection['y'] *= -1;
            return true;
        }

        return false;
    }

    /**
     * Check if the ball collides with the racket.
     * Uses <= comparison for X to handle potential overshooting due to speed.
     *
     * @return bool
     */
    private function checkRacketCollision(): bool
    {
        // Check if ball is roughly at the racket's X position and moving left
        if ($this->ballDirection['x'] < 0 && $this->ballPosition['x'] <= self::RACKET_COLLISION_X) {

            // Check Y position against racket margins
            if ($this->ballPosition['y'] >= $this->racketPosition + self::RACKET_COLLISION_MARGIN_TOP &&
                $this->ballPosition['y'] <= $this->racketPosition + self::RACKET_COLLISION_MARGIN_BOTTOM
            ) {
                // Prevent sticking or multiple collisions: snap X position slightly outside
                $this->ballPosition['x'] = self::RACKET_COLLISION_X + abs($this->ballDirection['x'] * $this->ballSpeed * 0.1); // Small offset

                // If the ball is not in the center of the racket, it will bounce off in the opposite direction.
                if ($this->ballPosition['y'] !== $this->racketPosition) {
                    $this->ballDirection['y'] *= -1;
                }
                $this->ballDirection['x'] *= -1; // Reverse X direction
                $this->dispatch('increase-score');
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the ball collides with the racket side.
     * Uses <= comparison for X to handle potential overshooting due to speed.
     *
     * @return bool
     */
    private function checkRacketSideCollision(): bool
    {
        // Check if ball is roughly at the racket side's X position and moving left
        if ($this->ballDirection['x'] < 0 && $this->ballPosition['x'] <= self::RACKET_SIDE_COLLISION_X) {
            // Check Y position against racket side margins
            if ($this->ballPosition['y'] >= $this->racketPosition + self::RACKET_COLLISION_MARGIN_TOP && // Re-using same top margin?
                $this->ballPosition['y'] <= $this->racketPosition + self::RACKET_SIDE_COLLISION_MARGIN_BOTTOM
            ) {
                 // Prevent sticking or multiple collisions: snap X position slightly outside
                $this->ballPosition['x'] = self::RACKET_SIDE_COLLISION_X + abs($this->ballDirection['x'] * $this->ballSpeed * 0.1); // Small offset

                $this->ballDirection['x'] *= -1;
                $this->ballDirection['y'] *= -1;
                $this->dispatch('increase-score');
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the ball has passed the left boundary (Game Over).
     *
     * @return bool
     */
    private function checkGameOver(): bool
    {
        return $this->ballPosition['x'] < self::GAME_OVER_X;
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\View\View
    {
        return view('livewire.game');
    }
}
