<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

//banner图
class SystemBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,//banner图的名称
            'url' => $this->url,//banner图的url
        ];
    }
}
