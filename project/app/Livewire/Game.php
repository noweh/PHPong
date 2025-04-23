<?php

namespace App\Livewire;

use App\Livewire\Score; // Import Score
use App\Livewire\Level; // Import Level
use Livewire\Attributes\On;
use Livewire\Component;

class Game extends Component
{
    // --- Constants (PIXEL-BASED for 800x500 area) ---
    // Boundaries
    private const AREA_WIDTH = 800;
    private const AREA_HEIGHT = 400;
    private const WALL_RIGHT_X = self::AREA_WIDTH + 55;
    private const WALL_BOTTOM_Y = self::AREA_HEIGHT - 30;
    private const WALL_TOP_Y = 10;
    private const RACKET_COLLISION_X = 10; // Racket front face X position (pixels)
    private const RACKET_SIDE_COLLISION_X = 0; // Left edge X position
    private const GAME_OVER_X = -self::BALL_SIZE_PX - 20;
    // Racket
    private const RACKET_WIDTH_PX = 10; // Visual width (pixels)
    private const RACKET_HEIGHT_PX = 80; // Visual height (pixels) - Adjust if needed
    private const RACKET_INITIAL_Y = (self::AREA_HEIGHT / 2) - (self::RACKET_HEIGHT_PX / 2); // Centered vertically
    private const RACKET_MOVE_STEP = 15; // Pixels per movement
    private const RACKET_MOVE_STEP_FAST = 45; // Pixels per fast movement
    private const RACKET_MIN_Y = self::WALL_TOP_Y;
    private const RACKET_MAX_Y = self::WALL_BOTTOM_Y - self::RACKET_HEIGHT_PX + 25; // Lower limit
    private const RACKET_Y_COLLISION_TOLERANCE = 5; // Top/bottom tolerance in pixels
    // Ball
    private const BALL_SIZE_PX = 15; // Diameter (pixels) - Adjust if needed
    private const BALL_INITIAL_X = self::AREA_WIDTH / 2;
    private const BALL_INITIAL_Y = self::AREA_HEIGHT / 2;
    private const BALL_INITIAL_SPEED = 8;
    private const BALL_INITIAL_DIR_X = 1;
    private const BALL_INITIAL_DIR_Y = 1;
    private const BALL_SPEED_SQRT_FACTOR = 2; // Use a factor for a square root curve
    // --- End Constants ---

    // --- Properties ---
    public bool $gameStarted = false;
    public bool $isGameOver = false;
    public float $racketPosition = self::RACKET_INITIAL_Y; // Use float for precise Y position
    public array $ballPosition = ['x' => self::BALL_INITIAL_X, 'y' => self::BALL_INITIAL_Y];
    public array $ballDirection = ['x' => self::BALL_INITIAL_DIR_X, 'y' => self::BALL_INITIAL_DIR_Y];
    public float $ballSpeed = self::BALL_INITIAL_SPEED; // Use float for precise speed

    /**
     * Start or restart the game.
     */
    public function startGame(): void
    {
        $this->gameStarted = true;
        $this->isGameOver = false;
        $this->racketPosition = self::RACKET_INITIAL_Y;
        $this->ballPosition = ['x' => self::BALL_INITIAL_X, 'y' => self::BALL_INITIAL_Y];
        $this->ballDirection = ['x' => self::BALL_INITIAL_DIR_X, 'y' => (rand(0, 1) ? 1 : -1) * self::BALL_INITIAL_DIR_Y];
        $this->ballSpeed = self::BALL_INITIAL_SPEED;
        $this->dispatch('reset-score-display')->to(Score::class);
        $this->dispatch('reset-level')->to(Level::class);
    }

    /**
     * Resume the game from pause.
     */
    public function resumeGame(): void
    {
        if (!$this->isGameOver && !$this->gameStarted) {
            $this->gameStarted = true;
        }
    }

    public function pauseGame(): void
    {
        if ($this->gameStarted && !$this->isGameOver) {
            $this->gameStarted = false;
        }
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
        $step = 0.0;
        switch ($direction) {
            case 'up': $step = -self::RACKET_MOVE_STEP; break;
            case 'down': $step = self::RACKET_MOVE_STEP; break;
            case 'up-fast': $step = -self::RACKET_MOVE_STEP_FAST; break;
            case 'down-fast': $step = self::RACKET_MOVE_STEP_FAST; break;
        }
        $newPosition = $this->racketPosition + $step;
        $this->racketPosition = min(self::RACKET_MAX_Y, max(self::RACKET_MIN_Y, $newPosition));
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
        // Ensure level is at least 1 to avoid sqrt(0) or negative
        $effectiveLevel = max(1, $level);
        // New formula using square root
        $this->ballSpeed = self::BALL_INITIAL_SPEED + (self::BALL_SPEED_SQRT_FACTOR * sqrt($effectiveLevel));
    }

    /**
     * Move the ball.
     * Launched by the "game-loop" event.
     *
     * @return void
     */
    public function moveBall(): void
    {
        if (!$this->gameStarted) return;
        $this->updateBallPosition();
        if ($this->checkWallCollision()) { }
        if ($this->checkRacketCollision()) { return; }
        if ($this->checkRacketSideCollision()) { return; }
        if ($this->checkGameOver()) {
            $this->gameStarted = false;
            $this->isGameOver = true;
            $this->dispatch('show-final-score')->to(Score::class);
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
        $collided = false;
        // Right wall (Consider ball SIZE)
        if ($this->ballPosition['x'] >= self::WALL_RIGHT_X - self::BALL_SIZE_PX && $this->ballDirection['x'] > 0) {
            $this->ballDirection['x'] *= -1;
            $this->dispatch('trigger-ball-squash');
            // Snap ball's right edge to the wall
            $this->ballPosition['x'] = self::WALL_RIGHT_X - self::BALL_SIZE_PX;
            $collided = true;
        }
        // Top wall (No size needed, Y position is already the top)
        if ($this->ballPosition['y'] <= self::WALL_TOP_Y && $this->ballDirection['y'] < 0) {
            $this->ballDirection['y'] *= -1;
            $this->dispatch('trigger-ball-squash');
            $this->ballPosition['y'] = self::WALL_TOP_Y; // Snap
            $collided = true;
        }
        // Bottom wall (Consider ball SIZE)
        elseif ($this->ballPosition['y'] >= self::WALL_BOTTOM_Y - self::BALL_SIZE_PX && $this->ballDirection['y'] > 0) {
            $this->ballDirection['y'] *= -1;
            $this->dispatch('trigger-ball-squash');
            // Snap ball's bottom edge to the wall
            $this->ballPosition['y'] = self::WALL_BOTTOM_Y - self::BALL_SIZE_PX;
            $collided = true;
        }
        return $collided;
    }

    /**
     * Check if the ball collides with the racket.
     * Uses corrected Y check based on estimated visual height.
     *
     * @return bool
     */
    private function checkRacketCollision(): bool
    {
        // Check direction & rough X position
        if ($this->ballDirection['x'] < 0 &&
            $this->ballPosition['x'] <= self::RACKET_COLLISION_X + self::RACKET_WIDTH_PX && // Collision zone X
            $this->ballPosition['x'] > self::RACKET_SIDE_COLLISION_X ) { // Avoid conflict with side collision

            // Check Y position with TOLERANCE
            if ($this->ballPosition['y'] >= $this->racketPosition - self::RACKET_Y_COLLISION_TOLERANCE &&
                $this->ballPosition['y'] <= $this->racketPosition + self::RACKET_HEIGHT_PX + self::RACKET_Y_COLLISION_TOLERANCE
            ) {
                // Snap X to the racket's front face
                $this->ballPosition['x'] = self::RACKET_COLLISION_X; // + self::RACKET_WIDTH_PX ? No, just X.

                // Simple bounce logic (to be improved)
                $racketCenterY = $this->racketPosition + (self::RACKET_HEIGHT_PX / 2.0);
                $impactFactor = ($this->ballPosition['y'] - $racketCenterY) / (self::RACKET_HEIGHT_PX / 2.0);
                $this->ballDirection['y'] = $impactFactor * 1.5; // Angle based on impact point
                $this->ballDirection['x'] *= -1; // Reverse X
                $this->dispatch('increase-score');
                $this->dispatch('racket-hit');
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the ball collides with the racket side.
     * Uses corrected Y check based on estimated visual height.
     *
     * @return bool
     */
    private function checkRacketSideCollision(): bool
    {
        // Check direction & rough X position
        if ($this->ballDirection['x'] < 0 && $this->ballPosition['x'] <= self::RACKET_SIDE_COLLISION_X) {
            // Check Y position with TOLERANCE
            if ($this->ballPosition['y'] >= $this->racketPosition - self::RACKET_Y_COLLISION_TOLERANCE &&
                $this->ballPosition['y'] <= $this->racketPosition + self::RACKET_HEIGHT_PX + self::RACKET_Y_COLLISION_TOLERANCE
            ) {
                $this->ballPosition['x'] = self::RACKET_SIDE_COLLISION_X; // Snap X
                // Different bounce for the side?
                $this->ballDirection['x'] *= -1;
                $this->ballDirection['y'] *= -1;
                $this->dispatch('increase-score');
                $this->dispatch('racket-hit');
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
     * Sets the racket position directly (e.g., from mouse input).
     * Ensures the position stays within bounds.
     */
    public function setRacketPosition(float $y): void
    {
        // Only update if game is running and not over
        if ($this->gameStarted && !$this->isGameOver) {
            // Validate and clamp the received Y position
            $this->racketPosition = min(self::RACKET_MAX_Y, max(self::RACKET_MIN_Y, $y));
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
