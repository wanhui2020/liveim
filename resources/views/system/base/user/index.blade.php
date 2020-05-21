@extends('layouts.base')
@section('content')
    <div class="layui-fluid">
            <div class="layui-card">
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">用户</label>
                            <div class="layui-input-inline">
                                <input type="text" name="key" placeholder="id/姓名/邮箱/手机号" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-inline">
                                <select name="status">
                                    <option value="">全部状态</option>
                                    <option value="0">正常</option>
                                    <option value="1">禁用</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button class="layui-btn " lay-submit lay-filter="search">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                            </button>
                        </div>
                    </div>
                </div>
                <div class="layui-card-body">
                    <script type="text/html" id="toolbar">
                        <div>
                            <button class="layui-btn  layui-btn-sm" lay-event="add">新增</button>
                        </div>
                    </script>
                    <table id="lists" lay-filter="lists"></table>

                </div>
            </div>
        </div>
@endsection
@section('script')
    <script type="application/javascript">
        let feildmap = {!! json_encode(config('feildmap.users_type')) !!};
        layui.use(['table'], function () {
            let table = layui.table, form = layui.form;
            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('system/base/user/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '用户管理'
                , cols: [[
                    {field: 'id', title: 'ID', width: 100, sort: true,align: 'center'}
                    , {field: 'name', title: '姓名', sort: true,align: 'center'}
                    , {field: 'email', title: '邮箱', sort: true,align: 'center'}
                    , {field: 'type', title: '角色',align: 'center',
                        templet: function (d) {
                            return feildmap[d.type];
                        }
                    }
                    , {field: 'phone', title: '手机号', sort: true,align: 'center'}
                    , {
                        field: 'status', title: '状态', width: 60,align: 'center',
                        templet: function (d) {
                            return d.status === 0 ? '正常' : '禁用';
                        }
                    }
                    , {
                        title: '操作', width: 150,align: 'center',
                        templet: function (d) {
                            let name = d.status === 0 ? '禁用' : '启用';
                            let html = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="status">' + name + '</a>';
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

            //监听工具栏事件
            table.on('toolbar(lists)', function (obj) {
                let checkStatus = table.checkStatus(obj.config.id);
                switch (obj.event) {
                    case 'add':
                        layer.open({
                            type: 2
                            , title: '新增系统人员'
                            , content: '{{url('system/base/user/create')}}'
                            , maxmin: true
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
                    layer.confirm('真的删除用户-'+data.name+'么', function (index) {
                        axios.post("{{url('system/base/user/destroy')}}", {ids: [data.id]})
                            .then(function (response) {
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
                        , title: '编辑系统用户'
                        , content: '{{url('system/base/user/edit?id=')}}' + data.id
                        , maxmin: true
                        , area: ['800px', '400px']
                        , end: function () {
                            tableIns.reload();
                        }
                    });
                } else if (layEvent === 'status') { //禁用启用
                    let str = data.status == 1 ? '启用' : '禁用';
                    layer.confirm('确定'+str+'用户-'+data.name+'么？', function (index) {
                        axios.post("{{url('system/base/user/status')}}", {id: data.id})
                            .then(function (response) {
                                    if (response.data.status) {
                                        layer.msg(response.data.msg);
                                        return tableIns.reload();
                                    }
                                    return layer.alert(response.data.msg);
                                }
                            );
                    });
                }
            });

            //监听单元格编辑
            table.on('edit(lists)', function (obj) {
                let value = obj.value //得到修改后的值
                    , data = obj.data //得到所在行所有键值
                    , field = obj.field; //得到字段
                update(data);
            });

            function update(data) {
                axios.post("{{url('system/base/user/update')}}", data)
                    .then(function (response) {
                            if (response.data.status) {
                                layer.msg(response.data.msg);
                                return
                            }
                            return layer.alert(response.data.msg);
                        }
                    );
            }
        });

    </script>
@endsection

