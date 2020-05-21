@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">选择会员:</label>
                                <div class="layui-input-inline">
                                    <select name="member_id" lay-search="">
                                        <option value="">选择会员</option>
                                        <optgroup label="会员">
                                            @foreach($member as $key=>$item)
                                                @if($item['sex']==0)
                                                    <option value="{{$item['id']}}">{{$item['code']}}-{{$item['nick_name']}}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="主播">
                                            @foreach($member as $key=>$item)
                                                @if($item['sex']==1)
                                                    <option value="{{$item['id']}}">{{$item['code']}}-{{$item['nick_name']}}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </optgroup>

                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">兑换金币:</label>
                                <div class="layui-input-block">
                                    <input type="text" id="text_gold" name="gold" lay-verify="required"
                                           placeholder="请输入获得积分数量"
                                           value="" autocomplete="off" class="layui-input" list="dhlist">
                                    <datalist id="dhlist">
                                        @foreach($dataList as $key=>$item)
                                            <option value="{{$item['quantity']}}">{{$item['quantity']}}币 —>
                                                ￥{{$item['cost']}}
                                            </option>
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">实得金额:</label>
                                <div class="layui-input-block">
                                    <input type="number" id="text_rmb" name="rmb" lay-verify="required" placeholder=""
                                           readonly
                                           value="0"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注说明:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入备注说明" name="remark" class="layui-textarea"></textarea>
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
        layui.use(['form'], function () {
            let $ = layui.$
                , form = layui.form

            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });

            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/exchange/store')}}", field)
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
            $("#text_gold").on('change', function () {
                var rate = '{{$config->dh_rate}}'; //兑换比例
                var dh_min = '{{$config->dh_min}}'; //最低兑换
                var gold = $(this).val();
                if (gold < dh_min) {
                    $("#text_rmb").val(0);
                    layer.msg('最低兑换' + dh_min + '金币！');
                    $("#text_gold").focus();
                    return;
                }
                var rmb = gold / rate;
                $("#text_rmb").val(rmb);
            });
        });

    </script>
@stop
