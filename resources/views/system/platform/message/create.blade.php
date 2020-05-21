@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">消息类型:</label>
                                <div class="layui-input-inline">
                                    <select name="type" lay-verify="required">
                                        @foreach($type as $key=>$item)
                                            <option value="{{$key}}">{{$item}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">消息内容</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入消息内容" name="content" class="layui-textarea"  lay-verify="required"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">接收会员:</label>
                                <div class="layui-input-inline">
                                    <select name="to_id" lay-verify="required"  lay-search="" >
                                        <option value="">查找接收会员</option>
                                        <option value="0">所有会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['code']}} - {{$item['nick_name']}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">相关会员:</label>
                                <div class="layui-input-inline">
                                    <select name="member_id" lay-search="">
                                        <option value="">查找相关会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['code']}} - {{$item['nick_name']}}
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
                , upload = layui.upload;
            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);

            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/platform/message/store')}}", field)
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
