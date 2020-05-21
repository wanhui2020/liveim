@extends('layouts.base')

@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">系统参数</div>

                    <div class="layui-card-body" pad15>

                        <div class="layui-form" lay-filter="formData" wid100>
                            <div class="layui-form-item">
                                <label class="layui-form-label">系统名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="name" lay-verify="required" placeholder="系统名称"
                                           autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">安卓版本号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="version" placeholder="安卓版本号" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">IOS版本号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="ios_version" placeholder="IOS版本号" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">安卓下载地址</label>
                                <div class="layui-input-block">
                                    <input type="text" name="android_down" placeholder="安卓下载地址" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">苹果下载地址</label>
                                <div class="layui-input-block">
                                    <input type="text" name="ios_down" placeholder="苹果下载地址" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">APP图标</label>
                                <div class="layui-input-block">
                                    <input type="text" name="app_pic" placeholder="APP图标" autocomplete="off"
                                           class="layui-input">
                                    <button type="button" class="layui-btn" id="appPic">
                                        <i class="layui-icon">&#xe67c;</i>上传
                                    </button>

                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">平台域名</label>
                                <div class="layui-input-block">
                                    <input type="text" name="domain" placeholder="平台域名" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备案号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="ba_no" placeholder="备案号" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">站点底部</label>
                                <div class="layui-input-block">
                                    <input type="text" name="footer" placeholder="站点底部" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">公司名称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="cp_name" placeholder="公司名称" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">联系地址</label>
                                <div class="layui-input-block">
                                    <input type="text" name="address" placeholder="联系地址" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">客服热线</label>
                                <div class="layui-input-block">
                                    <input type="text" name="tel" placeholder="客服热线" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">客服微信</label>
                                <div class="layui-input-block">
                                    <input type="text" name="weixin" placeholder="客服微信" autocomplete="off"
                                           class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">首次注册欢迎语</label>
                                <div class="layui-input-block">
                                    <textarea name="reg_welcome" placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">服务协议</label>
                                <div class="layui-input-block">
                                    <textarea id="fwxy" lay-verify="article_desc" name="fwxy" placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">VIP说明</label>
                                <div class="layui-input-block">
                                    <textarea id="vip_explain" lay-verify="article_desc" name="vip_explain"
                                              placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">关于我们</label>
                                <div class="layui-input-block">
                                    <textarea id="about_us" lay-verify="article_desc" name="about_us" placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">温馨提示</label>
                                <div class="layui-input-block">
                                    <textarea id="warm_prompt" lay-verify="article_desc" name="warm_prompt"
                                              placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">VIP特权说明</label>
                                <div class="layui-input-block">
                                    <textarea id="vip_privilege" lay-verify="vip_privilege" name="vip_privilege"
                                              placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">微游说明</label>
                                <div class="layui-input-block">
                                    <textarea id="app_explain" lay-verify="article_desc" name="app_explain"
                                              placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">提现说明</label>
                                <div class="layui-input-block">
                                    <textarea id="takenow_explain" lay-verify="article_desc" name="takenow_explain"
                                              placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">关键字过滤</label>
                                <div class="layui-input-block">
                                    <textarea id="keyword"  name="keyword"
                                              placeholder=""
                                              class="layui-textarea"></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="id">
                            <div class="layui-form-item ">
                                <div class="layui-input-block">
                                    <input type="button" class="layui-btn" lay-submit lay-filter="formSubmit"
                                           value="确认">
                                    {{--<input type="button" class="layui-btn layui-btn-primary " id="close" value="关闭">--}}
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
        layui.use(['form', 'upload', 'laydate', 'layedit'], function () {
            let $ = layui.$
                , form = layui.form
                , laydate = layui.laydate
                , upload = layui.upload
                , layedit = layui.layedit

            form.val("formData",{!! $config !!});

            layui.upload.render({
                elem: '#appPic' //绑定元素
                , url: '{{url('common/oss/put')}}' //上传接口
                , accept: 'file' //普通文件
                , before: function (obj) {
                    layui.layer.load();
                }
                , done: function (res) {
                    console.log(res);
                    // self.apply.android_url = res.src;
                    layer.closeAll('loading');
                    layer.msg('上传成功')
                }
                , error: function () {
                    layer.alert('上传失败');
                    layer.closeAll('loading');
                }
            });


            //创建一个编辑器
            var fwxyEdit = layedit.build('fwxy');
            var vip_explain_Edit = layedit.build('vip_explain');
            var about_us_Edit = layedit.build('about_us');
            var warm_prompt_Edit = layedit.build('warm_prompt');
            var vip_privilege = layedit.build('vip_privilege');
            var app_explain = layedit.build('app_explain');
            var takenow_explain = layedit.build('takenow_explain');
            form.verify({
                article_desc: function (value) {
                    layedit.sync(fwxyEdit);
                    layedit.sync(vip_explain_Edit);
                    layedit.sync(about_us_Edit);
                    layedit.sync(vip_privilege);
                    layedit.sync(app_explain);
                    layedit.sync(takenow_explain);
                    layedit.sync(warm_prompt_Edit);
                }
            });

            //时间选择器
            laydate.render({
                elem: '#autocolse_at'
                , type: 'time'
            });
            laydate.render({
                elem: '#deferred_at'
                , type: 'time'
            });
            laydate.render({
                elem: '#entrust_end'
                , type: 'time'
            });
            laydate.render({
                elem: '#entrust_start'
                , type: 'time'
            });
            form.on('submit(formSubmit)', function (data) {
                    let field = data.field;
                    layer.load();
                    axios.post("{{url('system/base/config/update')}}", field)
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
