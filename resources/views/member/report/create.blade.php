@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">举报会员:</label>
                                <div class="layui-input-inline">
                                    <select name="member_id">
                                        <option value="">选择举报会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">被举报会员:</label>
                                <div class="layui-input-inline">
                                    <select name="to_member_id">
                                        <option value="">选择被举报会员</option>
                                        @foreach($tomember as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">举报类型:</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="type" list="typeList" lay-verify="required"
                                           placeholder="请输入或选择举报类型"
                                           autocomplete="off" class="layui-input">
                                    <datalist id="typeList">
                                        @foreach($type as $key=>$item)
                                            <option value="{{$item['value']}}">{{$item['value']}}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">举报理由:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入举报理由" lay-verify="required" name="explain"
                                              class="layui-textarea"></textarea>
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
                    axios.post("{{url('system/member/report/store')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                parent.layer.msg(response.data.msg);
                                return parent.layer.close(index);;
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@stop
