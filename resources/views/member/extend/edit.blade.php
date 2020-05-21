@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 450px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">编辑主播信息</div>
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
                            <div class="layui-form-item">
                                <label class="layui-form-label">身高:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="height" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(CM)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">体重:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="weight" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(KG)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">签名:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="signature" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">联系地址:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="address" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">兴趣爱好:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="hobbies" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">星座:</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="constellation" placeholder="" list="xzlist"
                                           autocomplete="off" class="layui-input">
                                    <datalist id="xzlist">
                                        <option>白羊座</option>
                                        <option>金牛座</option>
                                        <option>双子座</option>
                                        <option>巨蟹座</option>
                                        <option>狮子座</option>
                                        <option>处女座</option>
                                        <option>天秤座</option>
                                        <option>天蝎座</option>
                                        <option>射手座</option>
                                        <option>摩羯座</option>
                                        <option>水瓶座</option>
                                        <option>双鱼座</option>
                                    </datalist>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">开启商务:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="is_business" value="0"
                                           title="否" {{$cons->is_business==0?'checked':''}}>
                                    <input type="radio" name="is_business" value="1"
                                           title="是" {{$cons->is_business==1?'checked':''}}>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">聊天平台分成:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="talk_rate" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(%)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">礼物平台分成占比:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="gift_rate" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(%)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">其他消费分成占比:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="other_rate" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(%)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">普通消息收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="text_fee" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(币)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">语音消息收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="voice_fee" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(币)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">视频消息收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="video_fee" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(币)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">颜照库收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="picture_view_fee" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(币)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">视频库收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="video_view_fee" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(币)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">换衣收费:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="coat_fee" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(币)</div>
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
            //自定义验证规则
            form.verify({
                article_desc: function (value) {
                    layedit.sync(editIndex);
                }
            });
            form.val("formData",{!! $cons !!});
            // let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                location.href = "{{url('system/member/extend')}}";

            });
            // parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/extend/update')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                location.href = "{{url('system/member/extend')}}";
                                return parent.layer.close(index);
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@endsection
