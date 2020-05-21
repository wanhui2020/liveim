<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>微游APP下载</title>

    <script src="{{ asset('js/app.js') }}" defer></script>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="app">
    <img src="/images/app/taste_02.png" style="width: 100%">
    <a href="https://apps.apple.com/cn/app/testflight/id899247664">
        <img src="/images/app/taste_03.png" style="width: 100%">
    </a>
    <a href="https://testflight.apple.com/join/pyhQlQKd">
        <img src="/images/app/taste_04.png" style="width: 100%">
    </a>
    <img src="/images/app/taste_05.png" style="width: 100%">
    <img src="/images/app/taste_06.png" style="width: 100%">
</div>
</body>
</html>
