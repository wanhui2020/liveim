<?php


namespace App\Http\Controllers\Api\Common;

use App\Facades\PayFacade;
use App\Facades\SmsFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\CommonRequest;
use Illuminate\Http\Request;

class CommonController extends ApiController
{
    public function test(Request $request)
    {
        $resp = PayFacade::alipay(rand(), 1);
        return $resp;

    }

    /**
     *  验证码发送
     * @param Request $request
     * @return array
     */
    public function sendCode(CommonRequest $request)
    {
        try {
            $phone = $request->phone;
            $expiry = $request->expiry ?? 60;
            if (empty($phone)) {
                return $this->validator('手机号不能为空');
            }
            $resp = SmsFacade::sendCode($phone, $expiry);
            if ($resp['status']) {
                return $this->succeed(['code' => $resp['data']], $resp['msg']);// 放入测试验证码 $resp['data']
            }
            return $this->validation($resp['msg']);

        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     *  获取充值方式
     * @return array
     */
    public function getPayType()
    {
        try {
            $payments = PlatformPayment::where('status', 0)->get();
            return $this->paginate(PlatformPaymentResource::collection($payments), '成功');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function getConfig()
    {
        try {
            $platconfig = PlatformConfig::first();
            return $this->succeed(new PlatformConfigResource($platconfig));
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
