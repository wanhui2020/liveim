@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 500px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form">
                            <input type="hidden" name="id" value="{{$cons->id}}">
                            <input type="hidden" name="member_id" value="{{$cons->member_id}}">
                            <div class="layui-form-item">
                                <label class="layui-form-label">主播:</label>
                                <div class="layui-form-mid">{{$cons->member->code}} - {{$cons->member->nick_name}}</div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">衣服标题:</label>
                                <div class="layui-form-mid"><b>{{$cons->title}}</b></div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">衣服预览:</label>
                                <div class="layui-input-block">
                                    <img width="300px" height="300px" src="{{$cons->url}}"/>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">花费金币:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="gold" lay-verify="required" readonly
                                           autocomplete="off" class="layui-input"
                                           value="{{$cons->member->extend->coat_fee}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">选择会员:</label>
                                <div class="layui-input-inline">
                                    <select name="view_member_id" lay-verify="required">
                                        <option value="">选择发起会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
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
            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    let index = parent.layer.getFrameIndex(window.name);
                    layer.load();
                    axios.post("{{url('system/member/coat/addorder/save')}}", field)
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
