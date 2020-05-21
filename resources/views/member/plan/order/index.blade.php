@extends('layouts.base')
@section('content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="aa">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <div class="layui-input-inline" style="margin-right: -11px;">
                            <input type="text" name="key" placeholder="输入订单号、会员编号、昵称" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline" style="margin-right: -11px;">
                            <input type="text" name="zbkey" placeholder="输入主播编号、昵称" autocomplete="off"
                                   class="layui-input">
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
                            <select name="pay_status">
                                <option value="">全部支付状态</option>
                                @foreach($pay_status as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="search_data">
                                <option value="0" selected>创建时间</option>
                            </select>
                        </div>
                        <div class="layui-inline">
                            <input type="text" class="layui-input" style="width: 120px;" id="bdate" name="bdate"
                                   placeholder=""
                                   value="" readonly>
                        </div>
                        至
                        <div class="layui-inline">
                            <input type="text" class="layui-input" style="width: 120px;" id="edate" name="edate"
                                   placeholder=""
                                   value="" readonly>
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
                        <div class="layui-inline" style="width: 20%">
                            <label class="layui-form-label" style="width: 100%;text-align: left"
                                   id="total_amounts"></label>
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
            });
            laydate.render({
                elem: '#edate'
            });
            loadTab = function (data) {
                axios.all([
                    axios.post("/system/member/talk/getcollect", data)

                ])
                    .then(axios.spread(function (paymentResp) {
                        if (paymentResp.data.status) {
                            let talk = paymentResp.data.data;
                            $("#total_amounts").text('总服务费：' + talk.total_amounts);
                        }
                    }));
            };
            loadTab({diff_type: 5});
            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/plan/order/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '主播商务服务订单'
                , cellMinWidth: 100
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
                    , {
                        field: 'tomember', title: '主播', align: 'center', width: 190, templet: function (d) {
                            if (d.tomember == null) {
                                return '-';
                            }
                            return d.tomember.code + ' - ' + d.tomember.nick_name;
                        }
                    }
                    , {field: 'service_date', title: '服务日期', align: 'center', width: 120}
                    // , {field: 'project', title: '服务选项', align: 'center', width: 100}
                    , {field: 'amount', title: '服务费(元)', align: 'center', width: 100}
                    , {field: 'remark', title: '备注说明', align: 'center'}
                    , {
                        field: 'type_cn', title: '退款原因', align: 'center',
                        templet: function (d) {
                            let html = '';
                            if (d.data.value) {
                                return d.data.value;
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'refund_type', title: '退款类型', align: 'center',
                        templet: function (d) {
                            let html = '';
                            if (d.refund_type) {
                                return d.refund_type;
                            }
                            return html;
                        }
                    }
                    , {field: 'evaluation', title: '评价', align: 'center', hide: true,
                        templet:function (d) {
                            if (d.evaluation){
                                return '<span style="overflow: hidden;white-space:nowrap; " title=" ' + d.evaluation +'">'+d.evaluation+'</span>';
                            }else {
                                return '无';
                            }
                        }
                    }
                    , {
                        field: 'status', title: '支付状态', align: 'center', width: 100,
                        templet: function (d) {
                            let color = 'green';
                            if (d.pay_status === 0 || d.pay_status == 3) {
                                color = 'orange';
                            } else if (d.pay_status === 1) {
                                color = 'green';
                            } else if (d.pay_status === 2) {
                                color = 'red';
                            } else if (d.pay_status === 4) {
                                color = 'gray';
                            }

                            return html = '<a style="color:' + color + ';">' + d.pay_status_cn + '</a>';
                        }
                    }
                    , {
                        field: 'status', title: '状态', align: 'center', width: 100,
                        templet: function (d) {
                            let color = 'green';
                            if (d.status === 0) {
                                color = 'orange';
                            } else if (d.status === 1) {
                                color = 'blue';
                            } else if (d.status === 4) {
                                color = 'orange';
                            } else if (d.status === 3) {
                                color = 'grey';
                            } else if (d.status === 5 || d.status === 9) {
                                color = 'red';
                            } else if (d.status === 7) {
                                color = 'gray';
                            }
                            return html = '<a style="color:' + color + ';">' + d.status_cn + '</a>';
                        }
                    }
                    , {
                        field: 'score', title: '评分', align: 'center', sort: true, width: 80, templet: function (d) {
                            if (d.score === 0) {
                                return '普通';
                            }
                            if (d.score === 1) {
                                return '满意';
                            }
                            if (d.score === 2) {
                                return '很满意';
                            }
                        }

                    }
                    // , {field: 'evaluation', title: '服务评价', width: 120, hide: true}
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 240, templet: function (d) {
                            let html = '';
                            if (d.pay_status == 0) {
                                html += '<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="do" lay-data="1">已支付</a>';
                                html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>';
                            }
                            if (d.pay_status == 1 && d.status == 1) {
                                html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="2">接单</a>';
                                html += '<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="do" lay-data="3">拒绝</a>';
                            }
                            if (d.pay_status == 3 || (d.pay_status == 1 && d.status != 4)) {
                                html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="7">退款</a>';
                            }
                            if (d.status == 2) {
                                html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="4">开始服务</a>';
                                html += '<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="do" lay-data="3">取消订单</a>';
                            }
                            if (d.status == 4) {
                                html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="5">结束服务</a>';
                            }
                            if (d.status == 5) {
                                html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="6">结算</a>';
                            }
                            if (d.status == 6 || d.status == 7 || d.status == 8 || d.status == 9) {
                                html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>';
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
                    , page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                field.diff_type = 5;
                loadTab(field);
            });

            //监听重置
            form.on('submit(refresh)', function (data) {
                //表单初始赋值
                form.val('aa', {
                    "key": ""
                    , "zbkey": ""
                    , "status": ""
                    , "bdate": ""
                    , "edate": ""
                    , "search_data": '0'
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
                            , title: '新增主播商务订单'
                            , content: '{{url('system/member/plan/order/create')}}'
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

                if (layEvent === 'gotalk') { //查看
                    layer.msg('通知会员发起视频聊天...');
                } else if (layEvent === 'do') {
                    let status = $(this).attr("lay-data");
                    var str = "接受";

                    if (status == 1) {
                        str = "已完成支付"
                    } else if (status == 3) {
                        str = "拒绝";
                    } else if (status == 4) {
                        str = "开始服务";
                    } else if (status == 5) {
                        str = "结束服务";
                    } else if (status == 6) {
                        str = "结算";
                    } else if (status == 7) {
                        str = "已退款";
                    }
                    var p = layer.confirm('确定【' + str + '】该商务订单？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/plan/order/deal')}}", {id: data.id, status: status})
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
                } else if (layEvent === 'del') { //删除
                    var p = layer.confirm('确定要删除该记录？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/plan/order/destroy')}}", {ids: [data.id]})
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

