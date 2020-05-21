@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id">
                            <div class="layui-form-item" >
                                <label class="layui-form-label">Level:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="lvl" lay-verify="required" placeholder="请输入级别" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item" >
                                <label class="layui-form-label">名称:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="name" lay-verify="required" placeholder="请输入名称" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            {{--<div class="layui-form-item" >--}}
                                {{--<label class="layui-form-label">范围下限:</label>--}}
                                {{--<div class="layui-input-block">--}}
                                    {{--<input type="number" name="min_score" lay-verify="required" placeholder="达级下限" autocomplete="off" class="layui-input">--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            <div class="layui-form-item" >
                                <label class="layui-form-label">范围上限:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="max_score" lay-verify="required" placeholder="达级上限" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item" >
                                <label class="layui-form-label">备注说明:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入备注说明" name="remark" class="layui-textarea"></textarea>
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
        layui.use(['form', 'upload'], function () {
            let $ = layui.$
                , form = layui.form
                , upload = layui.upload
            //自定义验证规则
            form.verify({
                article_desc: function(value){
                    layedit.sync(editIndex);
                }
            });
            form.val("formData",{!! $level !!});
            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    let index = parent.layer.getFrameIndex(window.name);
                    layer.load();
                    axios.post("{{url('system/member/level/update')}}", field)
                        .then(function (response) {
                            parent.layer.closeAll();
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
