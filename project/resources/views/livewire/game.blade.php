<div>
    {{-- Ajout d'un style pour les transitions --}}
    <style>
        .racket {
            /* Garder les styles existants (via CSS externe ou ici) */
            /* Ajouter la transition pour la propriété 'top' */
            transition: top 0.05s linear; /* Durée courte, animation linéaire */
        }
        .ball {
            /* Garder les styles existants */
            /* Ajouter la transition pour 'left' et 'top' */
            transition: left 0.05s linear, top 0.05s linear;
        }
        /* Garder les styles des overlays etc. s'ils étaient ici, 
           sinon s'assurer qu'ils sont dans le CSS externe */
        .game { position: relative; /* Important pour le positionnement absolu */ }
        .overlay, .overlay-game-over { position: absolute; /* ... autres styles overlay ... */ }
    </style>

    {{-- Game area container (Doit avoir position:relative via CSS externe ou style ci-dessus) --}}
    <div class="game">
        {{-- Overlay initial --}}
        @if (!$gameStarted && !$isGameOver)
            <div class="overlay">
                <p>Press Space to Play/Pause</p>
            </div>
        @endif

        {{-- Overlay GAME OVER --}}
        @if ($isGameOver)
            <div class="overlay-game-over">
                <p>Press "R" to Retry</p>
            </div>
        @endif

        {{-- Racket (position top en pixels) --}}
        @if (!$isGameOver)
            <div
                class="racket"
                style="top: {{ $racketPosition }}px;"
            >
                {{-- Remettre les spans --}}
                <span>||</span>
                <span>||</span>
                <span>||</span>
            </div>
        @endif
        {{-- Ball (position left/top en pixels) --}}
        @if ($gameStarted || !$isGameOver)
            <div
                class="ball"
                style="left: {{ $ballPosition['x'] }}px; top: {{ $ballPosition['y'] }}px;"
            >
                {{-- Remettre le span --}}
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
</script>
@endscript