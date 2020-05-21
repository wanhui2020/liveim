<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{env('APP_NAME')}}</title>
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{ asset('dist/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{ asset('dist/layuiadmin/style/admin.css')}}" media="all">

    <script>
        /^http(s*):\/\//.test(location.href) || alert('请先部署到 localhost 下再访问');
    </script>
</head>
<body class="layui-layout-body">

<div id="LAY_app">
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <!-- 头部区域 -->
            <ul class="layui-nav layui-layout-left">
                <li class="layui-nav-item layadmin-flexible" lay-unselect>
                    <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
                        <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
                    </a>
                </li>
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="/" target="_blank" title="前台">
                        <i class="layui-icon layui-icon-website"></i>
                    </a>
                </li>
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:;" layadmin-event="refresh" title="刷新">
                        <i class="layui-icon layui-icon-refresh-3"></i>
                    </a>
                </li>
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <input type="text" placeholder="搜索..." autocomplete="off" class="layui-input layui-input-search"
                           layadmin-event="serach" lay-action="template/search.html?keywords=">
                </li>
            </ul>
            <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">

{{--                <li class="layui-nav-item" lay-unselect>--}}
{{--                    <a lay-href="/dist/views/app/message/index.html" layadmin-event="message" lay-text="消息中心">--}}
{{--                        <i class="layui-icon layui-icon-notice"></i>--}}

{{--                        <!-- 如果有新消息，则显示小圆点 -->--}}
{{--                        <span class="layui-badge-dot"></span>--}}
{{--                    </a>--}}
{{--                </li>--}}
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="theme">
                        <i class="layui-icon layui-icon-theme"></i>
                    </a>
                </li>
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="note">
                        <i class="layui-icon layui-icon-note"></i>
                    </a>
                </li>
                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="fullscreen">
                        <i class="layui-icon layui-icon-screen-full"></i>
                    </a>
                </li>
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:;">
                        {{--<cite>{{Auth::user()->name}} </cite>--}}
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a lay-href="{{url('system/info')}}">基本资料</a></dd>
                        <hr>
                        <dd style="text-align: center;"><a id="logout">退出</a></dd>
                    </dl>
                </li>

                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="about"><i
                            class="layui-icon layui-icon-more-vertical"></i></a>
                </li>
                <li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-unselect>
                    <a href="javascript:;" layadmin-event="more"><i class="layui-icon layui-icon-more-vertical"></i></a>
                </li>
            </ul>
        </div>

        <!-- 侧边菜单 -->
        <div class="layui-side layui-side-menu">
            <div class="layui-side-scroll">
                <div class="layui-logo">
                    <span>{{env('APP_NAME')}}</span>
                </div>
                <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu"
                    lay-filter="layadmin-system-side-menu">
                    <li data-name="home" class="layui-nav-item ">
                        <a href="javascript:;" lay-tips="工作台" lay-direction="3">
                            <i class="layui-icon layui-icon-home"></i>
                            <cite>工作台</cite>
                        </a>
                    </li>
                    <li data-name="system" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="平台管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-username"></i>
                            <cite>平台管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="system">
                                <a lay-href="{{url('system/platform/config')}}">平台参数</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="system">
                                <a lay-href="{{url('system/platform/data')}}">基础数据</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="system">
                                <a lay-href="{{url('system/platform/banner')}}">Banner图</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="system">
                                <a lay-href="{{url('system/platform/tag')}}">标签管理</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="system">
                                <a lay-href="{{url('system/platform/gift')}}">礼物管理</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="system">
                                <a lay-href="{{url('system/platform/rec')}}">充值比例设置</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="system">
                                <a lay-href="{{url('system/platform/message')}}">系统消息</a>
                            </dd>
                        </dl>
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="system">--}}
{{--                                <a lay-href="{{url('system/platform/sms')}}">短信列表</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
                    </li>
                    <li data-name="member" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="会员管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-chart-screen"></i>
                            <cite>会员管理</cite>
                        </a>
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="member">--}}
{{--                                <a lay-href="{{url('/system/member/level')}}">会员等级</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="member">--}}
{{--                                <a lay-href="{{url('/system/member/group')}}">会员分组</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
                        <dl class="layui-nav-child">
                            <dd data-name="member">
                                <a lay-href="{{url('/system/member/info')}}">会员列表</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="member">
                                <a lay-href="{{url('/system/member/account')}}">会员账户</a>
                            </dd>
                        </dl>
                    </li>
                    <li data-name="member" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="主播管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-chart-screen"></i>
                            <cite>主播管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="member">
                                <a lay-href="{{url('/system/member/extend')}}">主播管理</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="member">
                                <a lay-href="{{url('/system/member/file')}}">主播颜照库</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="member">
                                <a lay-href="{{url('/system/member/file/view')}}">颜照查看记录</a>
                            </dd>
                        </dl>
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="member">--}}
{{--                                <a lay-href="{{url('/system/member/coat')}}">主播衣服管理</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="member">--}}
{{--                                <a lay-href="{{url('/system/member/coat/order')}}">主播换衣订单</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
                        <dl class="layui-nav-child">
                            <dd data-name="member">
                                <a lay-href="{{url('/system/member/tag')}}">主播分类标签</a>
                            </dd>
                        </dl>
                    </li>
                    <li data-name="recharge" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="订单管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-survey"></i>
                            <cite>订单管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="recharge">
                                <a lay-href="{{url('/system/member/talk')}}">聊天订单</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="recharge">
                                <a lay-href="{{url('/system/member/recharge')}}">充值记录</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="recharge">
                                <a lay-href="{{url('/system/member/takenow')}}">提现记录</a>
                            </dd>
                        </dl>
                    </li>
                    <li data-name="check" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="认证管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-survey"></i>
                            <cite>认证管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="check">
                                <a lay-href="{{url('/system/member/selfie')}}">自拍认证</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="check">
                                <a lay-href="{{url('/system/member/realname')}}">实名认证</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="check">
                                <a lay-href="{{url('/system/member/business')}}">商务认证</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="check">
                                <a lay-href="{{url('/system/member/headpic')}}">头像审核</a>
                            </dd>
                        </dl>
                    </li>
                    <li data-name="member" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="商务管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-chart-screen"></i>
                            <cite>商务管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="member">
                                <a lay-href="{{url('/system/member/plan')}}">商务计划管理</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="member">
                                <a lay-href="{{url('/system/member/plan/order')}}">商务订单管理</a>
                            </dd>
                        </dl>
                    </li>
{{--                    <li data-name="score" class="layui-nav-item  ">--}}
{{--                        <a href="javascript:;" lay-tips="积分管理" lay-direction="3">--}}
{{--                            <i class="layui-icon  layui-icon-survey"></i>--}}
{{--                            <cite>积分管理</cite>--}}
{{--                        </a>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="score">--}}
{{--                                <a lay-href="{{url('/system/member/score/rule')}}">积分规则</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="score">--}}
{{--                                <a lay-href="{{url('/system/member/score/pt')}}">积分列表</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="score">--}}
{{--                                <a lay-href="{{url('/system/member/score/fh')}}">富豪积分</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="score">--}}
{{--                                <a lay-href="{{url('/system/member/score/ml')}}">魅力积分</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="charge">--}}
{{--                                <a lay-href="{{url('/system/member/record')}}">排行榜</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                    </li>--}}
                    <li data-name="charge" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="资金管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-survey"></i>
                            <cite>资金管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="charge">
                                <a lay-href="{{url('/system/member/exchange')}}">金币兑换</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="charge">
                                <a lay-href="{{url('/system/member/record')}}">资金流水</a>
                            </dd>
                        </dl>
                    </li>
                    <li data-name="hudong" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="互动管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-survey"></i>
                            <cite>互动管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="hudong">
                                <a lay-href="{{url('/system/member/idea')}}">意见反馈</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="hudong">
                                <a lay-href="{{url('/system/member/report')}}">会员举报</a>
                            </dd>
                        </dl>
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="hudong">--}}
{{--                                <a lay-href="{{url('/system/member/signin')}}">会员签到</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="hudong">--}}
{{--                                <a lay-href="{{url('/system/member/credit')}}">信誉评价</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
                    </li>
                    <li data-name="gift" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="礼物管理" lay-direction="3">
                            <i class="layui-icon  layui-icon-survey"></i>
                            <cite>礼物管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="gift">
                                <a lay-href="{{url('/system/member/gift')}}">礼物记录</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="gift">
                                <a lay-href="{{url('/system/member/reward')}}">打赏记录</a>
                            </dd>
                        </dl>
                    </li>
{{--                    <li data-name="user" class="layui-nav-item  ">--}}
{{--                        <a href="javascript:;" lay-tips="统计报表" lay-direction="3">--}}
{{--                            <i class="layui-icon  layui-icon-find-fill"></i>--}}
{{--                            <cite>统计报表</cite>--}}
{{--                        </a>--}}
{{--                        <dl class="layui-nav-child">--}}
{{--                            <dd data-name="daycount">--}}
{{--                                <a lay-href="{{url('/system/platform/statistics/day/count')}}">日统计表</a>--}}
{{--                            </dd>--}}
{{--                        </dl>--}}
{{--                    </li>--}}

                    <li data-name="user" class="layui-nav-item  ">
                        <a href="javascript:;" lay-tips="系统设置" lay-direction="3">
                            <i class="layui-icon layui-icon-set-fill"></i>
                            <cite>系统设置</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd data-name="console">
                                <a lay-href="{{url('system/base/config')}}">系统参数</a>
                            </dd>
                        </dl>
                        @if(\Illuminate\Support\Facades\Auth::guard('system')->user()->type == 0)
                        <dl class="layui-nav-child">
                            <dd data-name="console">
                                <a lay-href="{{url('system/base/user')}}">用户管理</a>
                            </dd>
                        </dl>
                        <dl class="layui-nav-child">
                            <dd data-name="console">
                                <a lay-href="{{url('system/base/logs')}}">系统日志</a>
                            </dd>
                        </dl>
                        @endif
                    </li>

                </ul>
            </div>
        </div>

        <!-- 页面标签 -->
        <div class="layadmin-pagetabs" id="LAY_app_tabs">
            <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-down">
                <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:;"></a>
                        <dl class="layui-nav-child layui-anim-fadein">
                            <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                            <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                            <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
                        </dl>
                    </li>
                </ul>
            </div>
            <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
                <ul class="layui-tab-title" id="LAY_app_tabsheader">
                    <li lay-id="home/console.html" lay-attr="home/console.html" class="layui-this"><i
                            class="layui-icon layui-icon-home"></i></li>
                </ul>
            </div>
        </div>


        <!-- 主体内容 -->
        <div class="layui-body" id="LAY_app_body">
            <div class="layadmin-tabsbody-item layui-show">
                <iframe name="child" src="{{url('system/home')}}" frameborder="0" class="layadmin-iframe"></iframe>
            </div>
        </div>

        <!-- 辅助元素，一般用于移动设备下遮罩 -->
        <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
</div>

<script src="{{ asset('js/app.js')}}"></script>
<script src="{{ asset('dist/layuiadmin/layui/layui.js')}}"></script>
<script>
    let element;
    layui.config({
        base: '{{ asset('dist/layuiadmin')}}/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'element'], function () {
        element = layui.element;
        let $ = layui.$;
        // element.on('tab(layadmin-layout-tabs)', function(data){
        //     console.log(this); //当前Tab标题所在的原始DOM元素
        //     console.log(data.index); //得到当前Tab的所在下标
        //     console.log(data.elem); //得到当前的Tab大容器
        // });
        $(document).on('click', '#logout', function () {
            axios.post("{{url('system/logout')}}")
                .then(function (response) {
                        console.log(response.data);
                        if (response.data.status) {
                            layer.msg('退出成功', {
                                offset: '15px'
                                , icon: 1
                                , time: 1000
                            }, function () {
                                location.href = '{{url("system/login")}}'; //跳转到登入页
                            });

                        } else {
                            layer.msg('请求失败', response.data.msg);
                        }

                    }
                ).catch(function (err) {

            });

        });
    });

    // Echo.private('entrust')
    //     .listen('.deal.entrust', (e) => {
    //         console.log(123);
    //     });
</script>
</body>
</html>


