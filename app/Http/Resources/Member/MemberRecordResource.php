<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Resources\Json\JsonResource;

/*
 * 会员流水记录
 * */

class MemberRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->type_cn,//资金流水项
            'amount' => floatval($this->amount),//金额
            'unit' => $this->account_type == 0 ? '元' : '币',//资金流水项
            'time' => date('Y-m-d H:i:s', strtotime($this->created_at)),//时间
        ];
    }
}
