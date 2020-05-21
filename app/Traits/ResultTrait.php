<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ResultTrait
{
    public function paginate($data = null, $msg = null)
    {
        $arr = [
            'status' => true,
            'code' => 0,
            'msg' => $msg ?: config('errorcode.code')[0],
            'count' => method_exists($data, 'total') ? $data->total() : count($data),
            'data' => method_exists($data, 'items') ? $data->items() : $data
        ];

        return $arr;
    }

    public function succeed($data = null, $msg = null)
    {
        $arr = [
            'status' => true,
            'code' => 0,
            'msg' => $msg ?: config('errorcode.code')[0],
            'data' => $data,
        ];

        return $arr;
    }


    public function failure($code = 1, $msg = '警告', $data = null)
    {
        try {
            $arr = [
                'status' => false,
                'code' => $code,
                'msg' => $code != 1 ? config('errorcode.code')[-1] : $msg,
                'data' => $data,
            ];
            $this->logs('警告', $arr, 'warning');
            return $arr;
        } catch (Exception $ex) {
            return  $this->exception($ex);
        }
    }
    public function wait($msg = '处理中', $data = null)
    {
        try {
            $arr = [
                'status' => false,
                'code' => 9,
                'msg' =>  $msg,
                'data' => $data,
            ];

            return $arr;
        } catch (Exception $ex) {
            return  $this->exception($ex);
        }
    }


    public function error($code = 1, $msg = '错误', $data = null)
    {
        $arr = [
            'status' => false,
            'code' => $code,
            'msg' => $code != 1 ? config('errorcode.code')[$code] : $msg,
            'data' => $data,
        ];
        if ($code != 1) {
            $arr['data'] = $msg;
        }
        $this->logs('错误', $arr, 'error');

        return $arr;


    }

    public function validation($msg = '效验失败', $data = null)
    {
        $arr = [
            'status' => false,
            'code' => 2,
            'msg' => $msg,
            'data' => $data,
        ];
        return $arr;
    }


    public function exception(Exception $ex, $msg = null)
    {

        $data = [
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'msg' => $ex->getMessage(),
//            'ip' => CommonFacade::getIP(),
//            'url' => CommonFacade::getUrl(),
        ];

        $arr = [
            'status' => false,
            'code' => -1,
            'msg' => $msg ?: config('errorcode.code')[-1],
            'data' => $data,
        ];
        $this->logs('异常', $data, 'error');

        return $arr;

    }


    /**
     * 日志记录
     */
    public function logs($title, $data = 'ok', $info = 'info')
    {
        try {
            if (is_object($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            $params = ['data' => $data,
                'source' => [
                    'address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '未知',
                    'referer' => request()->fullUrl(),
                    'params' => request()->getContent(),
                    'browser' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '未知']
            ];
            if (Auth::guard('system')->check()) {
                $user = Auth::guard('system')->user();
                $params['user']['id'] = $user->id;
                $params['user']['name'] = $user->name;
            }

            Log::$info($title, $params);

        } catch (Exception $ex) {
            Log::error('日志写入异常', [$ex->getMessage()]);
            return false;
        }
        return true;
    }
}