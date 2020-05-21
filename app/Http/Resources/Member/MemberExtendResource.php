<?php

namespace App\Http\Resources\Member;

use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//会员扩展信息管理
class MemberExtendResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'code' => $this->code,//会员编号
            'head_pic' => $this->head_pic,//头像
            'nick_name' => $this->nick_name,//昵称
            'invitation_code' => $this->invitation_code,//会员邀请码
            'sex' => $this->sex == 0 ? '男' : '女',//性别
            'signature' => Helper::nulltostr($this->extend->signature),//签名
            'hobbies' => Helper::nulltostr($this->extend->hobbies),//兴趣爱好
            'city' => Helper::nulltostr($this->extend->city),//所在城市
            'address' => Helper::nulltostr($this->extend->address),//联系地址\
            'height' => $this->extend->height,//身高
            'weight' => $this->extend->weight,//体重
            'constellation' => $this->extend->constellation,//星座
            'text_fee' => $this->extend->text_fee,//一般消息收费
            'voice_fee' => $this->extend->voice_fee,//语音消息收费
            'video_fee' => $this->extend->video_fee,//视频消息收费
            'picture_view_fee' => $this->extend->picture_view_fee,//颜照库收费
            'video_view_fee' => $this->extend->video_view_fee,//视频库收费
            'coat_fee' => $this->extend->coat_fee,//换衣收费
            'is_selfie' => $this->selfie_check,//是否自拍认证
            'is_realname' => $this->realname_check,//是否实名认证
            'is_business' => $this->business_check,//是否商务认证
            'mobile' => $this->mobile,//手机号码
            'is_setpwd' => empty($this->take_pwd) ? 0 : 1,//是否设置了提现密码
            'is_vip' => $this->account != null ? ($this->account->vip_expire_date != null ? $this->account->is_vip ? 1 : 0 : 0) : 0,//是否是vip
        ];
    }
}
