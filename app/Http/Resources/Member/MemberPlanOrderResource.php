<?php

namespace App\Http\Resources\Member;

use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//主播订单
class MemberPlanOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($request['view_type'] == 'planlist') {
            return [
                'id' => $this->id,//订单ID
                'project' => $this->project,//选择项
                'content' => $this->content,//内容
                'sort' => $this->sort,//排序
                'pics' => $this->pics//
            ];
        }
        if ($request['view_type'] == 'list') {
            return [
                'id' => $this->id,//订单ID
                'order_no' => $this->order_no,//订单号
                'date' => $this->service_date,//服务日期
                'project' => $this->project,//选择项
                'amount' => floatval($this->amount),//服务金额
                'member_nick_name' => $this->member->nick_name,//发起会员昵称
                'zb_nick_name' => $this->tomember->nick_name,//主播昵称
                'zb_head_pic' => $this->tomember->head_pic,//主播头像
                'zb_id' => $this->tomember->id,//主播id
                'status' => $this->status,//状态码
                'pay_status' => $this->pay_status,//状态码
                'status_cn' => $this->status_cn,//状态中文
                'way' => $this->way,//支付方式
                'zb_mobile' => $this->tomember->mobile,//主播电话
                'search_from' => $request['search_from'],//0发起方 1接收方
                'create_time' => date('Y-m-d H:i:s', strtotime($this->created_at)),//创建时间
            ];
        }
        return [
            'id' => $this->id,//订单ID
            'zb_code' => $this->tomember->code,//所属主播编号
            'zb_nick_name' => $this->tomember->nick_name,//所属主播昵称
            'zb_head_pic' => $this->tomember->head_pic,//所属主播头像
            'zb_signature' => Helper::nulltostr($this->tomember->extend->signature),//签名
            'zb_mobile' => $this->tomember->mobile,//主播电话
            'member_code' => $this->member->code,//发起会员编号
            'member_nick_name' => $this->member->nick_name,//发起会员昵称
            'status' => $this->status,//状态码
            'pay_status' => $this->pay_status,//状态码
            'refund_status'=>$this->refund_status($this->status,$this->pay_status),//退款状态
            'status_cn' => $this->status_cn,//状态中文
            'remark' => $this->remark,//备注项
            'date' => $this->service_date,//服务日期
            'projectList' => $this->projects(),//选择项
            'order_no' => $this->order_no,//订单号
            'way' => $this->way,//支付方式
            'score' => $this->score,//评分; 0普通 1满意 2很满意
            'evaluation' => $this->evaluation,//评价内容
            'amount' => floatval($this->amount),//服务金额
            'create_time' => date('Y-m-d H:i:s', strtotime($this->created_at)),//创建时间
        ];
    }

    /**
     * 退款状态
     *  pay_status支付状态
     *  status状态
     *  1可以退单
     *  0不可以退单
     */
    public function refund_status($status,$pay_status)
    {
//        dd($status,$pay_status);
        if ($pay_status == 3){
            return 1;
        }elseif($pay_status == 1 && $status == 1){
            return 1;
        }elseif($pay_status == 1 && $status == 2){
            return 1;
        } elseif($status == 3){
            return 0;
        }elseif($status == 5){
            return 0;
        }elseif($status == 4){
            return 0;
        }elseif($status == 7){
            return 0;
        }elseif($status == 9){
            return 0;
        }
        else{
            return 0;
        }
    }
}
