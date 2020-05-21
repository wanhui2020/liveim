<?php

namespace App\Http\Resources\Member;

use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//会员好友表
class MemberFriendsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        //显示我的好友
        if ($request['view_type'] == 'my') {
            return [
                'friend_id' => $this->to_member_id,
                'friend_code' => $this->tomember->code, //编号
                'friend_nick_name' => $this->tomember->nick_name,//昵称
                'friend_head_pic' => $this->tomember->head_pic,//头像
                'signature' => Helper::nulltostr($this->tomember->extend->signature),//签名
            ];
        }
        return [
            'id' => $this->id, //记录ID
            'fromid' => $this->member_id,
            'toid' => $this->to_member_id,
            'status' => $this->status,
            'from_code' => $this->member->code,//昵称
            'from_nick_name' => $this->member->nick_name,//昵称
            'from_head_pic' => $this->member->head_pic,//头像
            'to_code' => $this->tomember->code,//编号
            'to_nick_name' => $this->tomember->nick_name,//昵称
            'to_head_pic' => $this->tomember->head_pic,//头像

        ];
    }
}
