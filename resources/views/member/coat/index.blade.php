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
            let laydate = layui.laydate;

            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/coat/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '主播衣服管理'
                , cellMinWidth: 100
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {field: 'id', title: 'ID', align: 'center', width: 60}
                    , {
                        field: 'member', title: '所属主播', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.user_name + '-' + d.member.nick_name;
                        }
                    }
                    , {
                        field: 'title', title: '衣服标题', align: 'center'
                    }
                    , {
                        field: 'pic', title: '衣服预览', align: 'center', width: 150, templet: function (d) {
                            let html = '<img width="50px" height="80px" src="' + d.url + '" alt=""/>';
                            return '<a href="' + d.url + '" target="_blank" title="点击查看">' + html + '</a>';
                        }
                    }
                    , {field: 'sort', title: '排序', align: 'center', width: 80}
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
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 160, templet: function (d) {
                            let _c = d.status === 1 ? '禁用' : '启用';
                            let html = '';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="status">' + _c + '</a>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>';
                            if (d.status == 1) {
                                html += '<br><a class="layui-btn layui-btn layui-btn-xs" lay-event="addorder">申请换衣</a>';
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
                            , title: '新增主播衣服'
                            , content: '{{url('system/member/coat/create')}}'
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

                if (layEvent === 'detail') { //查看
                    //do somehing
                } else if (layEvent === 'del') { //删除
                    var p = layer.confirm('确定要删除该记录？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/coat/destroy')}}", {ids: [data.id]})
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
                        , title: '编辑主播衣服'
                        , content: '{{url('system/member/coat/edit?id=')}}' + data.id
                        , maxmin: true
                        , area: ['800px', '400px']
                        , end: function () {
                            tableIns.reload();
                        }
                    });
                } else if (layEvent === 'status') { //禁用启用
                    var str = data.status == 0 ? '启用' : '禁用';
                    var p = layer.confirm('确定' + str + '主播该衣服？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/coat/status')}}", {id: data.id})
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
                else if (layEvent === 'addorder') { //申请
                    layer.open({
                        type: 2
                        , title: '申请主播换衣'
                        , content: '{{url('system/member/coat/addorder?id=')}}' + data.id
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

