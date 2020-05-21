@extends('layouts.base')
@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">设置我的资料</div>
                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id"  >
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">我的角色</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <select name="type" lay-verify="">--}}
{{--                                        <option value="0" selected>超级管理员</option>--}}
{{--                                        <option value="1">系统管理员</option>--}}

{{--                                    </select>--}}
{{--                                </div>--}}
{{--                                <div class="layui-form-mid layui-word-aux">当前角色不可更改为其它角色</div>--}}
{{--                            </div>--}}
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">姓名</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="text" name="name"  class="layui-input">--}}
{{--                                </div>--}}
{{--                                <div class="layui-form-mid layui-word-aux">不可修改。一般用于后台登入名</div>--}}
{{--                            </div>--}}
                            <div class="layui-form-item">
                                <label class="layui-form-label">邮箱</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="email" readonly lay-verify="email"
                                           autocomplete="off" placeholder="请输入邮箱" class="layui-input">
                                </div>
                            </div>
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">手机号</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="text" name="mobile" lay-verify="phone"--}}
{{--                                           autocomplete="off" placeholder="请输入手机号" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="layui-form-item">
                                <label class="layui-form-label">密码</label>
                                <div class="layui-input-inline">
                                    <input type="password" name="password" lay-verify="password"
                                           autocomplete="new-password" placeholder="请输入密码" class="layui-input">
                                </div>
                            </div>

{{--                            <div class="layui-form-item layui-form-text">--}}
{{--                                <label class="layui-form-label">备注</label>--}}
{{--                                <div class="layui-input-block">--}}
{{--                                    <textarea name="remark" placeholder="请输入内容" class="layui-textarea"></textarea>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit lay-filter="save">确认</button>
{{--                                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>--}}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="application/javascript">
        layui.use(['table'], function () {

            let table = layui.table
                , form = layui.form;
            form.val("formData",{!! Auth::user('system') !!});

            //监听搜索
            form.on('submit(save)', function (data) {
                let field = data.field;
                axios.post("/system/base/user/update", field)
                    .then(function (response) {
                            if (response.data.status) {
                                axios.post("{{url('system/logout')}}")
                                    .then(function (response) {
                                            console.log(response.data);
                                            if (response.data.status) {
                                                layer.msg('重新登录', {
                                                    offset: '15px'
                                                    , icon: 1
                                                    , time: 1000
                                                }, function () {
                                                    location.href = '{{url("system/login")}}'; //跳转到登入页
                                                });

                                            } else {
                                                layer.msg('请求失败', response.data.msg);
                                            }

                                        }
                                    ).catch(function (err) {

                                });
                                return layer.msg(response.data.msg);

                            }
                            return layer.alert(response.data.msg);
                        }
                    );
            });


        });
    </script>
@endsection
