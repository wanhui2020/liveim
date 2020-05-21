<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Resources\Json\JsonResource;

//主播衣服
class MemberSelfieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'pic' => $this->pic,//图片
            'status' => $this->status,//状态码
            'status_cn' => $this->status_cn,//状态中文
            'deal_time' => $this->deal_time,//审核时间
            'deal_reason' => $this->deal_reason,//审核描述
        ];
    }
}
