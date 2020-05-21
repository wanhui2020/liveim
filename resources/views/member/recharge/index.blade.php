@extends('layouts.base')
@section('content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="aa">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <div class="layui-input-inline" style="margin-right: -11px;">
                            <input type="text" name="key" placeholder="输入订单号、会员编号、昵称等" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="type">
                                <option value="">全部类型</option>
                                @foreach($rechargeType as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="way">
                                <option value="">全部支付方式</option>
                                @foreach($recPayWay as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="status">
                                <option value="">全部状态</option>
                                @foreach($payStatus as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="search_data">
                                <option value="0" selected>支付时间</option>
                                <option value="1">创建时间</option>
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
                    <div class="layui-inline" style="width: 20%">
                        <label class="layui-form-label" style="width: 100%;text-align: left"
                               id="total_amounts"></label>
                    </div>
                    <div class="layui-inline" style="width: 20%">
                        <label class="layui-form-label" style="width: 100%;text-align: left"
                               id="total_quantitys"></label>
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
                            $("#total_amounts").text('总充值金额：' + talk.total_amounts);
                            $("#total_quantitys").text('总数量：' + talk.total_quantitys);
                        }
                    }));
            };
            loadTab({diff_type: 2});
            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/recharge/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员充值列表'
                , cellMinWidth: 100
                ,totalRow: true
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {field: 'order_no', title: '订单号', align: 'center', width: 150}
                    , {
                        field: 'member', title: '会员', align: 'center', width: 190, templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.code + ' - ' + d.member.nick_name;
                        }
                    }
                    , {field: 'type_cn', title: '充值类型', align: 'center'}
                    , {field: 'amount', title: '支付金额', align: 'center'}
                    , {
                        field: 'quantity', title: '数量', align: 'center', templet: function (d) {
                            let color = ' 币';
                            if (d.type === 2) {
                                color = ' 天';
                            }
                            return '<a>' + d.quantity + color + '</a>';
                        }
                    }
                    , {field: 'way_cn', title: '支付方式', width: 150, align: 'center'}
                    , {
                        field: 'status', title: '订单状态', align: 'center', width: 100,
                        templet: function (d) {
                            let color = 'green';
                            if (d.status === 0) {
                                color = 'orange';
                            }
                            if (d.status === 2) {
                                color = 'red';
                            }
                            return html = '<a style="color:' + color + ';">' + d.status_cn + '</a>';
                        }
                    }
                    , {field: 'remark', title: '描述', width: 160, align: 'center'}
                    , {field: 'pay_time', title: '支付时间', width: 160, align: 'center'}
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 160, templet: function (d) {
                            let html = '';
                            if (d.status == 0) {
                                if(d.way == 6){
                                    html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="deal" lay-data="1">核单处理</a>';
                                }else{
                                    html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="deal" lay-data="1">通过</a>';
                                    html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="deal" lay-data="2">拒绝</a>';
                                }
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
                field.diff_type = 2;
                loadTab(field);
            });

            //监听重置
            form.on('submit(refresh)', function (data) {
                //表单初始赋值
                form.val('aa', {
                    "key": ""
                    , "bdate": ""
                    , "edate": ""
                    , "status": ""
                    , "way": ""
                    , "search_data": 'pay_time'
                });
                table.reload('lists', {
                    where: ''
                });
                loadTab({diff_type: 2});
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
                } else if (layEvent === 'del') { //删除
                    var p = layer.confirm('确定要删除该充值记录？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/recharge/destroy')}}", {ids: [data.id]})
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
                } else if (layEvent === 'deal') { //处理
                    let status = $(this).attr("lay-data");
                    var str = status == 1 ? '通过' : '拒绝';
                    var p = layer.confirm('确定' + str + '该订单？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/recharge/status')}}", {id: data.id, status: status})
                            .then(function (response) {

                                    console.log(response.data.data);

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

