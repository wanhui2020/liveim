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
                            <select name="account_type">
                                <option value="">全部账户类型</option>
                                @foreach($recordAccountType as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="type">
                                <option value="">全部资金类型</option>
                                @foreach($type as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="status">
                                <option value="">全部状态</option>
                                @foreach($status as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
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
                {{--<script type="text/html" id="toolbar">--}}
                {{--<div>--}}
                {{--<button class="layui-btn  layui-btn-sm" lay-event="add">添加</button>--}}
                {{--</div>--}}
                {{--</script>--}}
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

            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/record/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员资金明细列表'
                , cellMinWidth: 100
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {
                        field: 'member', title: '会员', align: 'center', width: 200, templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.code + ' - ' + d.member.nick_name;
                        }
                    }
                    , {field: 'type_cn', title: '资金类型', align: 'center', width: 180}
                    , {field: 'account_type_cn', title: '账户', align: 'center'}
                    , {field: 'before_amount', title: '操作前金额', align: 'center'}
                    , {field: 'amount', title: '发生金额', align: 'center'}
                    , {field: 'balance', title: '实时余额', align: 'center'}
                    , {field: 'remark', title: '备注说明', width: 300}
                    , {
                        field: 'status', title: '状态', align: 'center', width: 100,
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
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {field: 'order_no', title: '相关订单号', align: 'center', width: 150}
                    , {
                        fixed: 'right', title: '操作', width: 120, templet: function (d) {
                            let html = '';
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
                    , "start_time": ""
                    , "end_time": ""
                    , "status": ""
                    , "type": ""
                    , "bdate": ""
                    , "edate": ""
                });
                table.reload('lists', {
                    where: ''
                });
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
                        axios.post("{{url('system/member/record/destroy')}}", {ids: [data.id]})
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

