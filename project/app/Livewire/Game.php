<?php

namespace App\Livewire;

use App\Livewire\Score; // Importer Score
use App\Livewire\Level; // Importer Level
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
    private const RACKET_COLLISION_X = 10; // Position X de la face avant de la raquette (pixels)
    private const RACKET_SIDE_COLLISION_X = 0; // Position X du bord gauche
    private const GAME_OVER_X = -self::BALL_SIZE_PX - 20;
    // Racket
    private const RACKET_WIDTH_PX = 10; // Largeur visuelle (pixels)
    private const RACKET_HEIGHT_PX = 80; // Hauteur visuelle (pixels) - Ajuster si besoin
    private const RACKET_INITIAL_Y = (self::AREA_HEIGHT / 2) - (self::RACKET_HEIGHT_PX / 2); // Centré verticalement
    private const RACKET_MOVE_STEP = 15; // Pixels par déplacement
    private const RACKET_MOVE_STEP_FAST = 45; // Pixels par déplacement rapide
    private const RACKET_MIN_Y = self::WALL_TOP_Y;
    private const RACKET_MAX_Y = self::WALL_BOTTOM_Y - self::RACKET_HEIGHT_PX + 35; // Limite basse
    private const RACKET_Y_COLLISION_TOLERANCE = 5; // Pixels de tolérance en haut/bas
    // Ball
    private const BALL_SIZE_PX = 15; // Diamètre (pixels) - Ajuster si besoin
    private const BALL_INITIAL_X = self::AREA_WIDTH / 2;
    private const BALL_INITIAL_Y = self::AREA_HEIGHT / 2;
    private const BALL_INITIAL_SPEED = 6; // Vitesse initiale (pixels par tick) - Ajuster
    private const BALL_INITIAL_DIR_X = 1;
    private const BALL_INITIAL_DIR_Y = 1;
    private const BALL_SPEED_SQRT_FACTOR = 2; // Utiliser un facteur pour une courbe racine carrée
    // --- End Constants ---

    // --- Properties ---
    public bool $gameStarted = false;
    public bool $isGameOver = false;
    public float $racketPosition = self::RACKET_INITIAL_Y; // Utiliser float pour position Y précise
    public array $ballPosition = ['x' => self::BALL_INITIAL_X, 'y' => self::BALL_INITIAL_Y];
    public array $ballDirection = ['x' => self::BALL_INITIAL_DIR_X, 'y' => self::BALL_INITIAL_DIR_Y];
    public float $ballSpeed = self::BALL_INITIAL_SPEED; // Utiliser float pour vitesse précise

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
        // S'assurer que le niveau est au moins 1 pour éviter sqrt(0) ou négatif
        $effectiveLevel = max(1, $level);
        // Nouvelle formule utilisant la racine carrée
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
        // Right wall (Prendre en compte la TAILLE de la balle)
        if ($this->ballPosition['x'] >= self::WALL_RIGHT_X - self::BALL_SIZE_PX && $this->ballDirection['x'] > 0) {
            $this->ballDirection['x'] *= -1;
            $this->dispatch('trigger-ball-squash');
            // Snap le bord droit de la balle contre le mur
            $this->ballPosition['x'] = self::WALL_RIGHT_X - self::BALL_SIZE_PX;
            $collided = true;
        }
        // Top wall (Pas besoin de taille, position Y est déjà le haut)
        if ($this->ballPosition['y'] <= self::WALL_TOP_Y && $this->ballDirection['y'] < 0) {
            $this->ballDirection['y'] *= -1;
            $this->dispatch('trigger-ball-squash');
            $this->ballPosition['y'] = self::WALL_TOP_Y; // Snap
            $collided = true;
        }
        // Bottom wall (Prendre en compte la TAILLE de la balle)
        elseif ($this->ballPosition['y'] >= self::WALL_BOTTOM_Y - self::BALL_SIZE_PX && $this->ballDirection['y'] > 0) {
            $this->ballDirection['y'] *= -1;
            $this->dispatch('trigger-ball-squash');
            // Snap le bord bas de la balle contre le mur
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
            $this->ballPosition['x'] > self::RACKET_SIDE_COLLISION_X ) { // Eviter conflit avec side collision

            // Check Y position avec TOLÉRANCE
            if ($this->ballPosition['y'] >= $this->racketPosition - self::RACKET_Y_COLLISION_TOLERANCE &&
                $this->ballPosition['y'] <= $this->racketPosition + self::RACKET_HEIGHT_PX + self::RACKET_Y_COLLISION_TOLERANCE
            ) {
                // Snap X à la face avant de la raquette
                $this->ballPosition['x'] = self::RACKET_COLLISION_X; // + self::RACKET_WIDTH_PX ? Non, juste X.

                // Logique de rebond simple (à améliorer)
                $racketCenterY = $this->racketPosition + (self::RACKET_HEIGHT_PX / 2.0);
                $impactFactor = ($this->ballPosition['y'] - $racketCenterY) / (self::RACKET_HEIGHT_PX / 2.0);
                $this->ballDirection['y'] = $impactFactor * 1.5; // Angle basé sur point d'impact
                $this->ballDirection['x'] *= -1; // Inverser X
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
            // Check Y position avec TOLÉRANCE
            if ($this->ballPosition['y'] >= $this->racketPosition - self::RACKET_Y_COLLISION_TOLERANCE &&
                $this->ballPosition['y'] <= $this->racketPosition + self::RACKET_HEIGHT_PX + self::RACKET_Y_COLLISION_TOLERANCE
            ) {
                $this->ballPosition['x'] = self::RACKET_SIDE_COLLISION_X; // Snap X
                // Rebond différent pour le côté?
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
