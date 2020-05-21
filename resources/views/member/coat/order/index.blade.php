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
                            <select name="type">
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

            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/coat/order/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '主播换衣订单'
                , cellMinWidth: 100
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {
                        field: 'member', title: '会员', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.code + ' - ' + d.member.nick_name;
                        }
                    }
                    , {
                        field: 'tomember', title: '主播', align: 'center', templet: function (d) {
                            if (d.tomember == null) {
                                return '-';
                            }
                            return d.tomember.code + ' - ' + d.tomember.nick_name;
                        }
                    }
                    , {field: 'member_coat_id', title: '衣服ID', align: 'center', width: 100}
                    , {field: 'gold', title: '花费金币', align: 'center', width: 100}
                    , {
                        field: 'status', title: '状态', align: 'center', width: 100,
                        templet: function (d) {
                            let color = 'green';
                            if (d.status === 0) {
                                color = 'orange';
                            }
                            else if (d.status === 1) {
                                color = 'blue';
                            }
                            else if (d.status === 4) {
                                color = 'black';
                            }
                            else if (d.status === 3) {
                                color = 'gray';
                            }
                            else if (d.status === 5) {
                                color = 'red';
                            }

                            return html = '<a style="color:' + color + ';">' + d.status_cn + '</a>';
                        }
                    }
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 240, templet: function (d) {
                            let html = '';
                            if (d.status == 0) {
                                html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="1">接受</a>';
                                html += '<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="do" lay-data="5">拒绝</a>';
                                html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="do" lay-data="3">取消</a>';
                            }
                            if (d.status == 1) {
                                html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="2">换衣完成</a>';
                            }
                            if (d.status == 2) {
                                html += '<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="gotalk">发起聊天</a>';
                                html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="do" lay-data="4">结束</a>';
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


            //监听行工具条
            table.on('tool(lists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                let data = obj.data; //获得当前行数据
                let layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                let tr = obj.tr; //获得当前行 tr 的DOM对象

                if (layEvent === 'gotalk') { //查看
                    layer.msg('通知会员发起视频聊天...');
                }
                else if (layEvent === 'do') {
                    let status = $(this).attr("lay-data");
                    var str = "接受";

                    if (status == 2) {
                        str = "已完成"
                    }
                    else if (status == 3) {
                        str = "取消";
                    }
                    else if (status == 4) {
                        str = "结束";
                    }
                    else if (status == 5) {
                        str = "拒绝";
                    }
                    var p = layer.confirm('确定【' + str + '】该换衣订单？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/coat/order/deal')}}", {id: data.id, status: status})
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
                        axios.post("{{url('system/member/coat/order/destroy')}}", {ids: [data.id]})
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

