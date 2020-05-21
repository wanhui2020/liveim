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
                        <div class="layui-input-inline" style="margin-right: -11px;">
                            <input type="text" name="parent" placeholder="输入推荐人" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="sex">
                                <option value="">全部性别</option>
                                <option value="0">男</option>
                                <option value="1">女</option>
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="status">
                                <option value="">全部状态</option>
                                @foreach($statusList as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="online_status">
                                <option value="">在线状态</option>
                                @foreach($onlineStatusList as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="vv_busy">
                                <option value="">忙碌状态</option>
                                @foreach($busyStatusList as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="selfie_check">
                                <option value="">自拍认证</option>
                                @foreach($selfieCheckList as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="realname_check">
                                <option value="">实名认证</option>
                                @foreach($realNameCheckList as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="business_check">
                                <option value="">商务认证</option>
                                @foreach($businessCheckList as $key=>$item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <select name="is_inviter">
                                <option value="">邀请人</option>
                                <option value="1">是</option>
                                <option value="0">否</option>
                            </select>
                        </div>
                        <div class="layui-inline">
                            <input type="text" class="layui-input" style="width: 150px;" id="bdate" name="bdate"
                                   placeholder="注册时间开始"
                                   value="" readonly>
                        </div>
                        -
                        <div class="layui-inline">
                            <input type="text" class="layui-input" style="width: 150px;" id="edate" name="edate"
                                   placeholder="注册时间结束"
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
            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/info/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员信息'
                , cellMinWidth: 100
                , cols: [[
                    {fixed: 'left', field: 'id', title: 'ID', align: 'center', width: 80}
                    ,
                    {fixed: 'left', field: 'no', title: '序号', type: 'numbers', align: 'center', width: 40,hide:true}
                    , {
                        fixed: 'left', field: 'user_name', title: '会员编号/昵称', width: 180, templet: function (d) {

                            return d.code + '<br>' + d.nick_name;
                        }
                    }
                    , {
                        field: 'new_head_pic',
                        title: '头像',
                        align: 'center',
                        width: 80,
                        sort: true,
                        templet: function (d) {
                            // if (d.new_head_pic) {
                            //     return '<img style="width:25px;height: 25px;display: block" lay-event="headPic" src="' + d.new_head_pic + '"></img><a lay-event="headAudit">待审</a>';
                            // }
                            // return '<img style="width:50px;height: 50px;" src="' + d.head_pic + '"></img>';
                            return '<div onclick="show_img(this)" ><img src="' + d.head_pic + '" alt="" width="50px" height="50px"></a></div>';
                        }
                    }
                    , {
                        field: 'canti_gold',
                        title: '剩余金币',
                        align: 'center',
                        width: 90,
                        templet: function (d) {
                            if (d.account == null) {
                                return 0;
                            }
                            let html = '<b>' + d.account.surplus_gold + '</b>';
                            return html;
                        }
                    }
                    , {
                        field: 'sex', title: '性别', align: 'center', width: 60, templet: function (d) {
                            if (d.sex == 0) {
                                return '<a style="color:black">男</a>';
                            }else if(d.sex == 1) {
                                return '<a style="color:red">女</a>'
                            }else {
                                return '-';
                            }
                        }
                    }
                    , {
                        field: 'canti_gold', title: '冻结金币', align: 'center', width: 90, templet: function (d) {
                            if (d.account == null) {
                                return 0;
                            }
                            let html = d.account.notuse_gold;
                            return html;
                        }
                    }
                    , {
                        field: 'sort', title: '排序', align: 'center',hide:true, width: 60
                    }
                    , {
                        field: 'selfie_check', title: '自拍', align: 'center', width: 60, templet: function (d) {
                            let html = '<a style="color: red;">否</a>';
                            if (d.selfie_check === 1) {
                                html = '<a style="color:green;">是</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'realname_check', title: '实名', align: 'center', width: 60, templet: function (d) {
                            let html = '<a style="color: red;">否</a>';
                            if (d.realname_check === 1) {
                                html = '<a style="color:green;">是</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'business_check', title: '商务', align: 'center', width: 60, templet: function (d) {
                            let html = '<a style="color: red;">否</a>';
                            if (d.business_check === 1) {
                                html = '<a style="color:green;">是</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'is_agent', title: '代理商', align: 'center', width: 86,hide:true, templet: function (d) {
                            let html = '<a style="color: red;">否</a>';
                            if (d.is_agent === 1) {
                                html = '<a style="color:green;">是</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'online_status', title: '在线', align: 'center', width: 60, templet: function (d) {
                            let html = '<a style="color: red;">离线</a>';
                            if (d.online_status === 1) {
                                html = '<a style="color:green;">在线</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'vv_busy', title: '忙碌', align: 'center', width: 60, templet: function (d) {
                            let html = '<a style="color: red;">忙碌</a>';
                            if (d.vv_busy === 0) {
                                html = '<a style="color:green;">空闲</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        field: 'pid', title: '上级', align: 'left', width: 180
                        ,hide:true
                        , templet: function (d) {
                            if (d.parent == null) {
                                return '-';
                            }
                            return d.parent.code + '<br>' + d.parent.nick_name;
                        }
                    }
                    , {
                        field: 'inviter_id', title: '邀请人', align: 'left', width: 180
                        , templet: function (d) {
                            if (d.inviter == null) {
                                return '-';
                            }
                            return d.inviter.code + '<br>' + d.inviter.nick_name;
                        }
                    }
                    , {
                        field: 'inviter_zbid', title: '经纪人', align: 'left', width: 180
                        , templet: function (d) {
                            if (d.inviterzb == null) {
                                return '-';
                            }
                            return d.inviterzb.code + '<br>' + d.inviterzb.nick_name;
                        }
                    }
                    , {
                        field: 'is_inviter', title: '邀请人', align: 'center', width: 110, templet: function (d) {
                            let _c = '<u style="color: red;">否</u>';
                            if (d.is_inviter === 1) {
                                _c = '<u style="color:green;">是</u>';
                            }
                            let html = '<a  lay-event="inviter">' + _c + '</a>';
                            return html;
                        }
                    }
                    , {
                        field: 'is_inviter_zb', title: '经纪人', align: 'center', width: 110, templet: function (d) {
                            let _c = '<u style="color: red;">否</u>';
                            if (d.is_inviter_zb === 1) {
                                _c = '<u style="color:green;">是</u>';
                            }
                            let html = '<a  lay-event="inviterzb">' + _c + '</a>';
                            return html;
                        }
                    }
                    , {
                        field: 'childrens_count', title: '下级数', align: 'left', width: 90,sort:true, templet: function (d) {
                            return d.childrens_count;
                        }
                    }
                    , {
                        field: 'inviterchilds_count', title: '邀请数', align: 'left', width: 90,sort:true, templet: function (d) {
                            return d.inviterchilds_count;
                        }
                    }
                    , {
                        field: 'total_consume', title: '总消费金币', align: 'center', width: 100,hide:true, templet: function (d) {
                            if (d.account == null) {
                                return 0;
                            }
                            let html = d.account.total_consume;
                            return html;
                        }
                    }
                    , {
                        field: 'total_income', title: '总收益金币', align: 'center', width: 110,hide:true, templet: function (d) {
                            if (d.account == null) {
                                return 0;
                            }
                            let html = d.account.total_income;
                            return html;
                        }
                    }
                    , {
                        field: 'sys_plus', title: '后台赠送', align: 'center', width: 110,hide:true, templet: function (d) {
                            if (d.account == null) {
                                return 0;
                            }
                            let html = d.account.sys_plus;
                            return html;
                        }
                    }
                    , {
                        field: 'sys_plus', title: '后台扣除', align: 'center', width: 110,hide:true, templet: function (d) {
                            if (d.account == null) {
                                return 0;
                            }
                            let html = d.account.sys_minus;
                            return html;
                        }
                    }
                    , {field: 'mobile', title: '手机号', width: 100, align: 'center',hide:true}
                    , {
                        field: 'created_at', title: '创建/最后登录', width: 160,hide:true, align: 'center', templet: function (d) {
                            let html = d.created_at;
                            if (d.lastlogin[0]) {
                                html += '<br>' + d.lastlogin[0].login_time;
                            }

                            return html;
                        }
                    }
                    , {
                        field: 'status', title: '状态', align: 'center', width: 80,
                        templet: function (d) {
                            let html = '<a style="color: red;">禁用</a>';
                            if (d.status === 1) {
                                html = '<a style="color:green;">正常</a>';
                            }
                            return html;
                        }
                    }
                    , {
                        title: '操作', width: 200, align: 'center', templet: function (d) {
                            let _c = d.status === 1 ? '禁用' : '启用';
                            let html = '<a class="layui-btn layui-btn layui-btn-xs" lay-event="gold">金币变动</a>';
                            html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="setpid">设置邀请人</a>';
                            html += '<br>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="status">' + _c + '</a>';
                            html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>';
                            // html += '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>';
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

                if (layEvent === 'inviter') { //改变是否是邀请人
                    let _c = data.is_inviter === 0 ? '开通' : '关闭';
                    layer.confirm('确定要【' + _c + '】么?', {icon: 3, btn: ['确定', '取消'], title: "是否开通邀请人"}, function (index) {
                        axios.post("{{url('system/member/info/inviter')}}", {id: data.id})
                            .then(function (response) {
                                    layer.closeAll();
                                    if (response.data.code === 0) {
                                        tableIns.reload();
                                        return layer.msg(response.data.msg);
                                    }else {
                                        return layer.msg(response.data.msg);
                                    }
                                }
                            );
                    });
                }
                if (layEvent === 'inviterzb') { //改变是否是邀请主播
                    let _c = data.is_inviter_zb === 0 ? '开通' : '关闭';
                    layer.confirm('确定要【' + _c + '】么?', {icon: 3, btn: ['确定', '取消'], title: "是否开通经纪人"}, function (index) {
                        axios.post("{{url('system/member/info/inviterzb')}}", {id: data.id})
                            .then(function (response) {
                                    layer.closeAll();
                                    if (response.data.code === 0) {
                                        tableIns.reload();
                                        return layer.msg(response.data.msg);
                                    }else {
                                        return layer.msg(response.data.msg);
                                    }
                                }
                            );
                    });
                }
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
                if (layEvent === 'detail') { //查看
                    //do somehing
                } else if (layEvent === 'del') { //删除
                    var p = layer.confirm('确定要删除会员' + data.user_name + '？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/info/destroy')}}", {ids: [data.id]})
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

                    location.href = '{{url('system/member/info/edit?id=')}}' + data.id;

                    {{--layer.open({--}}
                    {{--type: 2--}}
                    {{--, title: '编辑会员信息'--}}
                    {{--, content: '{{url('system/member/info/edit?id=')}}' + data.id--}}
                    {{--, maxmin: true--}}
                    {{--, area: ['800px', '400px']--}}
                    {{--, end: function () {--}}
                    {{--tableIns.reload();--}}
                    {{--}--}}
                    {{--});--}}
                } else if (layEvent === 'status') { //禁用启用
                    var str = data.status == 0 ? '启用' : '禁用';
                    var p = layer.confirm('确定' + str + '会员' + data.user_name + '？', function (index) {
                        layer.close(p);
                        layer.load();
                        axios.post("{{url('system/member/info/status')}}", {id: data.id})
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
                } else if (layEvent === 'gold') { //编辑

                    location.href = '{{url('system/member/info/changegold')}}' + '/' + data.id;


                    {{--layer.open({--}}
                    {{--type: 2--}}
                    {{--, title: '会员金币变动'--}}
                    {{--, content: '{{url('system/member/info/changegold')}}' + '/' + data.id--}}
                    {{--, maxmin: true--}}
                    {{--, area: ['600px', '400px']--}}
                    {{--, end: function () {--}}
                    {{--tableIns.reload();--}}
                    {{--}--}}
                    {{--});--}}
                } else if (layEvent === 'setpid') { //设置邀请人
                    location.href = '{{url('system/member/info/setpid')}}' + '/' + data.id;

                    {{--layer.open({--}}
                    {{--type: 2--}}
                    {{--, title: '设置会员邀请人'--}}
                    {{--, content: '{{url('system/member/info/setpid')}}' + '/' + data.id--}}
                    {{--, maxmin: true--}}
                    {{--, area: ['600px', '400px']--}}
                    {{--, end: function () {--}}
                    {{--tableIns.reload();--}}
                    {{--}--}}
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

