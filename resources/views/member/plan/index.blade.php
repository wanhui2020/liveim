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
                            <select name="project">
                                <option value="">全部选项</option>
                                @foreach($projectList as $key=>$item)
                                    <option value="{{$item['value']}}">{{$item['value']}}</option>
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
            let laydate = layui.laydate;

            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/plan/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '商务计划管理'
                , cellMinWidth: 100
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {field: 'id', title: 'ID', align: 'center', width: 60}
                    , {
                        field: 'member', title: '所属主播', width: 260, align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.user_name + '-' + d.member.nick_name;
                        }
                    }
                    , {
                        field: 'project', title: '服务项', width: 180, align: 'center'
                    }
                    , {
                        field: 'content', title: '服务内容'
                    }
                    , {field: 'pic_count', title: '图片数', width: 80, align: 'center'}
                    , {field: 'sort', title: '排序', align: 'center', width: 80}
                    , {
                        field: 'status', title: '当前状态', width: 120, sort: true, templet: function (d) {

                            let html = '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="audit">待审</a>';
                            if (d.status === 1) {
                                html = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="audit">已审</a>';
                            }
                            if (d.status === 0) {
                                html = '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="audit">审核失败</a>';
                            }
                            return html;
                        }
                    }
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 160, templet: function (d) {
                            let _c = d.status === 1 ? '禁用' : '启用';
                            let html = '';
                            html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="pic">图片</a>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>';
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
                            , title: '新增主播商务服务项'
                            , content: '{{url('system/member/plan/create')}}'
                            , maxmin: true
                            , fixed: false //不固定
                            , area: ['800px', '400px']
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
                if (layEvent === 'audit') { //头像
                    layer.confirm('商务行程审核？', {
                        btn: ['通过', '不通过'] //按钮
                    }, function () {
                        axios.post("{{url('system/member/plan/audit')}}", {id: data.id, status: 0})
                            .then(function (response) {
                                    layer.closeAll();
                                    if (response.data.status) {
                                        layer.msg(response.data.msg);
                                        return tableIns.reload();
                                    }
                                    return layer.alert(response.data.msg);
                                }
                            );
                    }, function () {
                        axios.post("{{url('system/member/plan/audit')}}", {id: data.id})
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
                }

                if (layEvent === 'detail') { //查看
                    //do somehing
                } else if (layEvent === 'del') { //删除
                    var p = layer.confirm('确定要删除该记录？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/plan/destroy')}}", {ids: [data.id]})
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
                } else if (layEvent === 'edit') { //编辑
                    layer.open({
                        type: 2
                        , title: '编辑主播商务服务项'
                        , content: '{{url('system/member/plan/edit?id=')}}' + data.id
                        , maxmin: true
                        , area: ['800px', '400px']
                        , end: function () {
                            tableIns.reload();
                        }
                    });
                } else if (layEvent === 'pic') { //图片列表
                    var lp = layer.open({
                        type: 2
                        , title: '服务计划图片列表'
                        , content: '{{url('system/member/plan/pic?id=')}}' + data.id
                        , maxmin: true
                        , area: ['800px', '600px']
                        , end: function () {
                            tableIns.reload();
                        }
                    });
                    layer.full(lp);
                }
            });
        });

    </script>
@endsection

