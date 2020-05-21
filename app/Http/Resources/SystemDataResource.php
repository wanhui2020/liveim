<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//平台基础数据
class SystemDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if($request['view_type']=='tags')
        {
            return [
                'id' => $this->id,//分类标签ID
                'name' => $this->name,//标签名称
            ];
        }

        return [
            'id' => $this->id,//分类标签ID
            'type' => $this->type_cn,//数据类型名称
            'key' => $this->key,//数据键
            'value' => $this->value,//数据值
            'remark' => Helper::nulltostr($this->remark)//备注说明
        ];
    }
}
