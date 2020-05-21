@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 500px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id" value="{{$cons->id}}">
                            <input type="hidden" name="member_id" value="{{$cons->member_id}}">
                            <div class="layui-form-item">
                                <label class="layui-form-label">主播编号:</label>
                                <div class="layui-form-mid">{{$cons->member->code}}</div>
                                <label class="layui-form-label">主播昵称:</label>
                                <div class="layui-form-mid">{{$cons->member->nick_name}}</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">资源类型:</label>
                                <div class="layui-form-mid">{{$cons->type_cn}}</div>
                                <label class="layui-form-label">是否封面:</label>
                                <div class="layui-form-mid">{{$cons->is_cover_cn}}</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">资源预览:</label>
                                <div class="layui-input-block">
                                    @if ($cons->type === 1)
                                        <img width="200px" height="200px" src="{{$cons->url}}"/>
                                    @else
                                        <video width="200px" height="200px" src="{{$cons->url}}" controls></video>
                                    @endif
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">审核意见:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入审核意见" name="deal_reason"
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">是否通过:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="status" value="1" title="通过" checked="">
                                    <input type="radio" name="status" value="2" title="拒绝">
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
            //自定义验证规则
            form.verify({
                article_desc: function (value) {
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
                    axios.post("{{url('system/member/file/deal/save')}}", field)
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
