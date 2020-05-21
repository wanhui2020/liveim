<?php

namespace App\Http\Resources\Member;

use App\Models\MemberFile;
use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//会员信息管理
class MemberInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        //主播列表显示项
        if ($request['view_place'] == "zblist") {
            return [
                'id' => $this->id,//主播ID
                'code' => $this->code,//编号
                'nick_name' => Helper::nulltostr($this->nick_name),//昵称
                'head_pic' => Helper::nulltostr($this->head_pic),//头像
                'cover' => $this->covers()->first() == null ? '' : $this->covers()->first()->url, //封面图
                'video_fee' => $this->extend ? $this->extend->video_fee : 0,//视频消息收费
                'sex' => $this->sex,//性别
                'selfie_check' => $this->selfie_check,//自拍认证
                'vv_busy' => $this->online_status == 1 ? $this->vv_busy : '2', //忙碌状态（0空闲 1忙碌）
            ];
        }
        if ($request['view_place'] == "zblist_tag") {
            $covers = MemberFile::where(['is_cover' => 1, 'status' => 1, 'member_id' => $this->id])->first();
            return [
                'id' => $this->id,//主播ID
                'code' => $this->code,//编号
                'nick_name' => Helper::nulltostr($this->nick_name),//昵称
                'head_pic' => Helper::nulltostr($this->head_pic),//头像
                'cover' => $covers == null ? '' : $covers->url, //封面图
                'video_fee' => isset($this->extend) ? $this->extend->video_fee : 0,//视频消息收费
                'vv_busy' => $this->online_status == 1 ? $this->vv_busy : '2', //忙碌状态（0空闲 1忙碌）
            ];
        }
        if ($request['view_place'] == "swlist") {
            return [
                'id' => $this->id,//主播ID
                'code' => $this->code,//编号
                'nick_name' => Helper::nulltostr($this->nick_name),//昵称
                'head_pic' => Helper::nulltostr($this->head_pic),//头像
                'cover' => $this->covers()->first() == null ? '' : $this->covers()->first()->url . '?x-oss-process=image/format,jpg', //封面图
                'signature' => Helper::nulltostr($this->extend->signature),//签名
                'address' => Helper::nulltostr($this->extend->address),//联系地址
                'meili' => $this->meili > 0 ? '魅' . $this->meili : '', //魅力值
                'haoqi' => "", //$this->haoqi > 0 ? '壕' . $this->haoqi : '', //壕气值
                'level' => "", //等级
                'city' =>Helper::nulltostr($this->extend->city), //等级
            ];
        }
        if ($request['view_place'] == "swinfo") {
            return [
                'id' => $this->id,//主播ID
                'code' => $this->code,//编号
                'nick_name' => Helper::nulltostr($this->nick_name),//昵称
                'head_pic' => Helper::nulltostr($this->head_pic),//头像
                'signature' => Helper::nulltostr($this->extend->signature),//签名
                'address' => Helper::nulltostr($this->extend->address),//联系地址
                'covers' => $request['cover_array'], //
                'projectList' => $request['projectList'], //
                'sin_fee' => $request['sin_fee'], //
                'mul_fee' => $request['mul_fee'], //
            ];
        }
        //男神列表显示项
        if ($request['view_place'] == "manlist") {
            return [
                'id' => $this->id,//主播ID
                'code' => $this->code,//编号
                'nick_name' => Helper::nulltostr($this->nick_name),//昵称
                'head_pic' => Helper::nulltostr($this->head_pic),//头像
                'haoqi' => $this->haoqi > 0 ? '壕' . $this->haoqi : '', //壕气值
                'signature' => $this->extend == null ? '' : Helper::nulltostr($this->extend->signature),//签名
                'vv_busy' => $this->online_status == 1 ? $this->vv_busy : '2', //忙碌状态（0空闲 1忙碌）
                'online_status' => $this->online_status, //忙碌状态（0空闲 1忙碌）
            ];
        }
        //主播详情显示项
        if ($request['view_place'] == "zbinfo") {
            return [
                'id' => $this->id,//主播ID
                'code' => $this->code,//主播ID
                'nick_name' => Helper::nulltostr($this->nick_name),//昵称
                'head_pic' => Helper::nulltostr($this->head_pic),//头像
                'covers' => $request['cover_array'], //$this->covers()->select(['url'])->get(), //封面图
                'video_fee' => $this->extend->video_fee,//视频消息收费
                'is_vip' => $this->account->is_vip,//是否VIP false:不是 true：是VIP
                'selfie_check' => $this->selfie_check,//是否自拍认证，0否 1是
                'signature' => Helper::nulltostr($this->extend->signature),//签名
                'im_token' => Helper::nulltostr($this->token),//三方IMToken
//                'meili' => $this->meili > 0 ? '魅' . $this->meili : '', //魅力值
                'meili' => $this->daycounts ? '魅' . round($this->daycounts->sum('profit_gold')/1800,0) : '', //魅力值
                'haoqi' => "", //$this->haoqi > 0 ? '壕' . $this->haoqi : '', //壕气值
                'level' => $this->level == null ? '' : 'LV' . $this->level->lvl . $this->level->name, //等级
                'address' => Helper::nulltostr($this->extend->address),//联系地址
                'is_attention' => $request['is_attention'], //是否关注(0:否 1:是)
                'is_friend' => $request['is_friend'], //是否好友(0:否 1:是)
                'visit_count' => $request['visit_count'], //访客人数
                'credit' => $request['credit'] //信誉评价

            ];
        }
        if ($request['view_place'] == "userinfo") {
            return [
                'id' => $this->id,//会员ID
                'code' => $this->code,//会员编号
                'nick_name' => Helper::nulltostr($this->nick_name),//会员昵称
                'head_pic' => Helper::nulltostr($this->head_pic),//会员头像
                'signature' => Helper::nulltostr($this->extend->signature),//签名
                'hobbies' => Helper::nulltostr($this->extend->hobbies),//兴趣爱好
                'address' => Helper::nulltostr($this->extend->address),//联系地址\
                'height' => $this->extend->height,//身高
                'weight' => $this->extend->weight,//体重
                'constellation' => $this->extend->constellation,//星座
                'is_business' => $this->extend->is_business,//是否开启商务，0关闭 1开启
            ];
        }
        return [
            'id' => $this->id,//会员ID
            'code' => $this->code,//会员编号
            'nick_name' => Helper::nulltostr($this->nick_name),//会员昵称
            'head_pic' => Helper::nulltostr($this->head_pic),//会员头像
            'api_token' => $this->api_token,//接口api_token
            'im_token' => $this->token,//三方IMToken
            'sex' => $this->sex,//性别 0 男  1女
            'meili' => $this->meili > 0 ? '魅' . $this->meili : '', //魅力值
            'haoqi' => "", // $this->haoqi > 0 ? '壕' . $this->haoqi : '', //壕气值
            'level' => $this->level == null ? '' : 'LV' . $this->level->lvl . $this->level->name, //等级
            'lx_login_days' => $this->account ? $this->account->lx_login_days :0,//连续登录天数
            'sign_days' => $this->account ? $this->account->sign_days :0,//连续签到天数
            'visit_count' => $this->account->visit_count,//被访问次数
            'is_vip' => $this->account->is_vip,//是否VIP false:不是 true：是VIP
            'is_reg' => $this->sex === -1 ? 1 : 0, //是否需要弹出修改性别，1是 0否
            'reg_time' => date('Y-m-d H:i:s', strtotime($this->created_at)), //注册时间
        ];
    }
}
