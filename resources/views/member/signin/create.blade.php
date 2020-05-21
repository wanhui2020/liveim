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
                                    <select name="member_id" lay-search="" lay-verify="required">
                                        <option value="">选择会员</option>
                                        @foreach($member as $key=>$item)
                                            <option value="{{$item['id']}}">{{$item['code']}} - {{$item['nick_name']}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">奖励:</label>
                                <div class="layui-input-block">
                                    <input type="number" name="award" lay-verify="required" placeholder="请输入奖励数量"
                                           value="0"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">补签日期:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="bq_date" placeholder="请选择补签日期,不填默认签到当天"
                                           autocomplete="off" class="layui-input" id="text_bqdate"
                                           placeholder="yyyy-MM-dd">
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
        layui.use(['form', 'laydate'], function () {
            let $ = layui.$
                , form = layui.form
                , laydate = layui.laydate
            let index = parent.layer.getFrameIndex(window.name);
            $(document).on('click', '#close', function () {
                parent.layer.close(index);
            });

            laydate.render({
                elem: '#text_bqdate'
                , max: minDate()
            });

            // 设置最小可选的日期
            function minDate() {
                var now = new Date();
                var preDate = new Date(now.getTime() - 24 * 60 * 60 * 1000); //前一天
                return preDate.getFullYear() + "-" + (preDate.getMonth() + 1) + "-" + preDate.getDate();
            }

            parent.layer.iframeAuto(index);
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/member/signin/store')}}", field)
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
