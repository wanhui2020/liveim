@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id">
                            <div class="layui-form-item">
                                <label class="layui-form-label">类型:</label>
                                <div class="layui-input-block">
                                    <select name="type" lay-filter="select_type" id="select_type">
                                        @foreach($typeList as $key=>$item)
                                            <option value="{{$key}}">{{$item}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">类型:</label>
                                <div class="layui-input-block">
                                    <select name="type" lay-filter="select_type" id="select_type">
                                        @foreach($typeList as $key=>$item)
                                            <option value="{{$key}}">{{$item}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">描述:</label>
                                <div class="layui-input-block">
                                    <select name="desc" id="select_desc">
                                        @foreach($descList as $key=>$item)
                                            <option value="{{$key}}">{{$item}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">获得积分:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="score" lay-verify="required" placeholder="请输入获得积分"
                                           value="0"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注说明:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="remark" lay-verify="required" placeholder="请输入规则值"
                                           value=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item ">
                                <div class="layui-input-block">
                                    <input type="button" class="layui-btn" lay-submit lay-filter="formSubmit" value="确认">
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
            //自定义验证规则
            form.verify({
                article_desc: function(value){
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
                    axios.post("{{url('system/member/score/rule/update')}}", field)
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

            //下拉框选择事件
            form.on('select(select_type)', function (data) {
                let v = data.value;
                layer.load();
                axios.get("{{url('system/member/score/rule/get/desc?type=')}}" + v)
                    .then(function (response) {
                        layer.closeAll();
                        $("#select_desc").html('');
                        $.each(response.data, function (key, val) {
                            var option1 = $("<option>").val(key).text(val);
                            $("#select_desc").append(option1);
                            form.render('select');
                        });
                        $("#select_desc").get(0).selectedIndex = 0;
                    });
            });


        });

    </script>
@endsection
