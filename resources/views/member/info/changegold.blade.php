@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 450px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">会员金币变动</div>

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <input type="hidden" name="member_id" value="{{$member['id']}}">
                            <div class="layui-form-item">
                                <label class="layui-form-label">用户名:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="user_name" readonly="readonly"
                                           autocomplete="off" class="layui-input" value="{{$member['user_name']}}--{{$member['nick_name']}}">
                                </div>
                            </div>
                            {{--<div class="layui-form-item">--}}
                            {{--<label class="layui-form-label">变动账户:</label>--}}
                            {{--<div class="layui-input-block">--}}
                            {{--<input type="radio" name="account" value="0" title="可用金币" checked>--}}
                            {{--<input type="radio" name="account" value="1" title="不可用金币">--}}
                            {{--</div>--}}
                            {{--</div>--}}
                            <div class="layui-form-item">
                                <label class="layui-form-label">变动类型:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="type" value="0" title="增加" checked="">
                                    <input type="radio" name="type" value="1" title="减少">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否可提现:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="is_lock" value="0" title="不可提现" checked="">
                                    <input type="radio" name="is_lock" value="1" title="可提现">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">变动数量:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="quantity" lay-verify="required" placeholder="请输入变动数量"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注说明:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入备注说明" name="remark" class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item" style="display: none;">
                                <label class="layui-form-label">支付状态:</label>
                                <div class="layui-input-inline">
                                    <select name="pay_status">
                                        <option value="0">未支付</option>
                                        <option value="1" selected="selected">已支付</option>
                                    </select>
                                </div>
                            </div>
{{--                            <div class="layui-form-item" type="hidden">--}}
{{--                                <label class="layui-form-label">是否后台:</label>--}}
{{--                                <div class="layui-input-inline" type="hidden">--}}
{{--                                    <select name="is_sys">--}}
{{--                                        <option value="1" selected="selected">是</option>--}}
{{--                                        <option value="0">否</option>--}}
{{--                                    </select>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <input type="hidden" name="is_sys" value="1">
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
            // let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                location.href = "{{url('system/member/info')}}";
            });
            // parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/info/changegold/save')}}", field)
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
