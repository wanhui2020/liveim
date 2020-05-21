<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//系统消息
class SystemMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'content' => Helper::nulltostr($this->content),
            'member_id' => Helper::nulltostr($this->member_id),
            'member_code' => $this->member == null ? '' : $this->member->code,
            'member_nickname' => $this->member == null ? '' : $this->member->nick_name,
            'send_time' => date('Y-m-d H:i:s', strtotime($this->created_at)),//发送时间
        ];
    }
}
