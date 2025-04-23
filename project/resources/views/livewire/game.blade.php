<div>
    {{-- Adding style for transitions --}}
    <style>
        .racket {
            transition: top 0.05s linear; /* RE-ENABLED */
        }
        .ball {
            transition: left 0.05s linear, top 0.05s linear; /* RE-ENABLED */
        }
        .game { position: relative; /* Important for absolute positioning */ }
        .overlay, .overlay-game-over { position: absolute; /* ... other overlay styles ... */ }

        /* --- "Juice" Animations (Originals with Alpine) --- */
        .ball-squash {
            animation: squash 0.2s ease-out; /* Uses @keyframes squash */
        }
        .racket-flash {
            animation: flash 0.2s linear;  /* Uses @keyframes flash - Duration increased */
        }

        /* Original keyframes (modified for more impact) */
        @keyframes squash {
            0%, 100% { transform: scale(1, 1); }
            50% { transform: scale(1.6, 0.4); } /* << MORE EXAGGERATED */
        }
        @keyframes flash {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(2.5); } /* << BRIGHTER */
        }
        /* @keyframes simple-opacity { ... } */ /* Removed as unnecessary */
    </style>

    {{-- Main container with Alpine --}}
    <div 
        x-data="{
            isBallSquashing: false, 
            isRacketFlashing: false
        }"
        @trigger-ball-squash.window="isBallSquashing = true; setTimeout(() => isBallSquashing = false, 500)"
        @trigger-racket-flash.window="isRacketFlashing = true; setTimeout(() => isRacketFlashing = false, 500)"
    >
        {{-- Game area container (Must have position:relative via external CSS or style above) --}}
        <div class="game">
            {{-- Initial overlay --}}
            @if (!$gameStarted && !$isGameOver)
                <div class="overlay">
                    <p>Press Space to Play/Pause</p>
                </div>
            @endif

            {{-- GAME OVER overlay --}}
            @if ($isGameOver)
                <div class="overlay-game-over">
                    <p>Press "R" to Retry</p>
                </div>
            @endif

            {{-- Racket (top position in pixels) --}}
            @if (!$isGameOver)
                <div
                    class="racket"
                    :class="{ 'racket-flash': isRacketFlashing }" {{-- Alpine handles the class --}}
                    style="top: {{ $racketPosition }}px;"
                >
                    {{-- Restore the spans --}}
                    <span>||</span>
                    <span>||</span>
                    <span>||</span>
                </div>
            @endif
            {{-- Ball (left/top position in pixels) --}}
            @if ($gameStarted || !$isGameOver)
                <div
                    class="ball"
                    :class="{ 'ball-squash': isBallSquashing }" {{-- Alpine handles the class --}}
                    style="left: {{ $ballPosition['x'] }}px; top: {{ $ballPosition['y'] }}px;"
                >
                    {{-- Restore the span --}}
                    <span>(&&)</span>
                </div>
            @endif
        </div>
    </div>
</div>

@script
<script>
    let gameStarted = {{ $gameStarted ? 'true' : 'false' }};
    let longPressTimeout = 0;
    let longPress = false;
    let lastTime = null;

    // JS constants based on PHP values (adjust if PHP changes!)
    const RACKET_HEIGHT_PX = 80;
    // RACKET_MIN_Y = WALL_TOP_Y(10) + 10 = 20
    const RACKET_MIN_Y = 20; 
    // RACKET_MAX_Y = WALL_BOTTOM_Y(460) - RACKET_HEIGHT_PX(80) + 30 = 410
    const RACKET_MAX_Y = 410; 

    // Reference to the game area
    const gameArea = document.querySelector('.game');

    document.addEventListener('keyup', event => {
        clearTimeout(longPressTimeout);
        longPress = false;
    });

    document.addEventListener('keydown', event => {
        if ((event.key === 'r' || event.key === 'R') && !event.ctrlKey && !event.metaKey) {
            event.preventDefault();
            console.log('R key pressed (without Ctrl/Meta) -> calling startGame()');
            gameStarted = true;
            $wire.startGame();
            return;
        }

        if (event.key === ' ') {
            event.preventDefault();

            if (!gameStarted) {
                gameStarted = true;
                $wire.resumeGame();
            } else {
                gameStarted = false;
                $wire.pauseGame();
            }
        }

        if (gameStarted) {
            if (event.key === 'ArrowUp') {
                longPressTimeout = setTimeout(() => {
                    longPress = true;
                    $wire.dispatch('move-racket', {'direction': 'up-fast'})
                }, 150);
                if (!longPress) {
                    $wire.dispatch('move-racket', {'direction': 'up'})
                }
            }
            if (event.key === 'ArrowDown') {
                longPressTimeout = setTimeout(() => {
                    longPress = true;
                    $wire.dispatch('move-racket', {'direction': 'down-fast'})
                }, 150);
                if (!longPress) {
                    $wire.dispatch('move-racket', {'direction': 'down'})
                }
            }
        }
    });

    // --- Mouse Control ---
    if (gameArea) {
        gameArea.addEventListener('mousemove', (event) => {
            // Only control with mouse if the game is started
            if (!gameStarted) return;

            // Calculate mouse Y relative to the .game container
            const rect = gameArea.getBoundingClientRect();
            const mouseY = event.clientY - rect.top;

            // Calculate desired top position to center the racket on the mouse
            let desiredTop = mouseY - (RACKET_HEIGHT_PX / 2);

            // Clamp the position within allowed limits
            const clampedTop = Math.min(RACKET_MAX_Y, Math.max(RACKET_MIN_Y, desiredTop));

            // Send the validated position to the backend
            // Use requestAnimationFrame to limit call frequency?
            // For now, direct call for max responsiveness.
            $wire.setRacketPosition(clampedTop);
        });

        // Optional: Hide the mouse when it enters the game area
        // gameArea.style.cursor = 'none'; 
    }

    function gameLoop(time) {
        if (lastTime !== null) {
            let deltaTime = time - lastTime;
            const UPDATE_INTERVAL_MS = 33;
            if (gameStarted && deltaTime >= UPDATE_INTERVAL_MS) {
                $wire.moveBall();
                lastTime = time;
            }
        } else {
            lastTime = time;
        }

        requestAnimationFrame(gameLoop);
    }
    requestAnimationFrame(gameLoop);

    // --- Simplified Livewire Listener ---
    Livewire.on('racket-hit', () => {
        // Dispatch events that Alpine listens for
        window.dispatchEvent(new CustomEvent('trigger-ball-squash'));
        window.dispatchEvent(new CustomEvent('trigger-racket-flash'));
        // No more direct DOM manipulation here
    });
</script>
@endscript