@extends('layouts.base')
@section('content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="aa">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <div class="layui-input-inline" style="margin-right: -11px;">
                            <input type="text" name="key" placeholder="输入会员编号、昵称、订单号" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline" style="margin-right: -11px;">
                            <input type="text" name="zbkey" placeholder="输入主播编号、昵称" autocomplete="off" class="layui-input">
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
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="type">
                                <option value="">全部聊天类型</option>
                                @foreach($type as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="search_data">
                                <option value="0" selected>创建时间</option>
                                <option value="1">开始时间</option>
                                <option value="2">结束时间</option>
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
                        <button class="layui-btn " id="Search" lay-submit lay-filter="search" style="margin-left: -21px;">
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
                                   id="total_amounts"></label>
                        </div>
                        <div class="layui-inline" style="width: 20%">
                            <label class="layui-form-label" style="width: 100%;text-align: left"
                                   id="total_profits"></label>
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
                            $("#total_amounts").text('总消费：' + talk.total_amounts);
                            $("#total_profits").text('总收益：' + talk.total_profits);
                        }
                    }));
            };
            loadTab({diff_type: 1});
            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/talk/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '聊天记录'
                , cellMinWidth: 100
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {field: 'id', title: 'ID', align: 'center', width: 60}
                    , {
                        field: 'member', title: '会员', align: 'center', width: 180, templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.code + '-' + d.member.nick_name;
                        }
                    }
                    , {
                        field: 'tomember', title: '主播', align: 'center', width: 180, templet: function (d) {
                            if (d.tomember == null) {
                                return '-';
                            }
                            return d.tomember.code + '-' + d.tomember.nick_name;
                        }
                    }
                    , {field: 'channel_code', title: '订单号', align: 'center'}
                    , {field: 'type_cn', title: '聊天类型', width: 100, align: 'center'}
                    , {field: 'price', title: '单价', width: 80, align: 'center'}
                    , {field: 'times', title: '时长(秒)', width: 80, align: 'center'}
                    , {field: 'amount', title: '总消费', width: 100, align: 'center'}
                    , {field: 'total_profit', title: '总收益', width: 100, align: 'center'}
                    , {field: 'begin_time', title: '开始时间', sort: true, align: 'center', width: 160}
                    , {field: 'end_time', title: '结束时间', sort: true, align: 'center', width: 160}
                    , {
                        field: 'status', title: '状态', align: 'center', width: 100,
                        templet: function (d) {
                            let color = 'black';
                            if (d.status === 0) {
                                color = 'orange';
                            }
                            if (d.status === 1) {
                                color = 'green';
                            }
                            return html = '<a style="color:' + color + ';">' + d.status_cn + '</a>';
                        }
                    }
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 180, templet: function (d) {
                            let html = '';
                            if (d.status == 0) {
                                if (d.type != 0) {
                                    html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="1">接听</a>';
                                    html += '<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="do" lay-data="2">拒接</a>';
                                } else {
                                    html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="do" lay-data="3">结束</a>';
                                }
                            }
                            if (d.status == 1) {
                                html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="do" lay-data="3">结束</a>';
                            }
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
                    , page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                field.diff_type = 1;
                loadTab(field);
            });

            //监听重置
            form.on('submit(refresh)', function (data) {
                //表单初始赋值
                form.val('aa', {
                    "key": "",
                    "zbkey":""
                    , "status": ""
                    , "bdate": ""
                    , "edate": ""
                    , "search_data": '0'
                });
                table.reload('lists', {
                    where: ''
                });
                loadTab({diff_type: 1});
            });

            //监听工具栏事件
            table.on('toolbar(lists)', function (obj) {
                let checkStatus = table.checkStatus(obj.config.id);
                switch (obj.event) {
                    case 'add':
                        layer.open({
                            type: 2
                            , title: '发起聊天'
                            , content: '{{url('system/member/talk/create')}}'
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
            $(document).keydown(function (e) {
                if (e.keyCode === 13) {
                    $("#Search").trigger("click");
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
                else if (layEvent === 'do') {
                    let status = $(this).attr("lay-data");
                    var str = "接听";
                    if (status == 2) {
                        str = "拒接"
                    }
                    if (status == 3) {
                        str = "结束";
                    }
                    var p = layer.confirm('确定' + str + '该聊天？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/talk/deal')}}", {id: data.id, status: status})
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
                else if (layEvent === 'del') { //删除
                    var p = layer.confirm('确定要删除该记录？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/talk/destroy')}}", {ids: [data.id]})
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

