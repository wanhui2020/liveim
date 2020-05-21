<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

//协议管理
class SystemAgreementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,//协议管理的名称
            'content' => $this->content,//协议管理的内容
            'type' => $this->type,//协议管理的类型
            'status' => $this->status,//状态 0:正常 1:禁用
        ];
    }
}
