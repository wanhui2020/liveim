<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/*
 * 积分规则
 * */

class ScoreRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->desc == 5) {
            return [
                'key' => $this->remark,//对应项
                'value' => $this->score,//对应值
            ];
        }
        $score = $this->score;
        if ($score == 0) {
            $value = $this->remark; //积分的话，只显示
        } else {
            $value = $score . '/' . $this->remark;
        }
        return [
            'key' => $this->desc_cn,//对应项
            'value' => $value,//对应值
        ];
    }
}
