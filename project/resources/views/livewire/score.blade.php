<div class="score">
    @if ($showFinalMessage)
        <span class="bold" style="color: red; font-size: 1.5em;">GAME OVER!</span><br>
        <span class="bold">Final Score</span>: {{ $score }}
        {{-- Ajouter un message "Press Space to Play Again"? Non, géré par l'overlay de Game. --}}
    @else
        <span class="bold">Score</span>: {{ $score }}
    @endif
</div>
