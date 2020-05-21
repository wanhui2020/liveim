<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{env('APP_NAME')}}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{ asset('dist/layuiadmin/layui/css/layui.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('dist/layuiadmin/style/admin.css') }} " media="all">
    <link rel="stylesheet" href="{{ asset('css/common.css') }} " media="all">

</head>
<body>
<div id="app">
    @yield('content')
</div>
<script src="{{ asset('js/app.js')}}"></script>
<script src="{{ asset('dist/layuiadmin/layui/layui.js?t=1')}}"></script>
<script src="{{ asset('js/common.js')}}"></script>
@yield('script')
</body>
</html>
