@extends('layouts.base')
@section('content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="aa">
                <div class="layui-form-item">
                    统计日期：
                    <div class="layui-inline">
                        <input type="text" class="layui-input" style="width: 120px;" id="bdate"
                               placeholder=""
                               value="{{ date('Y-m-d', strtotime("-1 month")) }}" readonly>
                    </div>
                    至
                    <div class="layui-inline">
                        <input type="text" class="layui-input" style="width: 120px;" id="edate"
                               placeholder=""
                               value="{{ date('Y-m-d') }}" readonly>
                    </div>
                    <div class="layui-inline demoTable">
                        <button class="layui-btn" data-type="reload">统计</button>
                    </div>

                </div>
            </div>
            <div class="layui-card-body">
                <table id="mergeTable" lay-filter="test3"></table>

            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        layui.use(['table', 'element', 'laydate'], function () {
            var table = layui.table
                , form = layui.form, laydate = layui.laydate;

            //常规用法
            laydate.render({
                elem: '#bdate'
            });
            laydate.render({
                elem: '#edate'
            });

            /**
             * 自动合并表格
             */
            table.render({
                initSort: {
                    field: 'dayint' //排序字段，对应 cols 设定的各字段名
                    , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
                },
                elem: '#mergeTable'
                , url: '/system/platform/statistics/daycount'
                , cols: [[
                    {type: 'numbers', fixed: 'left'}
                    , {field: 'dayint', title: '统计日期', width: 150, sort: true, fixed: 'left'}
                    , {field: 'rec_gold', align: 'center', width: 100, sort: true, title: '充值金币', totalRow: true}
                    , {field: 'award_gold', align: 'center', width: 100, sort: true, title: '奖励金币', totalRow: true}
                    , {field: 'profit_gold', align: 'center', width: 100, sort: true, title: '收益金币', totalRow: true}
                    , {field: 'consume_gold', align: 'center', width: 100, sort: true, title: '消费金币', totalRow: true}
                    , {field: 'rec_money', title: '充值金额', sort: true, totalRow: true}
                    , {field: 'take_money', title: '提现金额', sort: true, totalRow: true}
                    , {field: 'profit_money', title: '收益金额', sort: true, totalRow: true}
                    , {field: 'consume_money', title: '消费金额', sort: true, totalRow: true}
                ]]
                , page: false
                , id: 'testReload'
                , totalRow: true
                , where: {
                    key: {
                        bdate: '{{ date('Y-m-d', strtotime("-1 month")) }}',
                        edate: '{{ date('Y-m-d') }}',
                    }
                }
            });
            var $ = layui.$, active = {
                reload: function () {
                    var bdate = $('#bdate');
                    var edate = $('#edate');

                    //执行重载
                    table.reload('testReload', {
                        // page: {
                        //     curr: 1 //重新从第 1 页开始
                        // }
                        where: {
                            key: {
                                bdate: bdate.val(),
                                edate: edate.val(),
                            }
                        }
                    });
                }
            };

            $('.demoTable .layui-btn').on('click', function () {
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        })
    </script>
@endsection