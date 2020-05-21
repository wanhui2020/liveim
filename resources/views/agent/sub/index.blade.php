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
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="sex">
                                <option value="">全部性别</option>
                                <option value="0">男</option>
                                <option value="1">女</option>
                            </select>
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
                , url: '{{url('/agent/sub/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员信息'
                , cellMinWidth: 100
                , cols: [[
                    {fixed: 'left', field: 'no', title: '序号', type: 'numbers', align: 'center', width: 40}
                    , {
                        fixed: 'left',
                        field: 'head_pic',
                        title: '头像',
                        align: 'center',
                        width: 80,
                        templet: function (d) {
                            if (d.head_pic == null) {
                                return '';
                            }
                            return '<img style="width:40px;height: 40px;" src="' + d.head_pic + '"></img>';
                        }
                    }
                    , {
                        fixed: 'left', field: 'user_name', title: '会员编号/昵称', width:120, templet: function (d) {

                            return d.code + '<br>' + d.nick_name;
                        }
                    }
                    , {
                        fixed: 'left', field: 'sex', title: '性别', align: 'center', width: 60, templet: function (d) {
                            if (d.sex == 0) {
                                return '<a style="color:black">男</a>';
                            }
                            return '<a style="color:red">女</a>'
                        }
                    }
                    , {
                        field: 'online_status', title: '在线状态', align: 'center', width: 86, templet: function (d) {
                            let html = '<a style="color: red;">离线</a>';
                            if (d.online_status === 1) {
                                html = '<a style="color:green;">在线</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'vv_busy', title: '是否忙碌', align: 'center', width: 86, templet: function (d) {
                            let html = '<a style="color: red;">忙碌</a>';
                            if (d.vv_busy === 0) {
                                html = '<a style="color:green;">空闲</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'pid', title: '推荐人', align: 'left', width: 120, templet: function (d) {
                            if (d.parent == null) {
                                return '-';
                            }
                            return d.parent.code + '<br>' + d.parent.nick_name;
                        }
                    }
                    , {
                        field: 'pid', title: '推荐方式', align: 'center', width: 120, templet: function (d) {
                            if (d.pid == d.agent_id) {
                                return '直接推荐';
                            }
                            return '间接推荐';
                        }
                    }
                    , {field: 'mobile', title: '手机号', width: 100, align: 'center'}
                    , {
                        field: 'login_time', title: '上次登录时间', width: 160, align: 'center', templet: function (d) {
                            if (d.lastlogin == null) {
                                return '';
                            }
                            let html = d.lastlogin[0].login_time;
                            return html;
                        }
                    }
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        field: 'status', title: '状态', align: 'center', width: 80,
                        templet: function (d) {
                            let html = '<a style="color: red;">禁用</a>';
                            if (d.status === 1) {
                                html = '<a style="color:green;">正常</a>';
                            }
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

            //监听工具栏事件
            table.on('toolbar(lists)', function (obj) {
                let checkStatus = table.checkStatus(obj.config.id);
                switch (obj.event) {
                    case 'add':
                        break;
                }
            });

            //监听行工具条
            table.on('tool(lists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                let data = obj.data; //获得当前行数据
                let layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                let tr = obj.tr; //获得当前行 tr 的DOM对象

                if (layEvent === 'detail') { //查看
                    //do somehing
                }
            });
        });

    </script>
@endsection

