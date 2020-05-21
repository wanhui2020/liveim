<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{$site['name']}}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('static/css/index.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('src/layuiadmin/layui/css/layui.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

</head>
<body>
<div id="app">

    <div class="house-header">
        <div class="layui-container">
            <div class="house-nav">
      <span class="layui-breadcrumb" lay-separator="|">
        <a href="login">登录</a>
        <a href="">我的订单</a>
        <a href="">在线客服</a>
      </span>
                <span class="layui-breadcrumb house-breadcrumb-icon" lay-separator=" ">
        <a id="search"><i class="layui-icon layui-icon-house-find"></i></a>
        <a href="login"><i class="layui-icon layui-icon-username"></i></a>
        <a href="usershop"><i class="layui-icon layui-icon-house-shop"></i></a>
      </span>
            </div>
            <div class="house-banner layui-form">
                <a class="banner" href="{{url('mall')}}">
                    <img src="../static/img/logo.png" alt="开源商城">
                </a>
                <div class="layui-input-inline">
                    <input type="text" placeholder="搜索好物" class="layui-input"><i
                            class="layui-icon layui-icon-house-find"></i>
                </div>
                <a class="shop" href="{{url('mall/usershop')}}"><i class="layui-icon layui-icon-house-shop"></i><span
                            class="layui-badge">1</span></a>
            </div>
            <ul class="layui-nav close">
                <li class="layui-nav-item layui-this"><a href="{{url('mall')}}">首页</a></li>
                @foreach($classify as $item )
                    <li class="layui-nav-item"><a href="{{url('mall/lists?id=').$item->id}}">{{$item->name}}</a></li>
                @endforeach
            </ul>
            <button id="switch">
                <span></span><span class="second"></span><span class="third"></span>
            </button>
        </div>
    </div>
    @yield('content')
    <div class="house-footer">
        <div class="layui-container">
            <div class="intro">
                <span class="first"><i class="layui-icon layui-icon-house-shield"></i>7天无理由退换货</span>
                <span class="second"><i class="layui-icon layui-icon-house-car"></i>满99元全场包邮</span>
                <span class="third"><i class="layui-icon layui-icon-house-diamond"></i>100%品质保证</span>
                <span class="last"><i class="layui-icon layui-icon-house-tel"></i>客服{{$site['tel']}}</span>
            </div>
            <div class="about">
      <span class="layui-breadcrumb" lay-separator="|">
        <a href="{{url('mall/about')}}">关于我们</a>
        <a href="{{url('mall/about')}}">帮助中心</a>
        <a href="{{url('mall/about')}}">售后服务</a>
        <a href="{{url('mall/about')}}">配送服务</a>
        <a href="{{url('mall/about')}}">关于货源</a>
      </span>
                <p>{{$site['name']}}版权所有 &copy; 2012-2020</p>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ asset('src/layuiadmin/layui/layui.js') }}"></script>

<!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
<!--[if lt IE 9]>
<script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
<script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<script>
    layui.config({
        base: '{{ asset('static/js/') }}/'
    }).use('house');
</script>

@yield('script')
</body>
</html>