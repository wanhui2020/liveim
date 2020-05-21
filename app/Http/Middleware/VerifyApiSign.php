<?php

namespace App\Http\Middleware;

use App\Traits\ResultTrait;
use Closure;
use Illuminate\Support\Facades\Log;

class VerifyApiSign
{
    use ResultTrait;

    /**
     * 用于API鉴权
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        $debug = env('APP_DEBUG', true);
//        //调试模式不验证
//        if(!$debug){
//            $data = $request->all();
//            if (!$this->checkSign($data)) {
//                echo json_encode($this->validation('签名错误！'), JSON_UNESCAPED_UNICODE);
//                exit;
//            }
//        }
        return $next($request);
    }

    /*
     * 接口sign检查
     * */
    public function checkSign($data)
    {
        try {
            $appkey = env('appkey', 'hudiegu');//
            $secret = env('secret ', 'f88c45a07b2e53c62363af54ac3724d7'); //密钥
            $sign = $this->getHeaders('sign');

            ksort($data); //键升序排序
            $str = $appkey;
            foreach ($data as $key => $item) {
                if (!is_null($item)) {
                    $str .= $key . '=' . $item; //参数不为空的才拼接
                }
            }
            $str .= $secret;
            $cksign = strtoupper(md5($str));

            if ($cksign != $sign) {
                return false;
            }
            return true;

        } catch (\Exception $ex) {
            $this->logs('验签异常', $ex, 'error');
            return false;
        }
    }

    public function getHeaders($keyValue)
    {
        try {
            $headers = array();
            foreach ($_SERVER as $key => $value) {
                if ('HTTP_' == substr($key, 0, 5)) {
                    $headers[str_replace('_', '-', substr($key, 5))] = $value;
                }
            }
            return $headers[strtoupper($keyValue)];
        } catch (\Exception $ex) {
            return '';
        }
    }
}
