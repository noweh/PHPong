@extends('layouts.app')

@section('content')
    <div class="infos">
        @livewire(Score::class)
        @livewire(Level::class)
    </div>
    @livewire(Game::class)
@endsection