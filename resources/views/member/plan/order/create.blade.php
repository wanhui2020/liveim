@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">邀约会员:</label>
                                <div class="layui-input-inline">
                                    <select name="member_id" lay-verify="required">
                                        <option value="">选择邀约会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">被邀主播:</label>
                                <div class="layui-input-inline">
                                    <select name="to_member_id" lay-filter="select_member" lay-verify="required">
                                        <option value="">选择被邀主播</option>
                                        @foreach($tomember as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['nick_name']}}({{$item['code']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">服务日期:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="service_date" placeholder="请选择服务日期"
                                           autocomplete="off" class="layui-input" id="text_date" lay-verify="required"
                                           placeholder="yyyy-MM-dd">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">服务项目:</label>
                                <div class="layui-input-block" id="div_project">
                                    暂无选项
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">服务费用:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="amount" value="0" id="text_amount" lay-verify="required"
                                           readonly
                                           placeholder=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注说明:</label>
                                <div class="layui-input-block">
                               <textarea placeholder="请输入备注说明" name="remark"
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
        layui.use(['form', 'upload', 'laydate'], function () {
            let $ = layui.$
                , form = layui.form
                , upload = layui.upload
                , laydate = layui.laydate

            var pcounts = 0;

            laydate.render({
                elem: '#text_date'
            });


            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });
            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    if (pcounts == 0) {
                        return layer.msg('请至少选择一项服务项目！');
                    }
                    var prostr = '';
                    $("input:checkbox[name='pro']:checked").each(function () { // 遍历name=test的多选框
                        prostr += $(this).val() + ',';
                    });
                    // $("#hid_project").val(prostr);
                    let field = data.field;
                    field.project = prostr;
                    layer.load();
                    axios.post("{{url('system/member/plan/order/store')}}", field)
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

            form.on('select(select_member)', function (data) {
                let v = data.value;
                layer.load();
                axios.get("{{url('system/member/plan/order/get/projects?mid=')}}" + v)
                    .then(function (response) {
                        layer.closeAll();
                        $("#div_project").html('');
                        var html = '';
                        if (response.data.length == 0) {
                            html = '暂无选项';
                        } else {
                            $.each(response.data, function (key, val) {
                                html += '<input type="checkbox" name="pro" lay-filter="project" title="' + val.project + '" value="' + val.id + '">'; // $("<option>").val(key).text(val.project);
                            });
                        }
                        $("#div_project").html(html);
                        form.render('checkbox');
                    });
            });
            form.on('checkbox(project)', function (data) {
                if (data.elem.checked) {
                    pcounts++;
                } else {
                    pcounts--;
                }
                if (pcounts > 1) {
                    $("#text_amount").val('{{$fee['mul']}}')
                } else {
                    $("#text_amount").val('{{$fee['sin']}}')
                }
            });


        });

    </script>
@stop
