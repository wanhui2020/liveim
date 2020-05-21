<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

//礼物
class SystemGiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'giftid' => $this->id, //礼物ID
            'title' => $this->title,//礼物名称
            'gold' => $this->gold,//价值金币
            'url' => $this->url//效果地址
        ];
    }
}
