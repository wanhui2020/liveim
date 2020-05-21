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
                        <div class="layui-input-inline" style="width: 120px;">
                            <select name="type">
                                <option value="">全部类型</option>
                                @foreach($type as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn " id="Search"  lay-submit lay-filter="search" style="margin-left: -21px;">
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
                , url: '{{url('/system/member/file/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员资源库'
                , cellMinWidth: 100
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {field: 'id', title: 'ID', align: 'center', width: 120}
                    , {
                        field: 'member', title: '所属主播', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.user_name + '<br>' + d.member.nick_name;
                        }
                    }
                    , {
                        field: 'type_cn', title: '资源类型', align: 'center', width: 100, templet: function (d) {
                            let color = 'blue';
                            if (d.type === 1) {
                                color = 'green';
                            }
                            return html = '<a style="color:' + color + ';">' + d.type_cn + '</a>';
                        }
                    }
                    , {
                        field: 'pic', title: '资源预览', align: 'center', width: 150, templet: function (d) {
                            let html = '';
                            if (d.type === 0) {
                                let html = '';
                                html = '<video style="cursor:pointer" width="100%" height="80px"  src="' + d.url + '"></video>';
                                return '<a href="' + d.url + '" target="_blank" title="点击查看">' + html + '</a>';
                            } else {
                                return '<div onclick="show_img(this)" ><img src="' + d.url + '" alt="" width="500px" height="80px"></a></div>';
                            }
                        }
                    }
                    , {
                        field: 'is_cover', title: '是否封面', align: 'center', width: 90,
                        templet: function (d) {
                            let color = 'red';
                            if (d.is_cover === 1) {
                                color = 'green';
                            }
                            return html = '<a style="color:' + color + ';">' + d.is_cover_cn + '</a>';
                        }
                    }
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
                    , {
                        field: 'deal_user', title: '审核人/时间', align: 'center', width: 160, templet: function (d) {
                            if (d.status == 0) {
                                return '';
                            }
                            let html = '<a title="' + d.deal_reason + '">' + d.deal_user + '<br>' + d.deal_time + '</a>';
                            return html;
                        }
                    }
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 160, templet: function (d) {
                            let html = '';
                            if (d.status == 0) {
                                html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="deal">审核</a>';
                            }
                            if (d.status == 1) {
                                html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="view">查看</a>';
                            }
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a><br>';
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
            });
            $(document).keydown(function (e) {
                if (e.keyCode === 13) {
                    $("#Search").trigger("click");
                }
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
                            , title: '新增主播资源'
                            , content: '{{url('system/member/file/create')}}'
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
                        axios.post("{{url('system/member/file/destroy')}}", {ids: [data.id]})
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
                        , title: '编辑主播资源'
                        , content: '{{url('system/member/file/edit?id=')}}' + data.id
                        , maxmin: true
                        , area: ['800px', '400px']
                        , end: function () {
                            tableIns.reload();
                        }
                    });
                }
                else if (layEvent === 'deal') { //编辑
                    layer.open({
                        type: 2
                        , title: '主播资源审核'
                        , content: '{{url('system/member/file/deal?id=')}}' + data.id
                        , maxmin: true
                        , area: ['600px', '400px']
                        , end: function () {
                            tableIns.reload();
                        }
                    });
                }
                else if (layEvent === 'view') { //查看
                    layer.open({
                        type: 2
                        , title: '查看主播资源'
                        , content: '{{url('system/member/file/doview?id=')}}' + data.id
                        , maxmin: true
                        , area: ['600px', '400px']
                        , end: function () {
                            tableIns.reload();
                        }
                    });
                }
            });
        });
        //显示图片
        function show_img(t) {
            var t = $(t).find("img");
            //页面层
            layer.open({
                type: 1,
                skin: 'layui-layer-rim', //加上边框
                area: ['80%', '70%'], //宽高
                shadeClose: true, //开启遮罩关闭
                end: function (index, layero) {
                    return false;
                },
                content: '<div style="text-align:center;height: 100%;"><img src="' + $(t).attr('src') + ' " height="100%" width="auto"/></div>'
            });
        }
    </script>
@endsection

