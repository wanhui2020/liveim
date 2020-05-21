@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 450px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">新增会员</div>

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员编号:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="code" lay-verify="required" placeholder="请输入会员编号"
                                           autocomplete="off" class="layui-input" value="{{$code}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">用户名:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="user_name" lay-verify="required" placeholder="请输入用户名"
                                           autocomplete="off" class="layui-input" value="u{{$code}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">登录密码:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="password" lay-verify="required" placeholder="请输入登录密码"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">昵称:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="nick_name" lay-verify="required" placeholder="请输入昵称"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">头像:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="head_pic" placeholder="请上传头像"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">电子邮箱:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="email" placeholder="请输入电子邮箱"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">手机号码:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="mobile" placeholder="请输入手机号码"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">性别:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="sex" value="0" title="男" checked="">
                                    <input type="radio" name="sex" value="1" title="女">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员等级</label>
                                <div class="layui-input-inline">
                                    <select name="level_id">
                                        <option value="">选择会员等级</option>
                                        @foreach($level as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员分组</label>
                                <div class="layui-input-inline">
                                    <select name="group_id">
                                        <option value="">选择会员分组</option>
                                        @foreach($group as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">推荐人编号:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="pid" placeholder="请输入推荐人编号"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">生日:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="birth" placeholder="请选择会员生日"
                                           autocomplete="off" class="layui-input" id="text_birth"
                                           placeholder="yyyy-MM-dd">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">格言:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="maxim" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item ">
                                <div class="layui-input-block">
                                    <input type="button" class="layui-btn" lay-submit lay-filter="formSubmit"
                                           value="确认">
                                    <input type="button" class="layui-btn layui-btn-primary " id="close" value="返回列表">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script type="application/javascript">
        layui.use(['form', 'upload', 'laydate'
        ], function () {
            let $ = layui.$
                , form = layui.form
                , upload = layui.upload
                , laydate = layui.laydate
            //自定义验证规则
            form.verify({
                article_desc: function (value) {
                    layedit.sync(editIndex);
                }
            });
            laydate.render({
                elem: '#text_birth'
            });
            $(document).on('click', '#close', function () {
                location.href = "{{url('system/member/info')}}";

            });
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/info/store')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                location.href = "{{url('system/member/info')}}";
                                return parent.layer.msg(response.data.msg);
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@stop
