@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 450px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">编辑会员账户信息</div>

                    <div class="layui-card-body" pad15>
                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id">
                            <input type="hidden" name="member_id">
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="user_name"
                                           value="{{$cons->member->code}}-{{$cons->member->nick_name}}"
                                           autocomplete="off" class="layui-input" readonly>
                                </div>
                            </div>
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">可用金币:</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="surplus_gold" placeholder=""--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">不可用金币:</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="notuse_gold" placeholder=""--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">余额:</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="surplus_rmb" placeholder=""--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">不可用余额:</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="notuse_rmb" placeholder=""--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">累计消费金币:</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="total_consume" placeholder=""--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">累计收益金币:</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="total_income" placeholder=""--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">后台添加金币:</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="sys_plus" placeholder=""--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">后台扣除金币:</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="sys_minus" placeholder=""--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="layui-form-item">
                                <label class="layui-form-label">积分:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="score" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">魅力积分:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="ml_score" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">富豪积分:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="fh_score" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">签到天数:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="sign_days" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">补签次数:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="bq_count" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">被访问次数:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="visit_count" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">连续登录天数:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="lx_login_days" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">最大连续登录天数:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="lx_login_max_days" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">普通消息累计收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="text_charge" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">语音消息累计收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="voice_charge" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">视频消息累计收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="video_charge" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">颜照库累计收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="picture_view_charge" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">视频库累计收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="video_view_charge" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">vip等级:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="vip_level" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">vip到期日期:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="vip_expire_date" placeholder="" id="text_vip"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">收到礼物数量:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="gift_count" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">信誉评分:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="xy_score" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">满意度评分:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="myd_score" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">颜值评分:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="yz_score" placeholder=""
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
            form.val("formData",{!! $cons !!});
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
