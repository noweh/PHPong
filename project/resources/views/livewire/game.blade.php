<div>
    @if (!$gameStarted)
        <div class="overlay">
            <p>Press Space button to play</p>
        </div>
    @endif

    <div class="game">
        <div
            class="racket bold"
            style="top: {{ $racketPosition }}vh;"
        >
            <span>||</span>
            <span>||</span>
            <span>||</span>
        </div>
        <div
            class="ball bold"
            style=
                "left: {{ $ballPosition['x'] }}vh;
                top: {{ $ballPosition['y'] }}vh;"
        >
            <span>(&&)</span>
        </div>
    </div>
</div>

@script
<script>
    let gameStarted = false;
    let longPressTimeout = 0;
    let longPress = false;

    document.addEventListener('keyup', event => {
        clearTimeout(longPressTimeout);
        longPress = false;
    });

    document.addEventListener('keydown', event => {
        if (event.key === ' ') {
            gameStarted = true;
            $wire.startGame();
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

    setInterval(() => {
        if (gameStarted) {
            $wire.moveBall();
        }
    }, 100 / $wire.ballSpeed);
</script>
@endscript