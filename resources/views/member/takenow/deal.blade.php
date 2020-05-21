@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id" value="{{$cons->id}}">
                            <input type="hidden" name="member_id" value="{{$cons->member_id}}">
                            <input type="hidden" name="order_no" value="{{$cons->order_no}}">
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现会员:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="member_username"
                                           value="{{$cons->member->code}}-{{$cons->member->nick_name}}" readonly
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现方式:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="way_cn" value="" readonly
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-inline">
                                    <label class="layui-form-label">提现金额:</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="amount" value="" readonly
                                               autocomplete="off" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">手续费:</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="fee_money" value="" readonly
                                               autocomplete="off" class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-inline">
                                    <label class="layui-form-label">实际到账:</label>
                                    <div class="layui-input-inline">
                                        <input type="text" name="real_amount" value="" readonly
                                               autocomplete="off" class="layui-input">
                                    </div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">收款账号:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="account_no" value="" readonly
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>

{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">简介说明:</label>--}}
{{--                                <div class="layui-input-block">--}}
{{--                                    <textarea readonly name="desc" class="layui-textarea"></textarea>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="layui-form-item">
                                <label class="layui-form-label">处理结果:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入处理结果" name="deal_reason"
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">处理状态:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="status" value="1" title="审核通过" checked="">
                                    <input type="radio" name="status" value="2" title="审核拒绝">
                                </div>
                            </div>
                            <div class="layui-form-item ">
                                <div class="layui-input-block">
                                    <input type="button" class="layui-btn" lay-submit lay-filter="formSubmit"
                                           value="确认">
                                    <input type="button" class="layui-btn layui-btn-primary " id="close" value="关闭">
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
            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    let index = parent.layer.getFrameIndex(window.name);
                    layer.load();
                    axios.post("{{url('system/member/takenow/deal/save')}}", field)
                        // .then(function (response) {
                        //     layer.closeAll();
                        //     if (response.data.status) {
                        //         parent.layer.msg(response.data.msg);
                        //         return parent.layer.close(index);
                        //     }
                        //     return layer.msg(response.data.msg);
                        // });
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.code === 1) {
                                parent.layer.msg(response.data.msg);
                                return parent.layer.close(index);
                            }
                            parent.layer.msg(response.data.msg);
                            return parent.layer.close(index);
                        });
                }
            );
        });

    </script>
@endsection
