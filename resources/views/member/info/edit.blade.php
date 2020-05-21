@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 450px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">

                <div class="layui-card">
                    <div class="layui-card-header">编辑会员信息</div>

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id">

                            <div class="layui-form-item">
                                <label class="layui-form-label">用户名:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="user_name" lay-verify="required" placeholder="请输入用户名"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">登录密码:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="password" placeholder="不修改密码请留空"
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
                                    <input type="text" id="text_head_pic" name="head_pic" placeholder="请上传头像"
                                           autocomplete="off" class="layui-input">
                                    <button type="button" class="layui-btn" id="appPic">
                                        <i class="layui-icon">&#xe67c;</i>上传
                                    </button>
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
                                    <input type="radio" name="sex" value="0"
                                           title="男" {{$memberInfo->sex==0?'checked':''}}>
                                    <input type="radio" name="sex" value="1"
                                           title="女" {{$memberInfo->sex==1?'checked':''}}>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">代理商:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="is_agent" value="1"
                                           title="是" {{$memberInfo->is_agent==1?'checked':''}}>
                                    <input type="radio" name="is_agent" value="0"
                                           title="否" {{$memberInfo->is_agent==0?'checked':''}}>
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
                                <label class="layui-form-label">魅力等级:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="meili" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">壕气等级:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="haoqi" placeholder=""
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
                            <div class="layui-form-item">
                                <label class="layui-form-label">推广短链接:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="links" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">在线状态:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="online_status" value="0"
                                           title="离线" {{$memberInfo->online_status==0?'checked':''}}>
                                    <input type="radio" name="online_status" value="1"
                                           title="在线" {{$memberInfo->online_status==1?'checked':''}}>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否忙碌:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="vv_busy" value="0"
                                           title="空闲" {{$memberInfo->vv_busy==0?'checked':''}}>
                                    <input type="radio" name="vv_busy" value="1"
                                           title="忙碌" {{$memberInfo->vv_busy==1?'checked':''}}>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">自拍认证:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="selfie_check" value="0"
                                           title="否" {{$memberInfo->selfie_check==0?'checked':''}}>
                                    <input type="radio" name="selfie_check" value="1"
                                           title="是" {{$memberInfo->selfie_check==1?'checked':''}}>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">商务认证:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="business_check" value="0"
                                           title="否" {{$memberInfo->business_check==0?'checked':''}}>
                                    <input type="radio" name="business_check" value="1"
                                           title="是" {{$memberInfo->business_check==1?'checked':''}}>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">实名认证:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="realname_check" value="0"
                                           title="否" {{$memberInfo->realname_check==0?'checked':''}}>
                                    <input type="radio" name="realname_check" value="1"
                                           title="是" {{$memberInfo->realname_check==1?'checked':''}}>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否推荐:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="is_recommend" value="0"
                                           title="否" {{$memberInfo->is_recommend==0?'checked':''}}>
                                    <input type="radio" name="is_recommend" value="1"
                                           title="是" {{$memberInfo->is_recommend==1?'checked':''}}>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">排序:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="sort" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">数字越大越靠前</div>
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
        layui.use(['form', 'upload', 'laydate'], function () {
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

            layui.upload.render({
                elem: '#appPic' //绑定元素
                , url: '{{url('common/oss/put')}}' //上传接口
                , accept: 'file' //普通文件
                , before: function (obj) {
                    layui.layer.load();
                }
                , done: function (res) {
                    $("#text_head_pic").val(res.data);
                    layer.closeAll('loading');
                    layer.msg('上传成功')
                }
                , error: function () {
                    layer.alert('上传失败');
                    layer.closeAll('loading');
                }
            });


            form.val("formData",{!! $memberInfo !!});
            // let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                // parent.layer.close(index);
                location.href = "{{url('system/member/info')}}";
            });
            // parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/info/update')}}", field)
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
@endsection
