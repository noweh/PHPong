<div>
    <div class="game">
        @if (!$gameStarted && !$isGameOver)
            <div class="overlay">
                <p>Press Space to Play/Pause</p>
            </div>
        @endif

        @if ($isGameOver)
            <div class="overlay-game-over">
                <p>Press "R" to Retry</p>
            </div>
        @endif

        @if (!$isGameOver)
            <div
                class="racket bold"
                style="top: {{ $racketPosition }}vh;"
            >
                <span>||</span>
                <span>||</span>
                <span>||</span>
            </div>
        @endif
        @if ($gameStarted || !$isGameOver)
            <div
                class="ball bold"
                style=
                    "left: {{ $ballPosition['x'] }}vh;
                    top: {{ $ballPosition['y'] }}vh;"
            >
                <span>(&&)</span>
            </div>
        @endif
    </div>
</div>

@script
<script>
    let gameStarted = {{ $gameStarted ? 'true' : 'false' }};
    let longPressTimeout = 0;
    let longPress = false;
    let lastTime = null;

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
                }, 200);
                if (!longPress) {
                    $wire.dispatch('move-racket', {'direction': 'up'})
                }
            }
            if (event.key === 'ArrowDown') {
                longPressTimeout = setTimeout(() => {
                    longPress = true;
                    $wire.dispatch('move-racket', {'direction': 'down-fast'})
                }, 200);
                if (!longPress) {
                    $wire.dispatch('move-racket', {'direction': 'down'})
                }
            }
        }
    });

    function gameLoop(time) {
        if (lastTime !== null) {
            let deltaTime = time - lastTime;
            const speed = $wire.ballSpeed || 1;
            if (gameStarted && deltaTime >= 100 / speed) {
                $wire.moveBall();
                lastTime = time;
            }
        } else {
            lastTime = time;
        }

        requestAnimationFrame(gameLoop);
    }
    requestAnimationFrame(gameLoop);
</script>
@endscript