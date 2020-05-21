<?php

namespace App\Services;

use App\Facades\BaseFacade;
use App\Facades\CommonFacade;
use App\Models\MemberExchange;
use App\Models\MemberPlanOrder;
use App\Models\MemberRecharge;
use App\Models\MemberTakeNow;
use App\Models\MemberTalk;
use App\Traits\ResultTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

//统计报表
class ReportService
{

    private $pageSize = 10;
    use ResultTrait;

    public function getEntrusts(Request $request)
    {
        $where = function ($query) use ($request) {
            if (Auth::guard('agent')->check()){
                $agent_id = $request->user('agent')->id;
            }
            if (isset($agent_id)){
                $query->where('agent_id',$agent_id);
            }
            if ($request->key != null) {
                $query->where('no', 'like', '%' . $request->key . '%');
            }
            if ($request->stock != null) {
                $query->where('stock_code', 'like', '%' . $request->stock . '%')
                    ->orWhere('stock_name', 'like', '%' . $request->stock . '%');
            }
            if ($request->customer != null) {
                $query->whereHas('customer', function ($query) use ($request) {
                    $query->where('no', 'like', '%' . $request->customer . '%')
                        ->orwhere('realname', 'like', '%' . $request->customer . '%')
                        ->orwhere('phone', 'like', '%' . $request->customer . '%');
                });
            }
            if (request('agent') != null) {
                if (request('power') == 1){
                    $agent_ids = AgentUser::where(function ($query){
                        $query->where('no', 'like', '%' . request('agent') . '%')
                            ->orWhere('name', 'like', '%' . request('agent') . '%')
                            ->orWhere('email', 'like', '%' . request('agent') . '%')
                            ->orWhere('phone', 'like', '%' . request('agent') . '%');
                    })->pluck('id');
                    $parent_ids = BaseFacade::getAgentChilde(json_decode($agent_ids));
                    $query->whereIn('agent_id',$parent_ids);
                }else{
                    $query->whereHas('agent', function ($query) {
                        $query->where('no', 'like', '%' . request('agent') . '%')
                            ->orWhere('name', 'like', '%' . request('agent') . '%')
                            ->orWhere('email', 'like', '%' . request('agent') . '%')
                            ->orWhere('phone', 'like', '%' . request('agent') . '%');
                    });
                }
            }
            if ($request->start_time != null) {
                $query->where('created_at', '>', $request->start_time);
            }

            if ($request->end_time != null) {
                $query->where('created_at', '<', $request->end_time . '23:59:59');
            }
            if ($request->trade_type != null) {
                $query->where('trade_type', $request->trade_type);
            }
        };
        $entrusts = DealEntrust::where('entrust_status', 0)
            ->where('success_num', '>', 0)
            ->with([
                'customer:id,no,realname,phone',
                'agent:id,no,name',
                'contract:id,no,charge_money,platform_commission'
            ])
            ->where($where)
            ->get(['id', 'customer_id', 'agent_id', 'contract_id', 'no',
                'entrust_status', 'success_total', 'trade_type', 'charge_deal',
                'charge_service', 'created_at', 'stock_code', 'stock_name',
                'charge_stamps', 'charge_transfer', 'charge_trade','success_charge']);
        return $entrusts;
    }

    /**
     *  导出
     * @return array
     */
    public function getEntrustExport(Request $request)
    {
        try {
            $entrusts = $this->getEntrusts($request);
            $arr = [];
            $count_arr = $this->getEntrustCount($request);
            foreach ($entrusts as $k => $v) {
                $arr[$k]['订单委托编号'] = $v['no'];
                $arr[$k]['客户编号'] = $v['customer']['realname'] . $v['customer']['no'];
                $arr[$k]['所属代理商'] = $v['agent']['name'] . $v['agent']['no'];
                $arr[$k]['股票信息'] = $v['stock_name'] . $v['stock_code'];
                $arr[$k]['买卖方向'] = $v['trade_type'] == 0 ? '买入' : '卖出';
                $arr[$k]['成交金额'] = round($v['success_total'], 2);
                //$arr[$k]['策略建仓费'] = round(($v['trade_type'] == 1 ? 0 : $v['contract']['charge_money']), 2); // 卖出没有建仓费
                $arr[$k]['交易手续费'] = round($v['success_charge'], 2);
                $arr[$k]['印花税'] = round($v['charge_stamps'], 2);
                $arr[$k]['收益分成'] = round(($v['trade_type'] == 0 ? 0 : $v['contract']['platform_commission']), 2); // 收益分成买入没有
                $arr[$k]['过户费'] = round($v['charge_transfer'], 2);
                $arr[$k]['券商手续费'] = round(($v['charge_trade']), 2); // 券商手续费
                $arr[$k]['服务费'] = round($v['charge_service'], 2);
                $arr[$k]['利润'] = $this->getProfit($v); // 利润
                $arr[$k]['委托时间'] = $v['created_at'];
            }
            $count_export_arr = [
                "平台手续费:{$count_arr['total_charge']}元",
                "收益分成:{$count_arr['platform_commission']}元",
                "印花税:{$count_arr['total_charge_stamps']}元",
                "过户费:{$count_arr['total_charge_transfer']}元",
                "技术服务费:{$count_arr['total_charge_service']}元",
                "券商手续费:{$count_arr['total_trade_charge']}元",
                "总利润(不含服务费):{$count_arr['total_earn']}元",
            ];
            CommonFacade::csv($arr, $count_export_arr);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * 成交报表统计
     * @param  $request
     * @param  $customer_no
     * @return array
     */
    public function getEntrustCount(Request $request)
    {
        try {
            // 平台手续费
            $total_charge = 0;
            // 总利润 = 交易费 + 建仓费 + 收益分成-　券商费 -　过户费 - 印花税 - 技术
            $total_earn = 0;
            // 交易手续费
            $charge_deal = 0;
            // 过户费
            $charge_transfer = 0;
            // 印花税
            $charge_stamps = 0;
            // 收益分成
            $platform_commission = 0;
            // 券商手续费
            $charge_trade = 0;
            // 技术服务费
            $charge_service = 0;
            $entrusts = $this->getEntrusts($request);
            if (!$entrusts->isEmpty()) {
                foreach ($entrusts as $entrust) {
                    if ($entrust->trade_type == 0) { // 建仓费求和且买入才有   平台手续费 = 策略建仓费+交易手续费
                        $total_charge += $entrust->contract['charge_money'];
                    } else {//卖出时统计收益分成
                        $platform_commission += $entrust->contract->platform_commission;
                    }
                    $charge_deal += $entrust->success_charge;
                }
//                $charge_deal = $entrusts->sum('success_charge');
                $charge_transfer = $entrusts->sum('charge_transfer');
                $charge_stamps = $entrusts->sum('charge_stamps');
                $charge_service = $entrusts->sum('charge_service');
                $charge_trade = $entrusts->sum('charge_trade');
                // 总利润 = 交易费 + 建仓费 + 收益分成-　券商费 -　过户费 - 印花税 - 技术
                $total_earn = $charge_deal + $total_charge+ $platform_commission - $charge_transfer  - $charge_stamps  - $charge_trade - $charge_service;
            }
            return [
                'total_charge' => round($total_charge+$charge_deal , 2),     //平台手续费 = 策略建仓费+交易手续费     平台收入 = 交易费 + 建仓费 + 收益分成
                'platform_commission' => round($platform_commission, 2),     //收益分成
                'total_charge_service' => round($charge_service, 2),     //技术服务费
                'total_charge_stamps' => round($charge_stamps, 2),     //印花税
                'total_charge_transfer' => round($charge_transfer, 2),     //过户费
                'total_platform_commission' => round($platform_commission, 2),     //收益分成
                'total_trade_charge' => round($charge_trade, 2),     //总成券商手续费
                'total_earn' => round($total_earn, 2),  //总利润(不含服务费)
            ];
        } catch (\Exception $ex) {
            return $this->exception($ex, 'obj');
        }
    }

    /**
     *  计算成交利润
     * @param $entrust
     * @return array|float|int
     */
    public function getProfit($entrust)
    {
        try{
            $profit = 0;
            if (empty($entrust)){
                return $profit;
            }
            if ($entrust['trade_type'] == 0){
                $profit = round($entrust['success_charge'],2) - round($entrust['charge_trade'],2) - round($entrust['charge_stamps'],2) - round($entrust['charge_transfer'],2) - round($entrust['charge_service'],2);
                if ($entrust['contract']){
                    $profit += round($entrust['contract']['charge_money'],2);
                }
            }else{
                $profit = round($entrust['success_charge'],2) - round($entrust['charge_trade'],2) - round($entrust['charge_stamps'],2) - round($entrust['charge_transfer'],2) - round($entrust['charge_service'],2);
                if ($entrust['contract']){
                    $profit += round($entrust['contract']['platform_commission'],2);
                }
            }
            return round($profit,2);
        }catch (\Exception $e){
            return $this->exception($e);
        }
    }


    public function getDatas(Request $request)
    {
        $data = [];
        switch ($request->diff_type) {
            case 1:
                $where = function ($query) {
                    if (request('key')) {
                        $query->orWhere(function ($query) {
                            $query->whereHas('member', function ($query) {
                                $query->where('code', 'like', '%' . request('key') . '%')
                                    ->orWhere('user_name', 'like', '%' . request('key') . '%')
                                    ->orWhere('nick_name', 'like', '%' . request('key') . '%');

                            });
                        });
                    }
                    if (request('zbkey') != null) {
                        $query->where(function ($query) {
                            $query->whereHas('tomember', function ($query) {
                                $query->where('code', 'like', '%' . request('zbkey') . '%')
                                    ->orWhere('user_name', 'like', '%' . request('zbkey') . '%')
                                    ->orWhere('nick_name', 'like', '%' . request('zbkey') . '%');

                            });
                        });
                    }
                    if (request('status') != null) {
                        $query->where('status', request('status'));
                    }
                    if (request('type') != null) {
                        $query->where('type', request('type'));
                    }
                    if (request('bdate') != null && request('edate')) {
                        if (request('search_date') == 0) {
                            $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                        }
                    }
//                    if (request('bdate') != null && request('edate') != null) {
//                        if (request('search_data') == 0) {
//                            $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
//                        }
//                        if (request('search_data') == 1) {
//                            $query->WhereBetween('begin_time', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
//                        }
//                        if (request('search_data') == 2) {
//                            $query->WhereBetween('end_time', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
//                        }
//                    }

                };
                $data = MemberTalk::with(['member','tomember'])
                    ->where($where)
                    ->get();
                break;
            case 2:
                $where = function ($query) {
                    if (request('key')) {
                        $query->where(function ($query) {
                            $query->where('id', 'like', '%' . request('key') . '%')
                                ->orWhere('order_no', 'like', '%' . request('key') . '%');
                        });
                        $query->orWhere(function ($query) {
                            $query->whereHas('member', function ($query) {
                                $query->where('code', 'like', '%' . request('key') . '%')
                                    ->orWhere('user_name', 'like', '%' . request('key') . '%')
                                    ->orWhere('nick_name', 'like', '%' . request('key') . '%');

                            });
                        });
                    }
                    if (request('status') != null) {
                        $query->where('status', request('status'));
                    }
                    if (request('way') != null) {
                        $query->where('way', request('way'));
                    }
                    if (request('type') != null) {
                        $query->where('type', request('type'));
                    }
                    if (request('bdate') != null && request('edate')) {
                        if (request('search_date') == 0) {
                            $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                        }
                    }
                };
                $data = MemberRecharge::with([
                        'member',
                    ])
                    ->where($where)
                    ->get();
                break;
            case 3:
                $where = function ($query) {
                    if (request('key')) {
                        $query->where(function ($query) {
                            $query->where('id', 'like', '%' . request('key') . '%');
                        });
                        $query->orWhere(function ($query) {
                            $query->whereHas('member', function ($query) {
                                $query->where('code', 'like', '%' . request('key') . '%')
                                    ->orWhere('user_name', 'like', '%' . request('key') . '%')
                                    ->orWhere('nick_name', 'like', '%' . request('key') . '%');

                            });
                        });
                    }
                    if (request('status') != null) {
                        $query->where('status', request('status'));
                    }
                    if (request('way') != null) {
                        $query->where('way', request('way'));
                    }
                    if (request('bdate') != null && request('edate')) {
                        if (request('search_date') == 0) {
                            $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                        }
                    }
                };
                $data = MemberTakeNow::with([
                        'member',
                    ])
                    ->where($where)
                    ->get();
                break;
            case 4:
                $where = function ($query) {
                    if (request('key')) {
                        $query->orWhere(function ($query) {
                            $query->whereHas('member', function ($query) {
                                $query->where('code', 'like', '%' . request('key') . '%')
                                    ->orWhere('user_name', 'like', '%' . request('key') . '%')
                                    ->orWhere('nick_name', 'like', '%' . request('key') . '%');
                            });
                        });
                    }
                    if (request('bdate') != null && request('edate')) {
                        if (request('search_date') == 0) {
                            $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                        }
                    }
                };
                $data = MemberExchange::with(['member'])
                    ->where($where)
                    ->get();
                break;
            case 5:
                $where = function ($query) {
                    if (request('key')) {
                        $query->where(function ($query) {
                            $query->where('order_no', 'like', '%' . request('key') . '%');
                        });
                        $query->orWhere(function ($query) {
                            $query->whereHas('member', function ($query) {
                                $query->where('code', 'like', '%' . request('key') . '%')
                                    ->orWhere('user_name', 'like', '%' . request('key') . '%')
                                    ->orWhere('nick_name', 'like', '%' . request('key') . '%');

                            });
                        });
                    }
                    if (request('zbkey') != null) {
                        $query->where(function ($query) {
                            $query->whereHas('tomember', function ($query) {
                                $query->where('code', 'like', '%' . request('zbkey') . '%')
                                    ->orWhere('user_name', 'like', '%' . request('zbkey') . '%')
                                    ->orWhere('nick_name', 'like', '%' . request('zbkey') . '%');

                            });
                        });
                    }
                    if (request('status') != null) {
                        $query->where('status', request('status'));
                    }
                    if (request('pay_status') != null) {
                        $query->where('pay_status', request('pay_status'));
                    }
                    if (request('search_status') != null) {
                        if (request('search_status') == 1) {
                            //处理中
                            $query->whereIn('status', [0, 1, 2, 4, 5]);
                        }
                        if (request('search_status') == 2) {
                            //已完成的
                            $query->whereIn('status', [3, 6, 7]);
                        }
                    }
                    if (request('bdate') != null && request('edate')) {
                        if (request('search_date') == 0) {
                            $query->WhereBetween('created_at', [request('bdate') . ' 00:00:00', request('edate') . ' 23:59:59']);
                        }
                    }
                };
                $data = MemberPlanOrder::with(['member', 'tomember'])
                    ->where($where)
                    ->get();
                break;
        }

        return $data;
    }

    /**
     * 充值数据下载
     * @param $request
     */
    public function chargeDownload(Request $request)
    {
        $charges = $this->getDatas($request);
        $dataCount = $this->getDataCount($request);
        // 总充值金额
        $total_charge = $dataCount['total_charge'];
        // 总实收手续费
        $total_poundage = $dataCount['total_poundage'];
        // 总充值成本
        $total_real_poundage = $dataCount['total_real_poundage'];
        $arr = [];
        foreach ($charges as $k => $charge) {
            if ($charge->customer->is_real == 0) {
                $arr[$k]['客户编号'] = $charge->customer->realname . $charge->customer->no;
            } else {
                $arr[$k]['客户编号'] = $charge->customer->name . $charge->customer->no;
            }
            $arr[$k]['客户电话'] = $charge->customer->phone;
            $arr[$k]['所属代理商'] = $charge->agent ? $charge->agent->name . $charge->agent->no : '--';
            $arr[$k]['订单编号'] = $charge->no;
            $arr[$k]['充值金额'] = round($charge->money, 2);
            $arr[$k]['实收手续费'] = round($charge->user_poundage, 2);
            $arr[$k]['充值成本'] = round($charge->platform_poundage, 2);
            $arr[$k]['应收手续费'] = round($charge->poundage, 2);
            $arr[$k]['支付方式'] = config('feildmap.pay_type')[$charge->pay_type];
            $arr[$k]['利润'] = round(($charge->user_poundage - $charge->platform_poundage),2);
            $arr[$k]['备注'] = $charge->remark;
            $arr[$k]['创建时间'] = $charge->created_at;
        }
        // 总利润
        $loss = $dataCount['loss'];
        $count_export_arr = [
            "总充值金额:{$total_charge}元",
            "总实收手续费:{$total_poundage}元",
            "总充值成本:{$total_real_poundage}元",
            "盈利:{$loss}元",
        ];
        exit(CommonFacade::csv($arr, $count_export_arr));
    }


    /**
     * 提现数据下载
     * @param $request
     */
    public function withdrawDownload(Request $request)
    {
        $withdraws = $this->getDatas($request);
        $dataCount = $this->getDataCount($request);
        // 总提现金额
        $total_charge = $dataCount['total_charge'];
        // 总实收手续费
        $total_poundage = $dataCount['total_poundage'];
        // 总提现成本
        $total_real_poundage = $dataCount['total_real_poundage'];
        $arr = [];
        foreach ($withdraws as $k => $withdraw) {
            if ($withdraw->customer->is_real == 0) {
                $arr[$k]['客户编号'] = $withdraw->customer->realname . $withdraw->customer->no;
            } else {
                $arr[$k]['客户编号'] = $withdraw->customer->name . $withdraw->customer->no;
            }
            $arr[$k]['客户电话'] = $withdraw->customer->phone;
            $arr[$k]['所属代理商'] = $withdraw->agent ? $withdraw->agent->name . $withdraw->agent->no : '--';
            $arr[$k]['订单编号'] = $withdraw->no;
            $arr[$k]['提现金额'] = round($withdraw->money, 2);
            $arr[$k]['实收手续费'] = round($withdraw->user_poundage, 2);
            $arr[$k]['应收手续费'] = round($withdraw->poundage, 2);
            $arr[$k]['提现成本'] = round($withdraw->platform_poundage, 2);
            $arr[$k]['利润'] = round(($withdraw->user_poundage - $withdraw->platform_poundage),2);
            $arr[$k]['创建时间'] = $withdraw->created_at;
        }
        // 总利润
        $loss = $dataCount['loss'];
        $count_export_arr = [
            "总提现金额:{$total_charge}元",
            "实收手续费:{$total_poundage}元",
            "提现成本:{$total_real_poundage}元",
            "利润:{$loss}元",
        ];
        exit(CommonFacade::csv($arr, $count_export_arr));
    }


    /**
     * 递延费数据下载
     * @param $request
     */
    public function deferredDownload(Request $request)
    {
        $deferreds = $this->getDatas($request);
        $dataCount = $this->getDataCount($request);
        $total_charge = $dataCount['total_charge'];
        $total_capital_cost = $dataCount['total_capital_cost'];
        $arr = [];
        foreach ($deferreds as $k => $deferred) {
            if ($deferred->customer->is_real == 0) {
                $arr[$k]['客户编号'] = $deferred->customer->realname . $deferred->customer->no;
            } else {
                $arr[$k]['客户编号'] = $deferred->customer->name . $deferred->customer->no;
            }
            $arr[$k]['客户电话'] = $deferred->customer->phone;
            $arr[$k]['所属代理商'] = $deferred->agent ? $deferred->agent->name . $deferred->agent->no : '--';
            $arr[$k]['订单编号'] = $deferred->no;
            $arr[$k]['策略编号'] = $deferred->contract->no;
            $arr[$k]['保证金'] = $deferred->contract->deposite_money;
            $arr[$k]['保证金'] = $deferred->contract->strategy_money;
            $arr[$k]['递延费费率'] = $deferred->contract->contract_rate;
            $arr[$k]['扣费天数'] = 1 . '*' . 1 . '天';
            $arr[$k]['递延费'] = $deferred->money;
            $arr[$k]['资金成本'] = $deferred->capital_cost;
            $arr[$k]['资金成本利率'] = $deferred->capital_cost_rate;
            $arr[$k]['利润'] = round(($deferred->money - $deferred->capital_cost),2);
            $arr[$k]['创建时间'] = $deferred->created_at;
        }
        // 总利润
        $loss = $dataCount['loss'];
        $count_export_arr = [
            "总递延费:{$total_charge}元",
            "总资金成本:{$total_capital_cost}元",
            "利润:{$loss}元",
        ];
        exit(CommonFacade::csv($arr, $count_export_arr));
    }

    public function getDataCount(Request $request)
    {
        $data = [];
        switch ($request->diff_type) {
            case 1:
                $talks = $this->getDatas($request);
                $data['total_amounts'] = round($talks->sum('amount'), 2);
                $data['total_profits'] = round($talks->sum('total_profit'), 2);
                break;
            case 2:
                $withdraws = $this->getDatas($request);
                $data['total_amounts'] = round($withdraws->sum('amount'), 2);
                $data['total_quantitys'] = round($withdraws->sum('quantity'), 2);
                break;
            case 3:
                $deferreds = $this->getDatas($request);
                $data['total_amounts'] = round($deferreds->sum('amount'), 2);
                $data['total_fee_moneys'] = round($deferreds->sum('fee_money'), 2);
                $data['total_real_amounts'] = round($deferreds->sum('real_amount'), 2);
                break;
            case 4:
                $deferreds = $this->getDatas($request);
                $data['total_golds'] = round($deferreds->sum('gold'), 2);
                $data['total_rmbs'] = round($deferreds->sum('rmb'), 2);
                break;
            case 5:
                $deferreds = $this->getDatas($request);
                $data['total_amounts'] = round($deferreds->sum('amount'), 2);
                break;
        }
        return $data;
    }

    public function getCross(Request $request,DealContractRepository $repository)
    {
        try{
            // 查询穿仓id
            $position_ids = collect(Redis::hVals('deal:contracts'))->map(function ($item,$key){
                return json_decode($item,true);
            })->reject(function ($item,$key){
                return $item['stock_num'] <= 0 || $item['deposite_money'] + $item['loss'] >= 0;
            })->pluck('id')->all();
            $lists = $repository->where(function ($query) use ($position_ids) {
                $query->where('contract_status', 0);
                $query->where('settle_total','<', 0);
                $query->whereRaw('deposite_money < abs(settle_total)');
                $query->orWhereIn('id', $position_ids);
            })
                ->with([
                    'customer:id,no,realname,phone',
                    'agent:id,no,name',
                    'trade:name',
                    'stock.market'
                ])
                ->lists();
            // 计算用户总资产、可用余额
            if ($lists){
                foreach ($lists as $k => $v){
                    $lists[$k]->total_balance = round($v->customer->dealContracts->whereNotIn('contract_status',[0,9])->sum('loss') + $v->customer->wallet->account_balance,2);
                    $lists[$k]->usable_balance = round($v->customer->wallet->usable_balance,2);
                    $lists[$k]->threshold = round(($v->customer->wallet->usable_balance + $v->customer->wallet->frozen_balance) * BaseFacade::platform('assets_ratio'),2);
                    $lists[$k]->buy_time = $v->buy->created_at->toDateTimeString();
                    $lists[$k]->see = $v->customer->dealContracts->where('stock_num','>',0)->first() ? 1 : 0;
                }
            }
            return $lists;
        }catch (\Exception $e){
            $this->exception($e);
            return $this->validation('穿仓数据获取异常');
        }
    }

    public function bankruptcyData(Request $request,CustomerWalletRepository $repository)
    {
        try{
            $lists = $repository->with(['customer.dealContracts' =>function($query){
                $query->where('contract_status','<>',0);
                $query->with(['stock.market']);
            },'agent:id,name,no'])->lists();
            foreach ($lists as $k =>  $list) {
                $list->loss = round($list->customer->dealContracts->where('contract_status','<>',9)->sum('loss'),2); // 当前盈亏
                $list->total_balance = round($list->account_balance + $list->loss,2);
                if ($list->total_balance >= 0){
                    unset($lists[$k]);
                    continue;
                }
                $list->total_market = round($list->customer->dealContracts->sum('total_market'),2); // 当前市值
            }
            return $lists->values()->all();
        }catch (\Exception $e){
            $this->exception($e);
            return $this->validation('资产穿仓统计异常');
        }
    }
}
