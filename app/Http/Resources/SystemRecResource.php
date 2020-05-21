<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//平台充值项
class SystemRecResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'type' => $this->type_cn,//类型名称
            'name' => $this->name,//项目名称
            'old_cost' => floatval($this->old_cost),//原价
            'cost' => floatval($this->cost),//现价
            'gold' => $this->quantity,//对应金币
            'give_gold' => $this->give,//普通会员赠送金币
            'vip_give_gold' => $this->vip_give,//VIP会员赠送金币
            'remark' => Helper::nulltostr($this->remark)//备注说明
        ];
    }
}
