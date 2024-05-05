<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPong</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
    @livewireStyles
</head>
<body>
<div class="page">
    <div class="page-overlay"></div>
    @include('random-text')

    <div class="title">
        <h1>
        <pre class="preformatted-text">
<span class="violet"> ########   #######  ##     ## ########   #######  ##    ##  ######</span>   <span class="white">####</span>
<span class="violet">##  ##  ## ##     ## ##     ## ##     ## ##     ## ###   ## ##    ##</span>  <span class="white">####</span>
<span class="violet">##  ##     ##     ## ##     ## ##     ## ##     ## ####  ## ##</span>
<span class="violet"> ########  ########  ######### ########  ##     ## ## ## ## ##   ####</span> <span class="white">####</span>
<span class="violet">    ##  ## ##        ##     ## ##        ##     ## ##  #### ##    ##</span>  <span class="white">####</span>
<span class="violet">##  ##  ## ##        ##     ## ##        ##     ## ##   ### ##    ##</span>   <span class="white">##</span>
<span class="violet"> ########  ##        ##     ## ##         #######  ##    ##  ######</span>   <span class="white">##</span>
        </pre>
        </h1>
    </div>
    <div class="content">
        @yield('content')
    </div>
</div>
<script src="{{ asset('js/app.min.js') }}"></script>
@livewireScripts
</body>
</html>