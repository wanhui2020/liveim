@extends('layouts.base')
@section('content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="aa">
            </div>
            <div class="layui-card-body">
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
                , url: '{{url('/system/member/headpic/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员信息'
                , cellMinWidth: 100
                , cols: [[
                    {fixed: 'left', field: 'id', title: 'ID', align: 'center'}
                    ,
                    {fixed: 'left', field: 'no', title: '序号', type: 'numbers', align: 'center',hide:true}
                    , {
                        fixed: 'left', field: 'user_name', title: '会员编号/昵称', templet: function (d) {

                            return d.code + '<br>' + d.nick_name;
                        }
                    }
                    , {
                        field: 'new_head_pic',
                        title: '原头像',
                        align: 'center',
                        sort: true,
                        templet: function (d) {
                            return '<div onclick="show_img(this)" ><img src="' + d.head_pic + '" alt="" width="50px" height="50px"></a></div>';
                        }
                    }
                    , {
                        field: 'new_head_pic',
                        title: '新头像',
                        align: 'center',
                        sort: true,
                        templet: function (d) {
                            if (d.new_head_pic) {
                                return '<img style="width:30px;height: 25px;display: block" lay-event="headPic" src="' + d.new_head_pic + '"></img><a lay-event="headAudit">待审</a>';
                            }
                            // return '<img style="width:50px;height: 50px;" src="' + d.head_pic + '"></img>';
                            return '<div onclick="show_img(this)" ><img src="' + d.head_pic + '" alt="" width="50px" height="50px"></a></div>';
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
                    ,"parent": ""
                    , "start_time": ""
                    , "end_time": ""
                    , "status": ""
                    , "is_inviter": ""
                    , "online_status": ""
                    , "bdate": ""
                    , "edate": ""
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
                        location.href = "{{url('system/member/info/create')}}";

                        {{--layer.open({--}}
                                {{--type: 2--}}
                                {{--, title: '新增会员'--}}
                                {{--, content: '{{url('system/member/info/create')}}'--}}
                                {{--, maxmin: true--}}
                                {{--, fixed: false //不固定--}}
                                {{--, area: ['800px', '500px']--}}
                                {{--, end: function () {--}}
                                {{--tableIns.reload();--}}
                                {{--}--}}
                                {{--});--}}
                            break;
                }
            });

            //监听行工具条
            table.on('tool(lists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                let data = obj.data; //获得当前行数据
                let layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                let tr = obj.tr; //获得当前行 tr 的DOM对象
                if (layEvent === 'headPic') { //头像
                    layer.open({
                        type: 1,
                        skin: 'layui-layer-rim', //加上边框
                        area: ['600px', '600px'], //宽高
                        content: '原头像<br><img style="width:200px" src="' + data.head_pic + '"/><br>新头像<br><img style="width:200px" src="' + data.new_head_pic + '"/>'
                    });
                }
                if (layEvent === 'headAudit') { //头像
                    layer.confirm('头像审核？', {
                        btn: ['通过','不通过'] //按钮
                    }, function(){
                        axios.post("{{url('system/member/info/head/audit')}}", {id: data.id,status:0})
                            .then(function (response) {
                                    layer.closeAll();
                                    if (response.data.status) {
                                        layer.msg(response.data.msg);
                                        return tableIns.reload();
                                    }
                                    return layer.alert(response.data.msg);
                                }
                            );
                    }, function(){
                        axios.post("{{url('system/member/info/head/audit')}}", {id: data.id})
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

