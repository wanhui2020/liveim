<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台登入</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{ asset('dist/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{ asset('dist/layuiadmin/style/admin.css')}}" media="all">
    <link rel="stylesheet" href="{{ asset('dist/layuiadmin/style/login.css')}}" media="all">
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2>{{env('APP_NAME')}}</h2>
            <p>系统管理1.0</p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-username"
                       for="LAY-user-login-username"></label>
                <input type="text" name="email" id="LAY-user-login-email" lay-verify="required" placeholder="邮箱账号"
                       class="layui-input">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password"
                       for="LAY-user-login-password"></label>
                <input type="password" name="password" id="LAY-user-login-password" lay-verify="required"
                       autocomplete="new-password"  placeholder="密码" class="layui-input">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password"
                       for="LAY-user-login-password"></label>
                <input type="password" name="safety" id="LAY-user-login-password" lay-verify="required"
                       placeholder="安全码" class="layui-input">
            </div>
            {{--<div class="layui-form-item" style="margin-bottom: 20px;">
                <input type="checkbox" name="remember" lay-skin="primary" title="记住密码">
            </div>--}}
            {{--<div class="layui-form-item">--}}
            {{--<div class="layui-row">--}}
            {{--<div class="layui-col-xs7">--}}
            {{--<label class="layadmin-user-login-icon layui-icon layui-icon-vercode"--}}
            {{--for="LAY-user-login-vercode"></label>--}}
            {{--<input type="text" name="vercode" id="LAY-user-login-vercode" lay-verify="required"--}}
            {{--placeholder="图形验证码" class="layui-input">--}}
            {{--</div>--}}
            {{--<div class="layui-col-xs5">--}}
            {{--<div style="margin-left: 10px;">--}}
            {{--<img src="https://www.oschina.net/action/user/captcha" class="layadmin-user-login-codeimg"--}}
            {{--id="LAY-user-get-vercode">--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--<div class="layui-form-item" style="margin-bottom: 20px;">--}}
            {{--<input type="checkbox" name="remember" lay-skin="primary" title="记住密码">--}}
            {{--<a href="forget.html" class="layadmin-user-jump-change layadmin-link" style="margin-top: 7px;">忘记密码？</a>--}}
            {{--</div>--}}
            <div class="layui-form-item">
                <button type="button" class="layui-btn layui-btn-fluid" id="Login" lay-submit lay-filter="login">登 入
                </button>
            </div>
            <div class="layui-trans layui-form-item layadmin-user-login-other">
                {{--<label>社交账号登入</label>--}}
                {{--<a href="javascript:;"><i class="layui-icon layui-icon-login-qq"></i></a>--}}
                {{--<a href="javascript:;"><i class="layui-icon layui-icon-login-wechat"></i></a>--}}
                {{--<a href="javascript:;"><i class="layui-icon layui-icon-login-weibo"></i></a>--}}

                {{--<a href="register" class="layadmin-user-jump-change layadmin-link">注册帐号</a>--}}
            </div>
        </div>
    </div>

    {{--<div class="layui-trans layadmin-user-login-footer">--}}
    {{--<p>© 2019 <a href="http://www.cqyuanyou.cn/" target="_blank">{{env('APP_NAME')}}</a></p>--}}

    {{--</div>--}}


</div>

<script src="{{ asset('js/app.js')}}"></script>
<script src="{{ asset('dist/layuiadmin/layui/layui.js')}}"></script>
<script>
    if (top.location !== self.location) {
        top.location = "{{url('system/login')}}";
    }
    layui.use(['form', 'jquery', 'layer'], function () {
        let $ = layui.jquery,
            form = layui.form,
            layer = layui.layer
        ;
        form.render();
        //提交

        form.on('submit(login)', function (obj) {
            let field = obj.field;
            layer.load();
            axios.post("{{url('system/login')}}", field)
                .then(function (response) {
                        layer.close();
                        if (response.status == 200) {
                            parent.layer.msg('登入成功');
                            return location.href = '{{url('system')}}'; //后台主页
                        }
                        layer.msg(response.data.msg);
                    }
                ).catch(function (err) {
                layer.closeAll();
                let response = err.response;
                if (response.status === 422) {
                    //登入成功的提示与跳转
                    layer.msg('验证失败,请检查用户名或密码是否错误');
                }
                if (response.status === 500) {
                    //登入成功的提示与跳转
                    layer.msg('系统异常，请联系管理员');
                }
            });
            return false;
        });
        $(document).keydown(function (e) {
            if (e.keyCode === 13) {
                $("#Login").trigger("click");
            }
        });

    });
</script>
</body>
</html>
