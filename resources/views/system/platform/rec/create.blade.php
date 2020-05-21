@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">类型:</label>
                                <div class="layui-input-block">
                                    <select name="type" lay-filter="select_type">
                                        @foreach($typeList as $key=>$item)
                                            <option value="{{$key}}">{{$item}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">名称:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="name" lay-verify="required" placeholder="请输入名称"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">价格:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="cost" lay-verify="required" placeholder="请输入现价格"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(￥)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">原价格:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="old_cost" lay-verify="required" placeholder="请输入原价格"
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux">(￥)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">数量:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="quantity" lay-verify="required" placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-form-mid layui-word-aux quantityLabel">(金币)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员赠送:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="give" placeholder="请输入普通会员赠送"
                                           autocomplete="off" class="layui-input" value="0">
                                </div>
                                <div class="layui-form-mid layui-word-aux quantityLabel">(金币)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">VIP赠送:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="vip_give" placeholder="请输入VIP赠送"
                                           autocomplete="off" class="layui-input" value="0">
                                </div>
                                <div class="layui-form-mid layui-word-aux quantityLabel">(金币)</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注说明:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入备注说明" name="remark" class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">排序:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="sort" lay-verify="required" placeholder="请输入排序数值"
                                           autocomplete="off" class="layui-input" value="0">
                                </div>
                                <div class="layui-form-mid layui-word-aux">数值越大越靠前</div>

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

            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/platform/rec/store')}}", field)
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
            form.on('select(select_type)', function (data) {
                let v = data.value;
                if (v == 2) {
                    $(".quantityLabel").text('(天数)');
                }else{
                    $(".quantityLabel").text('(金币)');
                }
                console.log(data.value);
            });
        });

    </script>
@stop
