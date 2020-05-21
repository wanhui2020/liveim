@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">自拍认证审核</div>

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData">
                            <input type="hidden" name="id" value="{{$cons->id}}">
                            <input type="hidden" name="member_id" value="{{$cons->member_id}}">
                            <input type="hidden" name="pic" value="{{$cons->pic}}">
                            <div class="layui-form-item">
                                <label class="layui-form-label">所属会员:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="user_name"
                                           value="{{$cons->member->code}} - {{$cons->member->nick_name}}" readonly
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">自拍照:</label>
                                <div class="layui-input-block">
                                    <img width="200px" height="200px" src="{{$cons->pic}}"/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">审核原因:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入审核原因" name="deal_reason"
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
                                <label class="layui-form-label">是否推荐:</label>
                                <div class="layui-input-block">
                                    <input type="radio" name="is_recommend" value="0" title="不进推荐" checked="">
                                    <input type="radio" name="is_recommend" value="1" title="进入推荐">
                                </div>
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
            $(document).on('click', '#close', function () {
                location.href = "{{url('system/member/selfie')}}";

            });
            form.on('submit(formSubmit)', function (data) {
                    var prostr = '';
                    $("input:checkbox[name='tag']:checked").each(function () { // 遍历name=test的多选框
                        prostr += $(this).val() + ',';
                    });
                    let field = data.field;
                    field.tagstr = prostr;
                    layer.load();
                    axios.post("{{url('system/member/selfie/deal/save')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                location.href = "{{url('system/member/selfie')}}";
                                return parent.layer.msg(response.data.msg);
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@endsection
