<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

//系统参数
class SystemConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'app_name' => $this->name,//平台名称
            'version' => $this->version,//版本号
            'ios_version' => $this->ios_version,//IOS版本号
            'down_url' => $this->android_down,//安卓下载地址
            'ios_url' => $this->ios_down,//IOS下载地址
            'icon' => Helper::nulltostr($this->app_pic),//APP图标
            'address' => Helper::nulltostr($this->address),//联系地址
            'tel' => Helper::nulltostr($this->tel),//客服热线
            'wechat' => Helper::nulltostr($this->weixin),//客服微信
            'fwxy' => Helper::nulltostr($this->fwxy),//服务协议
            'vip_explain' => Helper::nulltostr($this->vip_explain),//VIP说明
            'about_us' => Helper::nulltostr($this->about_us),//关于我们
            'warm_prompt' => Helper::nulltostr($this->warm_prompt),//商务温馨提示
            'vip_privilege' => Helper::nulltostr($this->vip_privilege),//VIP特权说明
            'app_explain' => Helper::nulltostr($this->app_explain),//微游说明
            'takenow_explain' => Helper::nulltostr($this->takenow_explain),//提现说明
        ];
    }
}
