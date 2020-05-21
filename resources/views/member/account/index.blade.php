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
                            <select name="sex">
                                <option value="">全部性别</option>
                                <option value="0">男</option>
                                <option value="1">女</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn "   id="Search" lay-submit lay-filter="search" style="margin-left: -21px;">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                        </button>
                        <button lay-submit lay-filter="refresh" class="layui-btn" style="margin-left: -1px;">重置</button>
                    </div>

                </div>
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

            let tableIns = table.render({
                elem: '#lists'
                , url: '{{url('/system/member/account/lists')}}' //数据接口
                , method: 'POST'
                , toolbar: '#toolbar'
                , page: true //开启分页
                , title: '会员扩展信息'
                , cellMinWidth: 100
                , cols: [[
                    {fixed: 'left', field: 'no', title: '序号', type: 'numbers', align: 'center'}
                    , {
                        fixed: 'left', field: 'user_name', title: '会员编号', align: 'center', templet: function (d) {
                            if (d.member == null) {
                                return '-'
                            }
                            return d.member.code;
                        }
                    }
                    , {
                        field: 'user_name', title: '会员名称', align: 'center', width: 180, templet: function (d) {
                            if (d.member == null) {
                                return '-'
                            }
                            return d.member.nick_name;
                        }
                    }
                    , {
                        field: 'sex', title: '性别', align: 'center', width: 60, templet: function (d) {
                            if (d.member == null) {
                                return '-'
                            }
                            if (d.member.sex === 0) {
                                return '<a style="color:black">男</a>';
                            }
                            return '<a style="color:red">女</a>'
                        }
                    }
                    , {field: 'surplus_gold', title: '剩余金币', align: 'center'}
                    , {field: 'notuse_gold', title: '不可用金币', align: 'center'}
                    , {field: 'lock_gold', title: '锁定金币',hide:true, align: 'center',
                            templet:function (d) {
                                if (d.lock_gold < 0){
                                    return 0;
                                }else {
                                    return  d.lock_gold;
                                }

                            }
                    }
                    , {
                        field: 'balance_gold', title: '实际可用金币', align: 'center', templet: function (d) {
                            return '<b style="color:green">' + d.balance_gold + '</b>'
                        }
                    }
                    , {field: 'surplus_rmb', title: '余额', align: 'center'}
                    , {field: 'notuse_rmb', title: '不可用余额', align: 'center'}
                    , {field: 'total_consume', title: '消费金币', align: 'center',hide:true}
                    , {field: 'total_income', title: '收益金币', align: 'center',hide:true}
                    , {field: 'sys_plus', title: '后台添加', align: 'center'}
                    , {field: 'sys_minus', title: '后台扣除', align: 'center'}
                    , {field: 'score', title: '积分', align: 'center',hide:true}
                    , {field: 'ml_score', title: '魅力积分', align: 'center',hide:true}
                    , {field: 'fh_score', title: '富豪积分', align: 'center',hide:true}
                    , {field: 'sign_days', title: '签到天数', align: 'center',hide:true}
                    , {field: 'bq_count', title: '补签次数', align: 'center,hide:true'}
                    , {field: 'visit_count', title: '被访问次数', align: 'center',hide:true}
                    , {field: 'lx_login_days', title: '连续登录天数', align: 'center',hide:true}
                    , {field: 'lx_login_max_days', title: '最大连续登录天数', align: 'center',hide:true}
                    , {field: 'text_charge', title: '普通消息收费', align: 'center'}
                    , {field: 'voice_charge', title: '语音消息收费', align: 'center'}
                    , {field: 'video_charge', title: '视频消息收费', align: 'center'}
                    , {field: 'picture_view_charge', title: '颜照库收费', align: 'center'}
                    // , {field: 'vip_level', title: 'vip等级', align: 'center'}
                    , {
                        field: 'vip_expire_date', title: 'vip到期日期', align: 'center', width: 120, templet: function (d) {
                            if (d.vip_expire_date != null) {
                                if (!d.is_vip) {
                                    return '<del style="color: red" title="已过期">' + d.vip_expire_date + '</del>'
                                }
                                return '<a style="color: green">' + d.vip_expire_date + '</a>';
                            }
                            return '';
                        }
                    }
                    , {field: 'gift_count', title: '收到礼物数量', align: 'center'}
                    , {field: 'xy_score', title: '信誉评分', align: 'center',hide:true}
                    , {field: 'myd_score', title: '满意度评分', align: 'center',hide:true}
                    , {field: 'yz_score', title: '颜值评分', align: 'center',hide:true}
                    , {
                        title: '操作', width: 160, align: 'center', templet: function (d) {
                            let html = '<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>';
                            html += '<a class="layui-btn layui-btn layui-btn-xs" lay-event="vip">赠送VIP</a>';
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
                    "key": "",
                    "sex": ""
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

                if (layEvent === 'detail') { //查看
                    //do somehing
                }
                else if (layEvent === 'edit') { //编辑
                    location.href = "{{url('system/member/account/edit?id=')}}" + data.id;
                    {{--layer.open({--}}
                    {{--type: 2--}}
                    {{--, title: '编辑会员账户信息'--}}
                    {{--, content: '{{url('system/member/account/edit?id=')}}' + data.id--}}
                    {{--, maxmin: true--}}
                    {{--, area: ['800px', '400px']--}}
                    {{--, end: function () {--}}
                    {{--tableIns.reload();--}}
                    {{--}--}}
                    {{--});--}}
                }
                else if (layEvent === 'vip') { //编辑
                    location.href = "{{url('system/member/account/setvip?id=')}}" + data.id;

                    {{--layer.open({--}}
                    {{--type: 2--}}
                    {{--, title: '赠送会员VIP'--}}
                    {{--, content: '{{url('system/member/account/setvip?id=')}}' + data.id--}}
                    {{--, maxmin: true--}}
                    {{--, area: ['600px', '400px']--}}
                    {{--, end: function () {--}}
                    {{--tableIns.reload();--}}
                    {{--}--}}
                    {{--});--}}
                }
            });
        });

    </script>
@endsection

