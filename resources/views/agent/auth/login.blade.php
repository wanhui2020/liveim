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
            <p>代理商后台</p>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-username"
                       for="LAY-user-login-username"></label>
                <input type="text" name="user_name" id="LAY-user-login-email" lay-verify="required" placeholder="登陆账号"
                       class="layui-input">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password"
                       for="LAY-user-login-password"></label>
                <input type="password" name="password" id="LAY-user-login-password" lay-verify="required"
                       placeholder="密码" class="layui-input">
            </div>
            <div class="layui-form-item">
                <button type="button" class="layui-btn layui-btn-fluid" id="Login" lay-submit lay-filter="login">登 入
                </button>
            </div>
            <div class="layui-trans layui-form-item layadmin-user-login-other">
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
        top.location = "{{url('agent/login')}}";
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
            axios.post("{{url('agent/login')}}", field)
                .then(function (response) {
                        layer.closeAll();
                        if (response.status == 200) {
                            parent.layer.msg('登入成功');
                            return location.href = '{{url('agent')}}'; //后台主页
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
