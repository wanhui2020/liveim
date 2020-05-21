@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">平台参数设置</div>

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData" wid100>
                            <div class="layui-form-item">
                                <label class="layui-form-label">余额不足提前</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="yebz_remind" lay-verify="required"
                                           placeholder="请输入余额不足提前N秒提示"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">秒提示</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">平台费率</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="rate" lay-verify="required" placeholder="请输入平台费率"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">赠送礼物分成</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="gift_rate" lay-verify="required" placeholder="请输入赠送礼物分成"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">补签扣费</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="bk_kf" placeholder="请输入补签扣费"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">最低兑换金币</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="dh_min" lay-verify="required" placeholder="请输入最低兑换金币数量"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">金币兑换比例</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="dh_rate" lay-verify="required" placeholder="请输入金币兑换比例"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现最低限额</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="tx_min" lay-verify="required" placeholder="请输入提现最低限额"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现手续费率</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="tx_rate" lay-verify="required" placeholder="请输入提现手续费率"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现单日</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="tx_nofee_count" lay-verify="required"
                                           placeholder="请输入提现单日免手续费笔数"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">笔免手续费</div>

                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">单日最高提现</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="tx_max_count" lay-verify="required"
                                           placeholder="请输入单日最高提现次数"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">次</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">单日最高提现额</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="tx_max_amount" lay-verify="required"
                                           placeholder="请输入单日最高提现金额"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">金币打赏分成</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="ds_rate" lay-verify="required" placeholder="请输入金币打赏分成比例"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">信誉打赏条件</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="pj_dstj" lay-verify="required"
                                           placeholder="请输入信誉评价打赏金币条件"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">注册赠送</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="reg_give_gold" lay-verify="required"
                                           placeholder="请输入注册赠送金币"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（金币）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">邀请注册赠送</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="yqzc_give_gold" lay-verify="required"
                                           placeholder="请输入注册赠送金币"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（金币）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">下级充值奖励</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="invite_rate" lay-verify="required"
                                           placeholder="请输入邀请用户充值奖励占比"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">下级视频礼物奖励</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="invite_gift_rate" lay-verify="required"
                                           placeholder="请输入邀下级视频礼物奖励"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
{{--                            <div class="layui-form-item">--}}
{{--                                <label class="layui-form-label">间接下级充值奖励</label>--}}
{{--                                <div class="layui-input-inline">--}}
{{--                                    <input type="number" name="consume_rate" lay-verify="required"--}}
{{--                                           placeholder="请输入间接下级用户充值奖励占比"--}}
{{--                                           autocomplete="off" class="layui-input">--}}
{{--                                </div>--}}
{{--                                <div class="layui-form-mid layui-word-aux">（%）</div>--}}
{{--                            </div>--}}
                            <div class="layui-form-item">
                                <label class="layui-form-label">下级商务收益奖励</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="yield_rate" lay-verify="required"
                                           placeholder="请输入下级商务收益奖励占比"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">自拍认证奖励</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="selfie_check_award" lay-verify="required"
                                           placeholder="请输入自拍认证奖励金币"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（金币）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">换衣收益分成</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="hy_rate" lay-verify="required" placeholder="请输入换衣收益分成"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员文本消息前</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="free_text" lay-verify="required" placeholder="请输入条数"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">条免费</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">商务平台分成</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="business_rate" lay-verify="required"
                                           placeholder="请输入商务平台分成占比"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（%）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">商务单项收费</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="business_sin_fee" lay-verify="required"
                                           placeholder="请输入商务选择单项收费"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（元）</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">商务多项收费</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="business_mul_fee" lay-verify="required"
                                           placeholder="请输入商务选择多项收费"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">（元）</div>
                            </div>
                            <input type="hidden" name="id">
                            <div class="layui-form-item ">
                                <div class="layui-input-block">
                                    <input type="button" class="layui-btn" lay-submit lay-filter="formSubmit"
                                           value="确认">
                                    {{--<input type="button" class="layui-btn layui-btn-primary " id="close" value="关闭">--}}
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
                , upload = layui.upload

            form.val("formData",{!! $config !!});

            //时间选择器
            laydate.render({
                elem: '#autocolse_at'
                , type: 'time'
            });
            laydate.render({
                elem: '#deferred_at'
                , type: 'time'
            });
            laydate.render({
                elem: '#entrust_end'
                , type: 'time'
            });
            laydate.render({
                elem: '#entrust_start'
                , type: 'time'
            });
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/platform/config/update')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                parent.layer.msg(response.data.msg);
                                return parent.layer.close(index);
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@endsection
