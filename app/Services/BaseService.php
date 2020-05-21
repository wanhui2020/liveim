<?php

namespace App\Services;

use App\Models\PlatformConfig;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 基础服务
 * @package App\Http\Service
 */
class BaseService
{

    public function __construct()
    {

    }


    /**
     *获取用户信息
     * @param $key
     * @return mixed
     */
    public function user($key = null)
    {
        if (Auth::guard('system')->guest()) {
            return null;
        }
        $user = Auth::guard('system')->user();

        if ($key) {
            return $user->$key;
        }
        return $user;
    }

    /**
     *获取代理商信息
     * @param $key
     * @return mixed
     */
    public function agent($key = null)
    {
        if (Auth::guard('agent')->guest()) {
            return null;
        }
        $user = Auth::guard('agent')->user();

        if ($key) {
            return $user->$key;
        }
        return $user;
    }

    /**
     * 获取企业参数配置
     * @param $key
     * @return mixed
     */
    public function config($key = null)
    {
        if (Cache::has('SystemConfig')) {
            $config = Cache::get('SystemConfig');
        } else {
            $config = SystemConfig::first();
            Cache::forever('SystemConfig', $config);
        }
        if ($key) {
            return $config->$key;
        }
        return $config;
    }

    /**
     * 关键字过滤
     * @param $key
     * @return mixed
     */
    public function keyword($key)
    {
        $keyword = $this->config('keyword');
        if (isset($keyword)) {
            $a = explode(',', $keyword);
            foreach ($a as $item) {
                if (strpos($key, $item) !== false) {
                    return false;
                }
            }
        }
        return true;

    }

    /**
     * 字符串替换
     * @param $key
     * @return bool
     */
    public function replacekeyword($key)
    {
        $keyword = $this->config('keyword');
        if (isset($keyword)) {
            $a = explode(',', $keyword);
            foreach ($a as $item) {
                if (strpos($key, $item) !== false) {
                    return false;
                }
            }
        }
        return true;

    }
    /**
     * 获取平台参数
     * @param $key
     * @return mixed
     */
    public function platform($key = null)
    {
        if (Cache::has('PlatformConfig')) {
            $config = Cache::get('PlatformConfig');
        } else {
            $config = PlatformConfig::first();
            Cache::forever('PlatformConfig', $config);
        }
        if ($key) {
            return $config->$key;
        }
        return $config;
    }


    /**
     * 获得日期前缀的固定长度15位系统编号生成
     * @param $type
     * @param int $newNo
     * @return string
     */
    public function getFixedDateNumber($type, $newNo = 1)
    {
        $num = $this->getNumber($type, $newNo);
        return date('Ymd') . str_pad($num, 7, 0, STR_PAD_LEFT);
    }

    /**
     * 隐藏数字（手机号，身份证，银行卡）
     * @param $str
     * @return mixed
     */
    public function hideNumber($str, $start = 3, $end = 4)
    {
        if ($str) {
            $len = strlen($str) - $start - $end;
            $stars = '';
            for ($i = 0; $i < $len; $i++) {
                $stars .= '*';
            }
            return substr_replace($str, $stars, $start, $end);
        }
        return $str;
    }
}
