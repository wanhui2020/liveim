@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">主播会员:</label>
                                <div class="layui-input-inline">
                                    <select name="member_id" lay-search="">
                                        <option value="">选择主播会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">首页分类:</label>
                                <div class="layui-input-block" id="div_project">
                                    @foreach($tags as $key=>$item)
                                        <input type="checkbox" name="tag" lay-filter="tag"
                                               title="{{$item['name']}}" value="{{$item['id']}}">
                                    @endforeach
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">排序:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="sort" lay-verify="required" placeholder="" value="0" autocomplete="off" class="layui-input" value="{{$item['sort']}}">
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

            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {

                    var prostr = '';
                    $("input:checkbox[name='tag']:checked").each(function () { // 遍历name=test的多选框
                        prostr += $(this).val() + ',';
                    });
                    if (prostr == '') {
                        return layer.msg('请至少选择一个标签分类！');
                    }
                    let field = data.field;
                    field.tagstr = prostr;
                    layer.load();
                    axios.post("{{url('system/member/tag/store')}}", field)
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
@stop
