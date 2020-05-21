@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="">
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现会员:</label>
                                <div class="layui-input-block">
                                    <select name="member_id" lay-search="">
                                        <option value="">选择会员</option>
                                        <optgroup label="会员">
                                            @foreach($member as $key=>$item)
                                                @if($item['sex']==0)
                                                    <option value="{{$item['id']}}">{{$item['code']}}
                                                        -{{$item['nick_name']}}(￥{{$item['account']['cantx_rmb']}})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="主播">
                                            @foreach($member as $key=>$item)
                                                @if($item['sex']==1)
                                                    <option value="{{$item['id']}}">{{$item['code']}}
                                                        -{{$item['nick_name']}}(￥{{$item['account']['cantx_rmb']}})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </optgroup>

                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现金额:</label>
                                <div class="layui-input-inline">
                                    <input type="number" name="amount" lay-verify="required" placeholder="请输入提现金额"
                                           value=""
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现方式:</label>
                                <div class="layui-input-inline">
                                    <select name="way">
                                        @foreach($way as $key=>$item)
                                            <option value="{{$key}}">{{$item}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现账号:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="account_no" lay-verify="required" placeholder="请输入提现账号"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">账号名称:</label>
                                <div class="layui-input-block">
                                    <input type="text" name="account_name" placeholder="请输入账号名称"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">简介说明:</label>
                                <div class="layui-input-block">
                                    <textarea placeholder="请输入简介说明" name="desc"
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
                    axios.post("{{url('system/member/takenow/store')}}", field)
                        .then(function (response) {
                            layer.closeAll();
                            if (response.data.status) {
                                parent.layer.msg(response.data.msg);
                                return parent.layer.close(index);
                                ;
                            }
                            return layer.msg(response.data.msg);
                        });
                }
            );
        });

    </script>
@stop
