@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <input type="hidden" name="gift_id" id="hid_gift_id">
                            <input type="hidden" id="hid_gift_price">
                            <input type="hidden" name="gift_name" id="hid_gift_name">
                            <div class="layui-form-item">
                                <label class="layui-form-label">赠送会员:</label>
                                <div class="layui-input-inline">
                                    <select name="member_id">
                                        <option value="">选择赠送会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">接收主播:</label>
                                <div class="layui-input-inline">
                                    <select name="to_member_id">
                                        <option value="">选择接收主播会员</option>
                                        @foreach($tomember as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">赠送礼物:</label>
                                <div class="layui-input-block">

                                    <select lay-filter="select_gift">
                                        <option value="">选择礼物</option>
                                        @foreach($gift as $key=>$item)
                                            <option value="{{$item['id']}}-{{$item['gold']}}-{{$item['title']}}">{{$item['title']}}({{$item['gold']}}币)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">赠送数量:</label>
                                <div class="layui-input-block">
                                    <input type="number" id="text_quantity" name="quantity" value="1"
                                           lay-verify="required"
                                           placeholder="请输入赠送数量"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">总价值:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="gold" value="0" id="text_gold" lay-verify="required"
                                           readonly
                                           placeholder="请输入赠送数量"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">后台赠送:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="status" value="0" title="否" checked="">
                                    <input type="radio" name="status" value="1" title="是">
                                </div>
                            </div>
                            <div class="layui-form-item">
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

            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/gift/store')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                parent.layer.msg(response.data.msg);
                                return parent.layer.close(index);
                                ;
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
            form.on('select(select_gift)', function (data) {
                let v = data.value.split('-');
                $("#hid_gift_id").val(v[0]);
                $("#hid_gift_price").val(v[1]);
                $("#hid_gift_name").val(v[2]);
                computTotalGold();

            });

            $("#text_quantity").on('keyup', function () {
                computTotalGold();
            });

            function computTotalGold() {
                var c = $("#text_quantity").val() === '' ? 1 : parseInt($("#text_quantity").val());
                var p = $("#hid_gift_price").val()=== '' ? 0 : parseInt($("#hid_gift_price").val());
                var t = c * p;
                $("#text_gold").val(t);
            }
        });

    </script>
@stop
