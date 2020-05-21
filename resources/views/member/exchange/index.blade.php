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
                            <select name="search_data">
                                <option value="0" selected>创建时间</option>
                            </select>
                        </div>
                        <div class="layui-inline">
                            <input type="text" class="layui-input" style="width: 150px;" id="bdate" name="bdate"
                                   placeholder=""
                                   value="" readonly>
                        </div>
                        至
                        <div class="layui-inline">
                            <input type="text" class="layui-input" style="width: 150px;" id="edate" name="edate"
                                   placeholder=""
                                   value="" readonly>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn "  id="Search" lay-submit lay-filter="search" style="margin-left: -21px;">
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
                        <div class="layui-inline" style="width: 20%">
                            <label class="layui-form-label" style="width: 100%;text-align: left"
                                   id="total_golds"></label>
                        </div>
                        <div class="layui-inline" style="width: 20%">
                            <label class="layui-form-label" style="width: 100%;text-align: left"
                                   id="total_rmbs"></label>
                        </div>
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
            laydate.render({
                elem: '#bdate'
                ,type: 'datetime'
            });
            laydate.render({
                elem: '#edate'
                ,type: 'datetime'
            });
            loadTab = function (data) {
                axios.all([
                    axios.post("/system/member/talk/getcollect", data)

                ])
                    .then(axios.spread(function (paymentResp) {
                        if (paymentResp.data.status) {
                            let talk = paymentResp.data.data;
                            $("#total_golds").text('总金币数量：' + talk.total_golds);
                            $("#total_rmbs").text('总兑换金额：' + talk.total_rmbs);
                        }
                    }));
            };
            loadTab({diff_type: 4});
            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/exchange/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '金币兑换记录'
                , cellMinWidth: 100
                ,totalRow: true
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'},
                    {
                        field: 'member', title: '会员编号', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.code;
                        }
                    }
                    , {
                        field: 'member', title: '会员昵称', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.nick_name;
                        }
                    }
                    , {field: 'gold', title: '金币数量', align: 'center', width: 120}
                    , {field: 'rmb', title: '兑换金额', align: 'center', width: 120}
                    , {field: 'remark', title: '备注说明', align: 'center'}
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        fixed: 'right', title: '操作', width: 180, align: 'center', templet: function (d) {
                            let html = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>';
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
                    , page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                field.diff_type = 4;
                loadTab(field);
            });

            //监听重置
            form.on('submit(refresh)', function (data) {
                //表单初始赋值
                form.val('aa', {
                    "key": ""
                    , "type": ""
                });
                table.reload('lists', {
                    where: ''
                });
                loadTab({diff_type: 4});
            });
            $(document).keydown(function (e) {
                if (e.keyCode === 13) {
                    $("#Search").trigger("click");
                }
            });
            //监听工具栏事件
            table.on('toolbar(lists)', function (obj) {
                let checkStatus = table.checkStatus(obj.config.id);
                switch (obj.event) {
                    case 'add':
                        layer.open({
                            type: 2
                            , title: '新增兑换'
                            , content: '{{url('system/member/exchange/create')}}'
                            , maxmin: true
                            , area: ['600px', '600px']
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
                    var p = layer.confirm('确定要删除该条数据？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/exchange/destroy')}}", {ids: [data.id]})
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
            });
        });

    </script>
@endsection

