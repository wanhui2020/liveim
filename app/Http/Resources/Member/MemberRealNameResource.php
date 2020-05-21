<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Resources\Json\JsonResource;

//主播衣服
class MemberRealNameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'cert_no' => $this->cert_no,//身份证号码
            'name' => $this->name,//真实名称
            'zm' => $this->cert_zm,//身份证正面
            'fm' => $this->cert_fm,//身份证反面
            'sc' => $this->cert_sc,//手持身份证
            'selfie' => $this->selfie,//自拍照
            'status' => $this->status,//状态
            'status_cn' => $this->status_cn,//状态描述
            'deal_time' => $this->deal_time,//审核时间
            'deal_reason' => $this->deal_reason,//审核描述
        ];
    }
}
