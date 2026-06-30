<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ETEQS')</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        body { background: linear-gradient(135deg, #0a3470 0%, #0d47a1 100%); min-height: 100vh; }
        .jogo-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; }
        .jogo-top { color: #fff; text-align: center; margin-bottom: 1rem; }
        .jogo-top .marca { font-weight: 800; letter-spacing: 1px; }
        .jogo-top .marca small { display:block; font-weight:400; font-size:.7rem; opacity:.8; }
    </style>
</head>
<body>
<div class="jogo-wrap">
    <div class="jogo-top">
        <div class="marca">ETEQS<small>Escola Teológica Elyseu Queiroz de Souza</small></div>
    </div>
    <div class="w-100" style="max-width: 560px;">
        @yield('content')
    </div>
</div>
</body>
</html>
