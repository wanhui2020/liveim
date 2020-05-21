<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Resources\Json\JsonResource;

//会员账户信息表
class MemberAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'surplus_gold' => $this->surplus_gold,//账户金币
            'notuse_gold' => $this->notuse_gold,//冻结不可用金币
//            'balance_gold' => $this->balance_gold,//实际可用金币
            'balance_gold' => $this->balance_gold,//会员可提现金币
//            'cantx_gold' => $this->surplus_gold - $this->lock_gold < 0 ? 0 : $this->surplus_gold - $this->lock_gold ,//前端用户可以用来兑换的金币
            'cantx_gold' => $this->cantx_gold($this) ,//前端用户可以用来兑换的金币
            'lock_gold' => $this->lock_gold,//不可提现金币 锁定金币
            'surplus_rmb' => round($this->surplus_rmb,2),//账户余额
            'notuse_rmb' => $this->notuse_rmb,//不可用余额
            'cantx_rmb' => round($this->cantx_rmb,3),//可提现余额
            'selfie_check' => $this->member->selfie_check,//是否自拍认证，0否 1是
            'business_check' => $this->member->business_check,//商务认证
            'realname_check' => $this->member->realname_check,//实名认证
            'is_business' => $this->member->extend->is_business,//会员是否实名开启动商务
            'is_vip' => $this->vip_expire_date != null ? $this->is_vip ? 1 : 0 : 0,//是否是vip
        ];
    }
    public function cantx_gold($that)
    {
        if ($that->lock_gold < 0){
//            $cantx_gold = $that->surplus_gold + $that->lock_gold;
//            if ($cantx_gold < 0){
//                return 0;
//            }
            return $that->surplus_gold;
        }else{
            $cantx_gold = $that->surplus_gold - $that->lock_gold;
            if ($cantx_gold < 0){
                return 0;
            }
            return $cantx_gold;
        }
    }
}
