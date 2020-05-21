@extends('layouts.base')
@section('content')

<div class="layui-fluid"  >
            <div class="layui-card">
                <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="aa">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="margin-right: -11px;">
                                <input type="text" name="key" placeholder="请输入名称" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="margin-right: -11px;">
                                <input type="text" name="start_time" class="layui-input" id="start_time" placeholder="创建开始时间">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="margin-right: -11px;">
                                <input type="text" class="layui-input" name="end_time" id="end_time" placeholder="创建结束时间">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 120px;">
                                <select name="status">
                                    <option value="">全部状态</option>
                                    <option value="0">正常</option>
                                    <option value="1">禁用</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button class="layui-btn " lay-submit lay-filter="search" style="margin-left: -21px;">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                            </button>
                            <button lay-submit lay-filter="refresh" class="layui-btn" style="margin-left: -1px;" >重置</button>
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
        let feildmap = {!! json_encode(config('feildmap.contract_type')) !!};
        layui.use(['table','laydate'], function () {
            let table = layui.table,form = layui.form;
            let laydate = layui.laydate;

            let tableIns =  table.render({
                elem: '#lists'
                , url: '{{url('system/base/agreement/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '公告管理'

                , cols: [[
                      {field: 'name', title: '名称',align: 'center'}
                    , {field: 'content', title: '内容',align: 'center'}
                    , {field: 'type', title: '类型', align: 'center'}
                    , {
                        field: 'status', title: '状态', align: 'center',
                        templet: function (d) {
                            let html = '<a style="color: red;">禁用</a>';
                            if (d.status === 0) {
                                html = '<a style="color: #41a0ff;">正常</a>';
                            }
                            return html;
                        }
                    }
                    , {field: 'created_at', title: '创建时间', sort: true,align: 'center'}
                    , {
                        fixed: 'right', title: '操作', width: 220, align: 'center', templet: function (d) {
                            let _c = d.status === 0 ? '禁用':'启用';
                            let html = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="powerable">'+_c+'</a>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>';
                            return html;
                        }
                    }

                ]]
            });

            // 日期选择
            var endDate= laydate.render({
                elem: '#end_time',//选择器结束时间
                min:"1970-1-1",//设置min默认最小值
                done: function(value,date){
                    startDate.config.max={
                        year:date.year,
                        month:date.month-1,//关键
                        date: date.date
                    }
                }
            });
            //日期范围
            var startDate=laydate.render({
                elem: '#start_time',
                max:"2099-12-31",//设置一个默认最大值
                done: function(value, date){
                    endDate.config.min ={
                        year:date.year,
                        month:date.month-1, //关键
                        date: date.date
                    };
                }
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
                    ,"start_time": ""
                    ,"end_time": ""
                    ,"status": ""
                });
                table.reload('lists',{
                    where:''
                });
            });


            //监听工具栏事件
            table.on('toolbar(lists)', function (obj) {
                let checkStatus = table.checkStatus(obj.config.id);

                switch (obj.event) {
                    case 'add':
                        layer.open({
                            type: 2
                            , title: '添加公告'
                            , content: '{{url('system/base/agreement/create')}}'
                            , maxmin: true
                            , area: ['800px', '400px']
                            ,end:function () {
                                tableIns.reload(  );
                            }
                        });
                        break;
                    case 'delete':

                        let checkData = checkStatus.data; //得到选中的数据

                        if (checkData.length === 0) {
                            return layer.msg('请选择数据');
                        }
                        let ids = [];
                        checkData.forEach(function (value, index, array) {
                            ids.push(value.id);
                        });
                        layer.confirm('确定删除吗？', function (index) {
                            axios.post("{{url('system/merchant/receipt/destroy')}}", {ids: ids})
                                .then(function (response) {
                                        if (response.data.status) {
                                            table.reload('lists' );
                                            return layer.msg(response.data.msg);
                                        }
                                        return layer.alert(response.data.msg);
                                    }
                                );

                        });
                        break;
                    case 'update':
                        layer.msg('编辑');
                        break;
                }
            });

            //监听行工具条
            table.on('tool(lists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                let data = obj.data; //获得当前行数据
                let layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                let tr = obj.tr; //获得当前行 tr 的DOM对象

                if (layEvent === 'rates') { //倍数费率
                    if(data.status == 0)
                    {
                        layer.alert("请先禁用此协议!", {icon: 2});
                        return false;
                    }
                    top.layui.index.openTabsPage('/system/platform/product/rate/?id='+data.id,'倍数费率');
                } else if (layEvent === 'powerable') { //改变状态
                    let _c = data.status === 0 ? '禁用':'启用';
                    layer.confirm('确定要【'+_c+'】么?',{icon: 3,btn: ['确定', '取消'],title:"信息提示"}, function (index) {
                        axios.post("{{url('system/base/agreement/powerable')}}", {id: data.id})
                            .then(function (response) {
                                    if (response.data.code === 0) {
                                        layer.msg(response.data.data);
                                        tableIns.reload();
                                        return
                                    }
                                    return layer.msg(response.data.data);
                                }
                            );
                    });
                } else if (layEvent === 'edit') { //编辑
                    if(data.status == 0)
                    {
                        layer.alert("请先禁用此协议!", {icon: 2});
                        return false;
                    }
                    // alert(data.status);
                    layer.open({
                        type: 2
                        , title: '编辑协议管理'
                        , content: '{{url('system/base/agreement/edit?id=')}}' + data.id
                        , maxmin: true
                        , area: ['800px', '400px']
                        ,end:function () {
                            tableIns.reload(  );
                        }
                    });
                }else if (layEvent === 'del') { //删除
                    layer.confirm('确定要删除【'+data.name+'】么？',{icon:3,btn:['确定','取消'],title:"删除提示"}, function (index) {
                        axios.post("{{url('system/base/agreement/destroy')}}", {ids: [data.id]})
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
                axios.post("{{url('system/merchant/receipt/update')}}", data)
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

