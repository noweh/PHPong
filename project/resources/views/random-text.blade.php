<div class="random-text">
    @foreach($words = \App\Helpers\RandomWordsHelper::generateWords() as $word)
        <span class="{{ $word['color'] }}">{{ $word['word'] }}</span>
    @endforeach
</div>