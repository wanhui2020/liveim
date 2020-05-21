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
                                    <select name="member_id">
                                        <option value="">选择会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">资源类型:</label>
                                <div class="layui-input-block">
                                    <select name="type">
                                        <option value="">选择资源类型</option>
                                        @foreach($type as $key=>$item)
                                            <option value="{{$key}}">{{$item}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">资源路径:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="url" id="text_url" lay-verify="required"
                                           placeholder="请输入资源路径"
                                           autocomplete="off" class="layui-input">
                                    <button type="button" class="layui-btn" id="appPic">
                                        <i class="layui-icon">&#xe67c;</i>上传
                                    </button>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否封面:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="is_cover" value="0" title="否" checked="">
                                    <input type="radio" name="is_cover" value="1" title="是">
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

            layui.upload.render({
                elem: '#appPic' //绑定元素
                , url: '{{url('common/oss/put')}}' //上传接口
                ,accept: 'file' //普通文件
                , before: function (obj) {
                    layui.layer.load();
                }
                , done: function (res) {
                    $("#text_url").val(res.data);
                    layer.closeAll('loading');
                    layer.msg('上传成功')
                }
                , error: function () {
                    layer.alert('上传失败');
                    layer.closeAll('loading');
                }
            });

            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/file/store')}}", field)
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
