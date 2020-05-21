<?php

namespace App\Http\Resources\Member;

use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//会员关注表
class MemberAttentionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        //显示简单基本信息
        return [
            'toid' => $this->to_member_id,
            'nick_name' => $this->tomember->nick_name,//昵称
            'head_pic' => $this->tomember->head_pic,//头像
            'signature' => isset($this->tomember->extend) ? Helper::nulltostr($this->tomember->extend->signature) : '',//签名
            'meili' => $this->tomember->meili > 0 ? '魅' . $this->tomember->meili : '', //魅力值
            'haoqi' => $this->tomember->haoqi > 0 ? '壕' . $this->tomember->haoqi : '', //壕气值
            'level' => $this->tomember->level == null ? '' : 'LV' . $this->tomember->level->lvl . $this->tomember->level->name, //等级
        ];
    }
}
