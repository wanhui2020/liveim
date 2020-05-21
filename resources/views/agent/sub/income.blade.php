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
                , url: '{{url('/agent/sub/incomes')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员信息'
                , cellMinWidth: 100
                , cols: [[
                    {fixed: 'left', field: 'no', title: '序号', type: 'numbers', align: 'center', width: 40}
                    , {
                        fixed: 'left', field: 'user_name', title: '贡献会员编号', width: 120, templet: function (d) {
                            if (d.frommember == null) {
                                return '-';
                            }
                            return d.frommember.code;
                        }
                    }
                    , {
                        fixed: 'left', field: 'user_name', title: '贡献会员名称', width: 120, templet: function (d) {
                            if (d.frommember == null) {
                                return '-';
                            }
                            return d.frommember.nick_name;
                        }
                    }
                    , {
                        fixed: 'left', field: 'sex', title: '性别', align: 'center', width: 60, templet: function (d) {
                            if (d.frommember.sex == 0) {
                                return '<a style="color:black">男</a>';
                            }
                            return '<a style="color:red">女</a>'
                        }
                    }
                    , {
                        field: 'pid', title: '贡献来源', align: 'center', width: 120, templet: function (d) {
                            if (d.frommember.pid == d.member.agent_id) {
                                return '直接贡献';
                            }
                            return '直接贡献';
                        }
                    }
                    , {
                        field: 'pid', title: '贡献方式', align: 'center', width: 120, templet: function (d) {
                            if (d.type == 0) {
                                return '充值贡献奖励';
                            }
                            if (d.type == 1) {
                                return '消费贡献奖励';
                            }
                            if (d.type == 2) {
                                return '商务收益贡献奖励';
                            }
                        }
                    }
                    , {field: 'gold', title: '贡献金币', align: 'center', width: 100}
                    , {field: 'money', title: '贡献金额', align: 'center', width: 100}
                    , {field: 'created_at', title: '贡献时间', sort: true, align: 'center', width: 180}
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

