<?php

namespace App\Services;

//风控服务
use App\models\ContractContracts;
use App\Models\CustomerUser;
use App\Models\StockInfo;
use App\Models\StockPositions;
use App\Traits\ResultTrait;

class RiskService
{
    use ResultTrait;

    /**
     * 用户风控
     * @param CustomerUser $customer
     * @return array
     */
    public function CustomerRisk(CustomerUser $customer)
    {
        try {
            if ($customer->status == 1 || $customer->status == 2) {
                return $this->failure(1, '账户异常');
            }
            //检查现金余额
            $wallet = $customer->wallet;
            $records = $customer->cashRecords()->orderBy('id', 'desc')->first();
            if ($records && $wallet->balance != $records->after_balance) {
                return $this->failure(1, '账户资金和流水异常');
            }
            return $this->succeed('', '风控正常');
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * 获取用户的风控信息
     * @param CustomerUser $customer
     * @return array
     */
    public function getCustomerRisk(CustomerUser $customer)
    {
        return ['contract_warn' => 0.5, 'contract_die' => 0.7, 'contract_stock' => 0.8];
    }

    /**
     * 委托买票风控
     * @param ContractContracts $contract
     * @param CustomerUser $customer
     * @param StockInfo $stock
     * @return array
     */
    public function entrustBuyRisk(ContractContracts $contract, StockInfo $stock)
    {
        // todo 是否交易日判断
        // todo 是否在交易时间段判断
        // todo 客户信息验证
        //策略验证
        if ($contract->status == 1) {
            return $this->failure(1, '策略已结束');
        }
        // todo 平台风控验证
        // todo 股票信息验证
        // todo 风险股票限制
        // todo 买入风控
        // todo 资金合算
        // todo 资金使用比例
        /*
        if ($category == 0) {
            $stock = StockInfo::where('no', $stockNo)->first();
            if ($stock->status != 0) {
                return Result::validator('当前股票全网禁售', $stock, 'obj');
            }
            if ($stock->stock_status != 0) {
                return Result::validator('当前股票状态已停盘', $stock, 'obj');
            }
            if ($stock->risk_grade == 0) {
                return Result::validator('当前股票风险等级禁止买入委托', $stock, 'obj');
            }
            if ($stock->blacklist_status != 0) {
                return Result::validator('当前股票风险列入黑名单禁止买入委托', $stock, 'obj');
            }
            if ($stock->risk_status == 1) {
                return Result::validator('风险股票不可委托', $stock, 'obj');
            }
            $re = $this->customerRiskWarning($customerNo);
            if ($re->status != 0) {
                return Result::validator($re->msg, $contract, 'obj');
            }

            if ($contract->total_balance < $price * $quantity) {
                return Result::validator('策略可操作资金不足，请充值', $contract, 'obj');
            }
            $riskPlatform = $this->platformRisk();
            $totalCost = Order::where('stock_no', $stockNo)->where('status', 0)->sum('total_cost');
            if ($totalCost > $riskPlatform->stock_quota) {
                return Result::validator($riskPlatform->stock_quota . '单票风控超限' . $totalCost, '平台风控验证', 'obj');
            }

        }*/
        return $this->succeed('', '风控验证通过');
    }

    /**
     * 卖票风控
     * @param StockPositions $position
     * @return array
     */
    public function entrustSellRisk(StockPositions $position)
    {
        return $this->succeed();
    }
}