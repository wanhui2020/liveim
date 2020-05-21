<?php

namespace App\Http\Resources\Member;

use App\Models\MemberFileView;
use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//主播会员礼物
class MemberGiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->gift_name,//礼物名称
            'quantity' => $this->quantity,//数量
            'url' => $this->url
        ];
    }
}
