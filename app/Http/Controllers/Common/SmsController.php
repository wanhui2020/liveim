<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Facades\SmsFacade;

class SmsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    /**
     * 发送验证码
     * @param $phone
     */
    public function sendCode(Request $request)
    {
        try {
            $phone = $request->phone;
            $expiry = $request->expiry ?? 60;
            if (empty($phone)) {
                return $this->validator('手机号不能为空');
            }
            $resp = SmsFacade::sendCode($phone, $expiry);
            if ($resp['status']) {
                return $this->succeed($resp['msg'],$resp['data']);// 放入测试验证码 $resp['data']
            }
            return $this->failure(1,'失败：请60秒后再试！');

        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * 效验验证码
     * @param $phone
     * @param $code
     */
    public function verifyCode(Request $request)
    {
        $phone = $request->phone;
        $code = $request->code;
        if (empty($phone) || $code) {
            return $this->validator('手机号和验证码不能为空');
        }

        $resp = SmsFacade::verifyCode($phone, $code);
        if ($resp->status == 0) {
            return $this->succeed('效验成功');
        }
        return $this->failure(1,'效验失败');
    }
}
