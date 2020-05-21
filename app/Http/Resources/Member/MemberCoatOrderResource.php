<?php

namespace App\Http\Resources\Member;

use App\Models\MemberFileView;
use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//主播衣服
class MemberCoatOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($request['view_type'] == 'list') {
            return [
                'id' => $this->id,//订单ID
                'title' => $this->coat == null ? '' : $this->coat->title,//衣服标题
                'member_code' => $this->member->code,//发起会员编号
                'member_nick_name' => $this->member->nick_name,//发起会员昵称
                'status' => $this->status,//状态码
                'status_cn' => $this->status_cn,//状态中文
            ];
        }
        return [
            'id' => $this->id,//订单ID
            'title' => $this->coat == null ? '' : $this->coat->title,//衣服标题
            'url' => $this->coat == null ? '' : $this->coat->url,//衣服地址
            'zb_code' => $this->tomember->code,//所属主播编号
            'zb_nick_name' => $this->tomember->nick_name,//所属主播昵称
            'member_code' => $this->member->code,//发起会员编号
            'member_nick_name' => $this->member->nick_name,//发起会员昵称
            'status' => $this->status,//状态码
            'status_cn' => $this->status_cn,//状态中文
        ];
    }
}
