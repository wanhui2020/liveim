<?php

namespace App\Services;


use App\Models\CustomerUser;
use App\models\FinanceRecord;
use App\Models\CustomerWallet;
use App\Traits\ResultTrait;
use Illuminate\Support\Facades\DB;

//金钱服务
class FinanceService
{
    use ResultTrait;

    //充值审核
    public function rechargeAudit(){

    }

    //提现审核
    public function withdrawAudit(){

    }
}
