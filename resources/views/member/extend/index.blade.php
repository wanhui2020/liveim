@extends('layouts.base')
@section('content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="aa">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <div class="layui-input-inline" style="margin-right: -11px;">
                            <input type="text" name="key" placeholder="输入关键字" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn " lay-submit lay-filter="search" style="margin-left: -21px;">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                        </button>
                        <button lay-submit lay-filter="refresh" class="layui-btn" style="margin-left: -1px;">重置</button>
                    </div>

                </div>
            </div>
            <div class="layui-card-body">
                {{--<script type="text/html" id="toolbar">--}}
                {{--<div>--}}
                {{--<button class="layui-btn  layui-btn-sm" lay-event="add">添加</button>--}}
                {{--</div>--}}
                {{--</script>--}}
                <table id="lists" lay-filter="lists"></table>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script type="application/javascript">
        layui.use(['table', 'laydate'], function () {
            let table = layui.table, form = layui.form;
            let laydate = layui.laydate;

            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/extend/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员扩展信息'
                , cellMinWidth: 100
                , cols: [[
                    {fixed: 'left', field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {
                        fixed: 'left', field: 'user_name', title: '主播编号', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-'
                            }
                            return d.member.code;
                        }
                    }
                    , {
                        field: 'user_name', title: '主播名称', align: 'center', width: 180, templet: function (d) {
                            if (d.member == null) {
                                return '-'
                            }
                            return d.member.nick_name;
                        }
                    }
                    , {
                        field: 'business_check', title: '商务认证', align: 'center', width: 90, templet: function (d) {
                            let html = '<a style="color: red;">否</a>';
                            if (d.member.business_check === 1) {
                                html = '<a style="color:green;">是</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'user_name', title: '邀请码', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-'
                            }
                            return d.member.invitation_code;
                        }
                    }
                    , {
                        field: 'online_status', title: '在线状态', align: 'center', templet: function (d) {
                            let html = '<a style="color: red;">离线</a>';
                            if (d.member.online_status === 1) {
                                html = '<a style="color:green;">在线</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'vv_busy', title: '是否忙碌', align: 'center', templet: function (d) {
                            let html = '<a style="color: red;">忙碌</a>';
                            if (d.member.vv_busy === 0) {
                                html = '<a style="color:green;">空闲</a>';
                            }
                            return html;
                        }
                    }
                    , {field: 'height', title: '身高(cm)', align: 'center', width: 90}
                    , {field: 'weight', title: '体重(kg)', align: 'center', width: 90}
                    , {field: 'constellation', title: '星座', align: 'center', width: 90}
                    // , {field: 'signature', title: '签名', align: 'center'}
                    , {field: 'hobbies', title: '兴趣爱好', align: 'center',hide:true}
                    , {field: 'address', title: '联系地址', align: 'center',hide:true}
                    , {
                        field: 'is_business', title: '是否开启商务', align: 'center', width: 90, templet: function (d) {
                            let html = '<a style="color: red;">否</a>';
                            if (d.is_business === 1) {
                                html = '<a style="color:green;">是</a>';
                            }
                            return html;
                        }
                    }
                    , {field: 'text_fee', title: '普通消息收费', align: 'center'}
                    , {field: 'voice_fee', title: '语音消息收费', align: 'center'}
                    , {field: 'video_fee', title: '视频消息收费', align: 'center'}
                    , {field: 'picture_view_fee', title: '颜照库收费', align: 'center'}
                    , {field: 'video_view_fee', title: '视频库收费', align: 'center'}
                    , {field: 'coat_fee', title: '换衣收费', align: 'center'}
                    , {field: 'talk_rate', title: '聊天平台分成(%)', align: 'center'}
                    , {field: 'gift_rate', title: '礼物平台分成(%)', align: 'center'}
                    , {field: 'other_rate', title: '其他消费分成(%)', align: 'center'}

                    , {
                        title: '操作', width: 80, align: 'center', templet: function (d) {
                            let html = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>';
                            return html;
                        }
                    }

                ]]
            });

            //监听排序事件
            table.on('sort(lists)', function (obj) {

                table.reload('lists', {
                    initSort: obj
                    , where: {
                        field: obj.field
                        , order: obj.type
                    }
                });
            });

            //监听搜索
            form.on('submit(search)', function (data) {
                let field = data.field;
                //执行重载
                table.reload('lists', {
                    where: field
                });
            });

            //监听重置
            form.on('submit(refresh)', function (data) {
                //表单初始赋值
                form.val('aa', {
                    "key": ""
                    , "start_time": ""
                    , "end_time": ""
                    , "status": ""
                });
                table.reload('lists', {
                    where: ''
                });
            });

            //监听行工具条
            table.on('tool(lists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                let data = obj.data; //获得当前行数据
                let layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                let tr = obj.tr; //获得当前行 tr 的DOM对象

                if (layEvent === 'detail') { //查看
                    //do somehing
                }
                else if (layEvent === 'edit') { //编辑
                    location.href = "{{url('system/member/extend/edit?id=')}}" + data.id;
                    {{--layer.open({--}}
                    {{--type: 2--}}
                    {{--, title: '编辑主播信息'--}}
                    {{--, content: '{{url('system/member/extend/edit?id=')}}' + data.id--}}
                    {{--, maxmin: true--}}
                    {{--, area: ['800px', '400px']--}}
                    {{--, end: function () {--}}
                    {{--tableIns.reload();--}}
                    {{--}--}}
                    {{--});--}}
                }
            });
        });

    </script>
@endsection

