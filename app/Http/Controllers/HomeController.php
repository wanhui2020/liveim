<?php

namespace App\Http\Controllers;


use App\Facades\AliyunFacade;
use App\Facades\PushFacade;
use App\Models;
use App\Models\PlatformEdition;
use GuzzleHttp\Client;

class HomeController extends Controller
{

    public function __construct()
    {
//        $this->middleware('auth:system');
    }

    function ordersn()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%04d%02d', rand(1000, 9999), rand(0, 99));
        return $orderSn;
    }

    public function index()
    {
abort(404);
    }

    public function share()
    {
        return view('share');
    }

    public function testflight()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            return view('testflight');
        }
        return redirect('https://appkk.top/3XcyWo');
    }

    /**
     * 测试方法
     */
    public function test()
    {
        dd(AliyunFacade::DescribeVerifyResult('337'));
        return 123;

//        $token = ['cb4bd7299691a816f0f6c3374271ac66b47eca55'];
//        $data = PushFacade::iosGeneral('测试推送', '这是一条测试推送到ios的信息', $token);
//
        $token = ['cb4bd7299691a816f0f6c3374271ac66b47eca55'];
        $data = PushFacade::androidMessage('测试单推', '这是一条测试推送到android的单推信息');
        return $this->succeed($data);

        //微信app支付
//        $orderno = $this->ordersn();
//        //1.统一下单方法
//        $appid = "wxf40312b43b92191c";
//        $mch_id = "1558282021";
//        $notify_url = "http://live.weiyou8888.cn/callback/pay/appwechat/recharge";
//        $key = "zhaocheng11637501641762365756888";//
//        $wechatAppPay = new WechatAppPay($appid, $mch_id, $notify_url, $key);
//        $params['body'] = '会员金币充值';                       //商品描述
//        $params['out_trade_no'] = $orderno;    //自定义的订单号
//        $params['total_fee'] = 1;                       //订单金额 只能为整数 单位为分
//        $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
//        $result = $wechatAppPay->unifiedOrder($params);
////        var_dump($result);die();
//        //print_r($result); // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
//        //2.创建APP端预支付参数
//        /** @var TYPE_NAME $result */
////        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
//
//        $data['appid'] = $appid;
//        $data['partnerid'] = $mch_id;
//        $data['prepayid'] = $result['prepay_id'];
//        $data['package'] = 'Sign=WXPay';
//        $data['noncestr'] = $wechatAppPay->genRandomString();
//        $data['timestamp'] = time();
////        $data['sign'] = $wechatAppPay->MakeSign($data);
//
//        ksort($data);
//        $string = $wechatAppPay->ToUrlParams($data);
//        //签名步骤二：在string后加入KEY
//        $string = $string . "&key=" . $key;
//        $data['string'] = $string;
//
//        $string = md5($string);
//        //签名步骤四：所有字符转为大写
//        $sign = strtoupper($string);
//
//        $data['sign'] = $sign;


        //下边为了拼够参数，多拼了几个给安卓、ios前端
//        $data['body'] = '会员金币充值';
//        $data['notify_url'] = $notify_url;
//        $data['total_fee'] = 1;
//        $data['success'] = 1;
//        $data['spbill_create_ip'] = '127.0.0.1';
//        $data['out_trade_no'] = $orderno;
//        $data['trade_type'] = 'APP';


        //var_dump($data);die();
//        return $data;
        // 根据上行取得的支付参数请求支付即可
        //print_r($data);
//        BASETOOL::ajaxResponse($this->error['RETURN_SUCCESS']['code'], $data);


        //支付
//        $resp = PayFacade::alipay(rand(), 1);
//        //查询
////        $resp = PayFacade::alipayQuery('2129209909');
//        if ($resp['status']) {
//            return $resp['data'];
//        }
//        $res = PayFacade::pddPay(rand(), 13);
//
//        if (!array_key_exists('code', $res) || $res['code'] != 200) {
//            return $this->validation($res['msg']);
//        }
//
////        return $res['data']['h5_url'];
//
//        header('Location:' . $res['data']['h5_url']);

//        $urlStr = 'https://w.url.cn/s/AahnRu5';
//        $index = trim(strrchr($urlStr, '/'),'/');
//        list($ret, $data) = CommonFacade::short_3url_info([$index]);
//
////        list($ret, $data) = CommonFacade::short_3url_list();
////
//
//        return $data;
//        if ($data['count'] > 0) {
//            $lists = $data['lists'];
//            $index = array_rand($data['lists'], 1);
//            return $this->succeed($lists[$index]['shorturl']);
//        }
//        if ($ret) {
//            return $data;
//        }
//        return '失败' . $data;

//        return $data;


        //跳转
//        header('Location:' . 'http://' . $_SERVER['HTTP_HOST'] . '/download?userid=12345');
//        exit();

//        $member = MemberInfo::where('vv_busy', 0)->orderBy('updated_at', 'desc')->first();
//        if (isset($member)) {
//            $account = [$member->id];
//            return ImFacade::userStatus($account);
//        }
//        return $this->failure();
//        $account = [0];
//        list($ret, $data) = ImFacade::userStatus($account);
//        if(!$ret){
//            return $data;
//        }
//        if ($data['ActionStatus'] == "OK") {
//            $queryStatus = $data['QueryResult'][0]['State'];
//            return $queryStatus;
//        }
//        return $data;

//        $url = 'http://live.weiyou8888.cn/download?userid=1';
//        //创建短链接
////        $data = array('link' => $url, 'info' => '微游社交平台');
//        list($ret, $result) = CommonFacade::short_monkey_url($url);
//        return $this->validation('创建分享链接失败!',$result);


    }

    //下载页面
    public function download()
    {
        return view('download');
    }

}
