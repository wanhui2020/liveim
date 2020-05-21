<?php


namespace App\Http\Controllers\Api\Common\System;

use App\Facades\CommonFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\SystemConfigResource;
use App\Models\MemberInfo;
use clagiordano\weblibs\configmanager\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

//系统参数
class ConfigController extends ApiController
{
    public function config(Request $request)
    {
        try {
            $config = Cache::get('SystemConfig');
            if ($config) {
                return $this->succeed(new SystemConfigResource($config));
            } else {
                return $this->failure(1, '查询系统参数配置错误');
            }
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex);
        }
    }


    //获取分享短链接
    public function getShare3url(Request $request)
    {
        try {

            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }

            //推广链接如果为空，则生成
            if (!empty($member->links)) {
                return $this->succeed($member->links);
            }
            //未生成过或不可用再生成
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/download?userid=' . $member->code;
            //创建短链接
            list($ret, $result) = CommonFacade::short_monkey_url($url);
            if (!$ret) {
                return $this->validation('创建分享链接失败!');
            }
            //成功
            $urlStr = $result;
            $member->links = $urlStr;
            $member->save(); //保存到用户

            return $this->succeed($urlStr,$urlStr);

        } catch (\Exception $ex) {
            return $this->validation('获取分享链接失败', $ex->getMessage());
        }
    }
}
