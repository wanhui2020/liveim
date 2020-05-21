<?php

namespace App\Http\Resources\Member;

use App\Models\MemberFileView;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\AbstractList;

//主播会员资源库
class MemberFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($request['view_place'] == 'mylists') {
            //主播自己查看
            return [
                'id' => $this->id,//颜照库ID
                'type' => $this->type,//类型
                'url' => $this->url,//查看地址
                'cover' => $this->is_cover,//是否封面
                'status' => $this->status, //状态代码
                'reason' => $this->deal_reason, //审核意见
            ];
        }

        $isLock = 0; //未解锁


        if ($request['memberid'] == $this->member_id || $request['picture_view_fee'] === 0) {
            //自己的不加锁  VIP看不加锁
            $isLock = 1;
        } else {
            $isLock = MemberFileView::where(['member_file_id' => $this->id, 'member_id' => $request['memberid']])->count();
        }
        return [
            'id' => $this->id,//颜照库ID
            'type' => $this->type,//类型
            'url' => $this->url,//查看地址
//            'gold' => $this->type == 0 ? $request['vido_view_fee'] : $request['picture_view_fee'],
            'gold' => $this->gold($this,$request),
            'is_lock' => $isLock
        ];

    }

    public function gold($that,$request)
    {
        if ($request['is_vip']){
            return 0;
        }else{
            if ($that->type === 0){
                return $request['vido_view_fee'];
            }else{
                return $request['picture_view_fee'];
            }
        }
    }
}
