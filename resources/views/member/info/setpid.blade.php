@extends('layouts.base')

@section('content')
    <div class="layui-fluid" style="max-height: 450px;">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">设置邀请人</div>

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <input type="hidden" name="member_id" value="{{$member['id']}}">
                            <div class="layui-form-item">
                                <label class="layui-form-label">会员:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="user_name" readonly="readonly"
                                           autocomplete="off" class="layui-input"
                                           value="{{$member['code']}}-{{$member['nick_name']}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">邀请人编号:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="pcode" placeholder="请输入邀请会员编号"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">经纪人编号:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="agent_code" placeholder="请输入经纪人编号"
                                           autocomplete="off" class="layui-input">
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
        layui.use(['form', 'upload', 'laydate'
        ], function () {
            let $ = layui.$
                , form = layui.form
                , upload = layui.upload
            //自定义验证规则
            form.verify({
                article_desc: function (value) {
                    layedit.sync(editIndex);
                }
            });
            // let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                location.href = "{{url('system/member/info')}}";
            });
            // parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/info/setpid/save')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                location.href = "{{url('system/member/info')}}";
                                return parent.layer.msg(response.data.msg);
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@stop
