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
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="status">
                                <option value="">全部状态</option>
                                @foreach($status as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
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
                <script type="text/html" id="toolbar">
                    <div>
                        <button class="layui-btn  layui-btn-sm" lay-event="add">添加</button>
                    </div>
                </script>
                <table id="lists" lay-filter="lists"></table>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script type="application/javascript">
        layui.use(['table', 'laydate'], function () {
            let table = layui.table, form = layui.form;
            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/report/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '举报管理'
                , cellMinWidth: 100
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {
                        field: 'member', title: '举报会员', align: 'center', width: 120, templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.user_name + '<br>' + d.member.nick_name;
                        }
                    }
                    , {
                        field: 'tomember', title: '被举报会员', align: 'center', width: 120, templet: function (d) {
                            if (d.tomember == null) {
                                return '-';
                            }
                            return d.tomember.user_name + '<br>' + d.tomember.nick_name;
                        }
                    }
                    , {field: 'type', title: '举报类型', width: 100, align: 'center'}
                    , {field: 'explain', title: '举报理由'}, {
                        field: 'status', title: '处理状态', align: 'center', width: 100,
                        templet: function (d) {
                            let color = 'green';
                            if (d.status === 0) {
                                color = 'red';
                            }
                            else if (d.status === 1) {
                                color = 'orange';
                            }
                            return html = '<a style="color:' + color + ';">' + d.status_cn + '</a>';
                        }
                    }
                    , {field: 'deal_reason', title: '处理结果'}
                    , {
                        field: 'deal_user', title: '处理人/时间', align: 'center', width: 160, templet: function (d) {
                            if (d.status == 0) {
                                return '-'
                            }
                            let html = d.deal_user + '<br>' + d.deal_time;
                            return html;
                        }
                    }
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 120, templet: function (d) {
                            let html = '';
                            if (d.status != 2) {
                                html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="deal">审核</a>';
                            }
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a><br>';
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
                        layer.open({
                            type: 2
                            , title: '新增会员举报'
                            , content: '{{url('system/member/report/create')}}'
                            , maxmin: true
                            , fixed: false //不固定
                            , area: ['600px', '400px']
                            , end: function () {
                                tableIns.reload();
                            }
                        });
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
                } else if (layEvent === 'del') { //删除
                    var p = layer.confirm('确定要删除该记录？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/report/destroy')}}", {ids: [data.id]})
                            .then(function (response) {
                                    layer.closeAll();
                                    if (response.data.status) {
                                        layer.msg(response.data.msg);
                                        return tableIns.reload();
                                    }
                                    return layer.alert(response.data.msg);
                                }
                            );
                    });
                } else if (layEvent === 'deal') { //编辑
                    layer.open({
                        type: 2
                        , title: '举报处理'
                        , content: '{{url('system/member/report/deal?id=')}}' + data.id
                        , maxmin: true
                        , area: ['600px', '400px']
                        , end: function () {
                            tableIns.reload();
                        }
                    });
                }
            });
        });

    </script>
@endsection

