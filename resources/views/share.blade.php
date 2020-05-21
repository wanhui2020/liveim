<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>微游</title>

    <script src="{{ asset('js/app.js') }}" defer></script>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="app">
    <img src="/images/app/share_02.png" style="width: 100%">
        <img src="/images/app/share_03.png" style="width: 100%">
    <a href="{{url('testflight')}}">
        <img src="/images/app/share_04.png" style="width: 100%">
    </a>
</div>
</body>
</html>
