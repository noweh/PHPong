<div>
    {{-- Ajout d'un style pour les transitions --}}
    <style>
        .racket {
            transition: top 0.05s linear; /* RE-ACTIVÉ */
        }
        .ball {
            transition: left 0.05s linear, top 0.05s linear; /* RE-ACTIVÉ */
        }
        .game { position: relative; /* Important pour le positionnement absolu */ }
        .overlay, .overlay-game-over { position: absolute; /* ... autres styles overlay ... */ }

        /* --- Animations "Juice" (Originales avec Alpine) --- */
        .ball-squash {
            animation: squash 0.2s ease-out; /* Utilise @keyframes squash */
        }
        .racket-flash {
            animation: flash 0.2s linear;  /* Utilise @keyframes flash - Durée augmentée */
        }

        /* Keyframes originaux (modifiés pour plus d'impact) */
        @keyframes squash {
            0%, 100% { transform: scale(1, 1); }
            50% { transform: scale(1.6, 0.4); } /* << PLUS EXAGÉRÉ */
        }
        @keyframes flash {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(2.5); } /* << PLUS LUMINEUX */
        }
        /* @keyframes simple-opacity { ... } */ /* Supprimé car inutile */
    </style>

    {{-- Conteneur principal avec Alpine --}}
    <div 
        x-data="{
            isBallSquashing: false, 
            isRacketFlashing: false
        }"
        @trigger-ball-squash.window="isBallSquashing = true; setTimeout(() => isBallSquashing = false, 500)"
        @trigger-racket-flash.window="isRacketFlashing = true; setTimeout(() => isRacketFlashing = false, 500)"
    >
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
                    :class="{ 'racket-flash': isRacketFlashing }" {{-- Alpine gère la classe --}}
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
                    :class="{ 'ball-squash': isBallSquashing }" {{-- Alpine gère la classe --}}
                    style="left: {{ $ballPosition['x'] }}px; top: {{ $ballPosition['y'] }}px;"
                >
                    {{-- Remettre le span --}}
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

    // Constantes JS basées sur les valeurs PHP (ajuster si PHP change!)
    const RACKET_HEIGHT_PX = 80;
    // RACKET_MIN_Y = WALL_TOP_Y(10) + 10 = 20
    const RACKET_MIN_Y = 20; 
    // RACKET_MAX_Y = WALL_BOTTOM_Y(460) - RACKET_HEIGHT_PX(80) + 30 = 410
    const RACKET_MAX_Y = 410; 

    // Référence à la zone de jeu
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

    // --- Contrôle Souris --- 
    if (gameArea) {
        gameArea.addEventListener('mousemove', (event) => {
            // Ne contrôler à la souris que si le jeu est démarré
            if (!gameStarted) return;

            // Calculer Y souris relatif au conteneur .game
            const rect = gameArea.getBoundingClientRect();
            const mouseY = event.clientY - rect.top;

            // Calculer la position top désirée pour centrer la raquette sur la souris
            let desiredTop = mouseY - (RACKET_HEIGHT_PX / 2);

            // Clamper la position dans les limites autorisées
            const clampedTop = Math.min(RACKET_MAX_Y, Math.max(RACKET_MIN_Y, desiredTop));

            // Envoyer la position validée au backend
            // Utiliser requestAnimationFrame pour limiter la fréquence des appels?
            // Pour l'instant, appel direct pour max réactivité.
            $wire.setRacketPosition(clampedTop);
        });

        // Optionnel: Cacher la souris quand elle entre dans la zone de jeu
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

    // --- Listener Livewire simplifié --- 
    Livewire.on('racket-hit', () => {
        // Dispatcher des événements que Alpine écoute
        window.dispatchEvent(new CustomEvent('trigger-ball-squash'));
        window.dispatchEvent(new CustomEvent('trigger-racket-flash'));
        // Plus de manipulation directe du DOM ici
    });
</script>
@endscript