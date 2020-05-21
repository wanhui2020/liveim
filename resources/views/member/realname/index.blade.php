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
                        <button class="layui-btn " lay-submit lay-filter="search" style="margin-left: -21px;" id="Search">
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
                , url: '{{url('/system/member/realname/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员实名认证'
                , cellMinWidth: 100
                , cols: [[
                    {field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {
                        field: 'realname_id', title: '阿里ID', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }else{
                                if (d.member.realname_id != null){
                                    return d.member.realname_id;
                                }else {
                                    return '-';
                                }
                            }
                        }
                    }
                    , {
                        field: 'member', title: '会员', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-';
                            }
                            return d.member.user_name + '<br>' + d.member.nick_name;
                        }
                    }
                    , {
                        field: 'cert_no', title: '姓名/身份证号', align: 'center', width: 180, templet: function (d) {
                            let html = d.name + '<br>' + d.cert_no;
                            return html;
                        }
                    }
                    , {
                        field: 'pic', title: '身份证正面', align: 'center', width: 150, templet: function (d) {
                            // let html = '<span href="' + d.cert_zm + '" target="_blank"><img width="90px" height="60px" src="' + d.cert_zm + '" alt=""/></span>';
                            // return html;
                            return '<div onclick="show_img(this)" ><img src="' + d.cert_zm + '" alt="" width="500px" height="80px"></a></div>';
                        }
                    }
                    , {
                        field: 'pic', title: '身份证反面', align: 'center', width: 150, templet: function (d) {
                            // let html = '<span href="' + d.cert_fm + '" target="_blank"><img width="90px" height="60px" src="' + d.cert_fm + '" alt=""/></span>';
                            // return html;
                            return '<div onclick="show_img(this)" ><img src="' + d.cert_fm + '" alt="" width="500px" height="80px"></a></div>';
                        }
                    }
                   /* , {
                        field: 'pic', title: '手持身份证', align: 'center', width: 100, templet: function (d) {
                            let html = '<a href="' + d.cert_sc + '" target="_blank"><img width="50px" height="50px" src="' + d.cert_sc + '" alt=""/></a>';
                            return html;
                        }
                    }*/
                    , {
                        field: 'selfie_pic', title: '自拍照', align: 'center', width: 150, templet: function (d) {
                            // let html = '<span  href="' + d.selfie_pic + '" target="_blank"><img width="90px" height="60px" src="' + d.selfie_pic + '" alt=""/></a>';
                            // return html;
                            return '<div onclick="show_img(this)" ><img src="' + d.selfie_pic + '" alt="" width="500px" height="80px"></a></div>';
                        }
                    }
                    , {
                        field: 'status', title: '状态', align: 'center', width: 100,
                        templet: function (d) {
                            let color = 'green';
                            if (d.status === 0) {
                                color = 'orange';
                            }
                            if (d.status === 9) {
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
                            if (d.deal_user){
                                let html = '<a title="' + d.deal_reason + '">' + d.deal_user + '<br>' + d.deal_time + '</a>';
                                return html;
                            }else {
                                return '-';
                            }
                        }
                    }
                    , {field: 'created_at', title: '创建时间', sort: true, align: 'center', width: 160}
                    , {
                        title: '操作', width: 120, templet: function (d) {
                            let html = '';
                            if (d.status == 0　|| d.status == 9) {
                                html += '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="deal">审核</a>';
                            }
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
                    , "start_time": ""
                    , "end_time": ""
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
                            , title: '新增实名认证'
                            , content: '{{url('system/member/realname/create')}}'
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
                        axios.post("{{url('system/member/realname/destroy')}}", {ids: [data.id]})
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
                } else if (layEvent === 'deal') { //编辑
                    location.href='{{url('system/member/realname/deal?id=')}}' + data.id;
                    {{--layer.open({--}}
                    {{--    type: 2--}}
                    {{--    , title: '实名认证审核'--}}
                    {{--    , content: '{{url('system/member/realname/deal?id=')}}' + data.id--}}
                    {{--    , maxmin: true--}}
                    {{--    , area: ['600px', '400px']--}}
                    {{--    , end: function () {--}}
                    {{--        tableIns.reload();--}}
                    {{--    }--}}
                    {{--});--}}
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

