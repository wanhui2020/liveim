@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 450px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">赠送会员VIP</div>
                    <div class="layui-card-body" pad15>
                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id" value="{{$account->id}}">
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="user_name"
                                           value="{{$account->member->code}}-{{$account->member->nick_name}}"
                                           autocomplete="off" class="layui-input" readonly>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">vip等级:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="vip_level" placeholder="" value="{{$account->vip_level}}"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">vip到期日期:</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="vip_expire_date" placeholder="" id="text_vip"
                                           lay-verify="required"
                                           value="{{$account->vip_expire_date}}"
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
        layui.use(['form', 'upload', 'laydate'], function () {
            let $ = layui.$
                , form = layui.form
                , laydate = layui.laydate

            //自定义验证规则
            form.verify({
                article_desc: function (value) {
                    layedit.sync(editIndex);
                }
            });
            laydate.render({
                elem: '#text_vip'
            });
            // let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                location.href = "{{url('system/member/account')}}";
            });
            // parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/account/update')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                location.href = "{{url('system/member/account')}}";
                                return parent.layer.msg(response.data.msg);
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@endsection
