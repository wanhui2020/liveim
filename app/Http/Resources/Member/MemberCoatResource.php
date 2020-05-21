<?php

namespace App\Http\Resources\Member;

use App\Models\MemberFileView;
use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//主播衣服
class MemberCoatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,//衣服ID
            'title' => $this->title,//衣服标题
            'url' => $this->url,//图片地址
        ];
    }
}
