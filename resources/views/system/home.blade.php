@extends('layouts.base')
@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">

            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-header">
                        会员账户剩余
                        <span class="layui-badge layui-bg-blue layuiadmin-badge">金币</span>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p class="layuiadmin-big-font">{{$count['member_account']['surplus_gold']}}</p>
                        <p>
                            冻结不可使用
                            <span class="layuiadmin-span-color">{{$count['member_account']['notuse_gold']}}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-header">
                        会员账户可提现
                        <span class="layui-badge layui-bg-orange layuiadmin-badge">金额</span>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">

                        <p class="layuiadmin-big-font">￥{{$count['member_account']['surplus_rmb']}}</p>
                        <p>
                            冻结不可提现
                            <span class="layuiadmin-span-color">￥{{$count['member_account']['notuse_rmb']}}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-header">
                        主播账户剩余
                        <span class="layui-badge layui-bg-blue layuiadmin-badge">金币</span>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p class="layuiadmin-big-font">{{$count['zb_account']['surplus_gold']}}</p>
                        <p>
                            冻结不可兑换
                            <span class="layuiadmin-span-color">￥{{$count['zb_account']['notuse_rmb']}}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-header">
                        主播账户可提现
                        <span class="layui-badge layui-bg-orange layuiadmin-badge">金额</span>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">

                        <p class="layuiadmin-big-font">￥{{$count['zb_account']['surplus_rmb']}}</p>
                        <p>
                            冻结不可提现
                            <span class="layuiadmin-span-color">￥{{$count['zb_account']['notuse_rmb']}}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm3 layui-col-md2">
                <div class="layui-card">
                    <div class="layui-card-header">
                        会员消费金币
                        {{--<span class="layui-badge layui-bg-cyan layuiadmin-badge">今日</span>--}}
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>今日</p>
                        <p class="layuiadmin-big-font">{{floatval($count['today']['consume_gold'])}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>昨日</p>
                        <p class="layuiadmin-big-font">{{floatval($count['yestoday']['consume_gold'])}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>累计</p>
                        <p class="layuiadmin-big-font">{{floatval($count['total']['consume_gold'])}}</p>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm3 layui-col-md2">
                <div class="layui-card">
                    <div class="layui-card-header">
                        主播收益金币
                        {{--<span class="layui-badge layui-bg-cyan layuiadmin-badge">今日</span>--}}
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>今日</p>
                        <p class="layuiadmin-big-font">{{$count['today']['profit_gold']}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>昨日</p>
                        <p class="layuiadmin-big-font">{{$count['yestoday']['profit_gold']}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>累计</p>
                        <p class="layuiadmin-big-font">{{$count['total']['profit_gold']}}</p>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm3 layui-col-md2">
                <div class="layui-card">
                    <div class="layui-card-header">
                        平台收益金币
                        {{--<span class="layui-badge layui-bg-cyan layuiadmin-badge">今日</span>--}}
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>今日</p>
                        <p class="layuiadmin-big-font" style="color: green">{{$count['today']['sys_gold']}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>昨日</p>
                        <p class="layuiadmin-big-font" style="color: green">{{$count['yestoday']['sys_gold']}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>累计</p>
                        <p class="layuiadmin-big-font" style="color: green">{{$count['total']['sys_gold']}}</p>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-header">
                        充值金额
                        {{--<span class="layui-badge layui-bg-cyan layuiadmin-badge">今日</span>--}}
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>今日</p>
                        <p class="layuiadmin-big-font">￥{{floatval($count['today']['rec_money'])}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>昨日</p>
                        <p class="layuiadmin-big-font">￥{{floatval($count['yestoday']['rec_money'])}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>累计</p>
                        <p class="layuiadmin-big-font">￥{{floatval($count['total']['rec_money'])}}</p>
                    </div>
                </div>
            </div>
            <div class="layui-col-sm6 layui-col-md3">
                <div class="layui-card">
                    <div class="layui-card-header">
                        提现金额
                        {{--<span class="layui-badge layui-bg-cyan layuiadmin-badge">今日</span>--}}
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>今日</p>
                        <p class="layuiadmin-big-font">￥{{floatval($count['today']['take_money'])}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>昨日</p>
                        <p class="layuiadmin-big-font">￥{{floatval($count['yestoday']['take_money'])}}</p>
                    </div>
                    <div class="layui-card-body layuiadmin-card-list">
                        <p>累计</p>
                        <p class="layuiadmin-big-font">￥{{floatval($count['total']['take_money'])}}</p>
                    </div>
                </div>
            </div>

            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-header">待审核事项</div>
                    <div class="layui-card-body">
                        <div class="layui-carousel layadmin-carousel layadmin-backlog" lay-anim=""
                             lay-indicator="inside" lay-arrow="none" style="width: 100%; height: 280px;">
                            <div carousel-item="">
                                <ul class="layui-row layui-col-space10 layui-this">
                                    <li class="layui-col-xs6">
                                        <a lay-href="{{url('/system/member/selfie')}}" class="layadmin-backlog-body">
                                            <h3>自拍认证待审核</h3>
                                            <p><cite style="color: red">{{$count['check']['selfie']}}</cite></p>
                                        </a>
                                    </li>
                                    <li class="layui-col-xs6">
                                        <a lay-href="/" class="layadmin-backlog-body">
                                            <h3>实名认证待审核</h3>
                                            <p><cite style="color: red">{{$count['check']['realname']}}</cite></p>
                                        </a>
                                    </li>
                                    <li class="layui-col-xs6">
                                        <a lay-href="/" class="layadmin-backlog-body">
                                            <h3>商务认证待审核</h3>
                                            <p><cite style="color: red">{{$count['check']['business']}}</cite></p>
                                        </a>
                                    </li>
                                    <li class="layui-col-xs6">
                                        <a href="javascript:;" onclick="layer.tips('不跳转', this, {tips: 3});"
                                           class="layadmin-backlog-body">
                                            <h3>颜照视频库待审核</h3>
                                            <p><cite style="color: red">{{$count['check']['files']}}</cite></p>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-md6">
                <div class="layui-card">
                    <div class="layui-card-header">待处理事项</div>
                    <div class="layui-card-body">
                        <div class="layui-carousel layadmin-carousel layadmin-backlog" lay-anim=""
                             lay-indicator="inside" lay-arrow="none" style="width: 100%; height: 280px;">
                            <div carousel-item="">
                                <ul class="layui-row layui-col-space10 layui-this">
                                    <li class="layui-col-xs6">
                                        <a href="javascript:;" onclick="layer.tips('不跳转', this, {tips: 3});"
                                           class="layadmin-backlog-body">
                                            <h3>会员提现待处理</h3>
                                            <p><cite style="color: red">{{$count['check']['takenow']}}</cite></p>
                                        </a>
                                    </li>
                                    <li class="layui-col-xs6">
                                        <a href="javascript:;" onclick="layer.tips('不跳转', this, {tips: 3});"
                                           class="layadmin-backlog-body">
                                            <h3>会员举报待处理</h3>
                                            <p><cite style="color: red">{{$count['check']['report']}}</cite></p>
                                        </a>
                                    </li>
                                    <li class="layui-col-xs6">
                                        <a href="javascript:;" onclick="layer.tips('不跳转', this, {tips: 3});"
                                           class="layadmin-backlog-body">
                                            <h3>意见反馈待处理</h3>
                                            <p><cite style="color: red">{{$count['check']['idea']}}</cite></p>
                                        </a>
                                    </li>
                                    <li class="layui-col-xs6">
                                        <a href="javascript:;" onclick="layer.tips('不跳转', this, {tips: 3});"
                                           class="layadmin-backlog-body">
                                            <h3>头像审核待处理</h3>
                                            <p><cite style="color: red">{{$count['check']['head']}}</cite></p>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--<div class="layui-col-sm6 layui-col-md3">--}}
            {{--<div class="layui-card">--}}
            {{--<div class="layui-card-header">--}}
            {{--活跃用户--}}
            {{--<span class="layui-badge layui-bg-orange layuiadmin-badge">月</span>--}}
            {{--</div>--}}
            {{--<div class="layui-card-body layuiadmin-card-list">--}}

            {{--<p class="layuiadmin-big-font">66,666</p>--}}
            {{--<p>--}}
            {{--最近一个月--}}
            {{--<span class="layuiadmin-span-color">15% <i--}}
            {{--class="layui-inline layui-icon layui-icon-user"></i></span>--}}
            {{--</p>--}}
            {{--</div>--}}
            {{--</div>--}}
            {{--</div>--}}

        </div>
    </div>
@endsection

@section('script')
    {{--    <script src="{{ asset('js/socket.io.js') }}"  ></script>--}}
    <script type="application/javascript">
        layui.use(['table', 'laydate'], function () {
            let form = layui.form;
            let laydate = layui.laydate;

        });
    </script>
@endsection

