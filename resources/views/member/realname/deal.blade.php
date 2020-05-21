@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 500px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">实名认证审核</div>

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id" value="{{$cons->id}}">
                            <input type="hidden" name="member_id" value="{{$cons->member_id}}">
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员编号:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="user_name" value="{{$cons->member->user_name}}" readonly
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">真实姓名:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="name" lay-verify="required" placeholder="请输入真实姓名" readonly
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">身份证号:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="cert_no" lay-verify="required" placeholder="请输入真身份证号"
                                           readonly
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">身份证正面:</label>
                                <div class="layui-input-block">
                                    <img width="200px" height="200px" src="{{$cons->cert_zm}}"/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">身份证反面:</label>
                                <div class="layui-input-block">
                                    <img width="200px" height="200px" src="{{$cons->cert_fm}}"/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">自拍照:</label>
                                <div class="layui-input-block">
                                    <img width="200px" height="200px" src="{{$cons->selfie_pic}}"/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">审核原因:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入审核原因" name="deal_reason"
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否通过:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="status" value="1" title="通过" checked="">
                                    <input type="radio" name="status" value="2" title="拒绝">
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
        layui.use(['form', 'upload'], function () {
            let $ = layui.$
                , form = layui.form
                , upload = layui.upload
            //自定义验证规则
            form.verify({
                article_desc: function (value) {
                    layedit.sync(editIndex);
                }
            });
            form.val("formData",{!! $cons !!});
            // let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                // parent.layer.close(index);
                location.href = "{{url('system/member/realname')}}";
            });
            // parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/realname/deal/save')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                location.href = "{{url('system/member/realname')}}";
                                return parent.layer.msg(response.data.msg);
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@endsection
