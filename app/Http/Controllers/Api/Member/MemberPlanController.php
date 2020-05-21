<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\HyFacade;
use App\Facades\JhFacade;
use App\Facades\MemberFacade;
use App\Facades\MkFacade;
use App\Facades\PayFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Member\MemberInfoResource;
use App\Http\Resources\Member\MemberPlanOrderResource;
use App\Models\MemberPlan;
use App\Models\MemberPlanOrder;
use App\Models\MemberPlanPic;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberPlanOrderRepository;
use App\Repositories\MemberPlanPicRepository;
use App\Repositories\MemberPlanRepository;
use App\Utils\Helper;
use App\Utils\WechatAppPay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

//会员商务邀约计划
class MemberPlanController extends ApiController
{

    public function __construct(MemberPlanRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 商务订单退款
     */
    public function refund(Request $request,MemberPlanOrderRepository $repository)
    {
        try{
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $order_id = $request->id;
            $to_data_id = $request->data_id;
            if ($request->filled($order_id)){
                return $this->validation('缺少商务订单id！');
            }
            if ($request->filled($to_data_id)){
                return $this->validation('缺少退款理由id！');
            }
            $res = MemberPlanOrder::where('pay_status',4)->where('status',7)->where('member_id',$member->id)->where('id',$order_id)->first();
            if (isset($res)){
                return $this->validation('该订单已退款，请勿重复退款!');
            }
            $data = MemberPlanOrder::find($order_id); //订单对象
            if (!isset($data)) {
                return $this->failure(1, '商务订单不存在！');
            }
            $remark = $request->remark;
            if ($request->filled($remark)){
                return $this->validation('请填写备注！');
            }
            if ($data['pay_status'] == 1 && $data['status'] == 1){  //全额退款 status 1 pay_status 1
                $data['pay_status'] = 4; //待退款
                $data['status'] = 7;
                $data['remark'] = $remark;
                $data['to_data_id'] = $to_data_id;
                $data['refund_type'] = '全额退款';
                $dealStatus = 7;
                $cancel_type = 0;
            }elseif($data['pay_status'] == 1 && $data['status'] == 2){  //接单了就是部分退款 status 2 pay_status 1
                $data['pay_status'] = 2; //待退款
                $data['status'] = 1;
                $data['remark'] = $remark;
                $data['to_data_id'] = $to_data_id;
                $data['refund_type'] = '部分退款';
                $dealStatus = 7;
                $cancel_type = 0;
            }else{
                $data['pay_status'] = 4; //待退款
                $data['status'] = 7;
                $data['remark'] = $remark;
                $data['to_data_id'] = $to_data_id;
                $data['refund_type'] = '全额退款';
                $dealStatus = 7;
                $cancel_type = 0;
            }
            return MemberFacade::dealPlanOrder($data, $dealStatus, $cancel_type); //调用处理方法
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }
    /*
     * 主播发布商务计划
      * */
    public function addPlan(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            if ($member->business_check == 0) {
                return $this->validation('还未通过商务认证！');
            }
            $memberId = $member->id; //主播ID
            //参数字段
            $project = $request['project']; //项目
            $content = $request['content']; //内容
            $sort = $request['sort']; //显示排序

            if (!isset($project) || !isset($content)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }
            $id = $request['planid']; //ID
            if (isset($id)) {
                //修改
                $data = $this->repository->find($id);
                if (!isset($data)) {
                    return $this->validation('未找到计划内容！');
                }
                $data['project'] = $project;
                $data['content'] = $content;
                $data['sort'] = $sort;
                $result = $this->repository->update($data);
                if (!$result['status']) {
                    return $this->validation('修改服务项失败！');
                }
                return $this->succeed(null, '修改服务项成功！');
            }
            $picstr = $request['pics'];
            $data['member_id'] = $memberId;
            $data['project'] = $project;
            $data['content'] = $content;
            $data['sort'] = !isset($sort) ? 0 : $sort;
            $result = MemberFacade::addPlan($data, $picstr);
            if (!$result['status']) {
                return $this->validation('发起服务项失败！');
            }
            return $this->succeed(null, '发布服务项成功！');

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }

    /*
     * 主播添加商务计划图片
     * */
    public function addPlanPic(Request $request, MemberPlanPicRepository $memberPlanPicRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $planid = $request['planid']; //计划ID
            $pic = $request['pic']; //图片

            if (!isset($planid) || !isset($pic)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }

            $planModel = $this->repository->find($planid);
            if (!isset($planModel)) {
                return $this->validation('未找到商务计划！');
            }
            $picData['plan_id'] = $planid;
            $picData['pic'] = $pic;
            $result = $memberPlanPicRepository->store($picData);
            if (!$result['status']) {
                return $this->validation('添加商务计划图片失败！');
            }
            return $this->succeed($result['data']->id);

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 主播删除商务服务计划图片
    * */
    public function deletePlanPic(Request $request, MemberPlanPicRepository $memberPlanPicRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $id = $request['picid']; //删除
            if (!isset($id)) {
                return $this->validation('请输入所有必填参数！');
            }
            $memberPlanPicRepository->destroy([$id]);
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 我的服务项列表
    * */
    public function myPlanList(Request $request)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $addwhere = array();
            $addwhere['member_id'] = $member->id;
            $list = $this->repository->orderBy('project', 'asc')->orderBy('created_at', 'asc')->lists($addwhere, ['pics']);
            $request['view_type'] = 'planlist';
            return $this->succeed(MemberPlanOrderResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 主播删除商务服务计划内容
    * */
    public function deletePlan(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $id = $request['planid']; //删除计划项
            if (!isset($id)) {
                return $this->validation('请输入所有必填参数！');
            }

            //先删除所有图片
            MemberPlanPic::where('plan_id', $id)->delete();
            $this->repository->destroy([$id]);
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
    * 会员添加商务订单
    * */
    public function addPlanOrder(Request $request, MemberInfoRepository $memberInfoRepository, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            //参数
            $memberId = $member->id;
            $zbid = $request['zbid']; //主播Id
            $date = $request['date'];//邀约日期
            $project = $request['project'];//选择服务项目，多个逗号隔开
            $amount = $request['amount']; //费用
            $remark = $request['remark']; //备注说明
            $way = $request['way']; //支付方式
            $today = Carbon::today()->toDateString(); //今日
            if ($date < $today){
                return $this->validation('请预约今天之后的日期！');
            }
            if (!isset($zbid) || !isset($date) || !isset($project) || !isset($amount) || !isset($way)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }
            if ($member->realname_check == 0) {
                return $this->failure(1, '对不起，请先进行实名认证！');
            }
            if ($member->sex == 1) {
                return $this->failure(1, '对不起，主播不能发起商务邀约！');
            }
            $zbinfo = $memberInfoRepository->find($zbid);
            if ($zbinfo == null) {
                return $this->validation('主播不存在！');
            }
            if ($zbinfo->extend->is_business == 0) {
                return $this->failure(1, '该主播暂未开启商务服务！');
            }
            //判断主播当天是否已经有订单
            /**
             * 0待处理
             * 1待接单
             * 2已接单
             * 4服务中
             * 5待结算
             * 6已结算
             *
             * 3已拒绝
             * 7已退单
             * 9已取消
             */
            $count = $memberPlanOrderRepository->findWhere(['to_member_id' => $zbid, 'service_date' => $date])->whereIn('status', [0, 1, 2, 4, 5, 6])->count();
            if ($count > 0) {
                return $this->failure(1, '该主播[' . $date . ']已有商务订单！');
            }
            //计算该单主播收益
            $config = Cache::get('SystemBasic'); //取平台配置
            $business_rate = $config->business_rate; //商务平台占比
            if ($business_rate > 0) {
                $profit = round($amount * (1 - $business_rate / 100), 2);//四舍五入保留2位小数
                $data['profit'] = $profit;
            }
            $data['order_no'] = Helper::getNo(); //订单号
            $orderNo = Helper::getNo();
            $data['member_id'] = $memberId;
            $data['to_member_id'] = $zbid;
            $data['service_date'] = $date;
            $data['project'] = $project;
            $data['amount'] = $amount;
            $data['remark'] = $remark;
            $data['way'] = $way;
            $data['order_no'] = $orderNo;
            //保存
            $result = MemberFacade::addPlanOrder($data, $project);
            if (!$result['status']) {
                return $this->validation('邀约申请失败！[' . $result['msg'] . ']');
            }

            //调用微信或支付宝统一下单接口处理
            if ($way == 6) {
                //微信APP支付
                //1.统一下单方法
                $payAmount = $amount * 100;
                $appid = "wxf40312b43b92191c";
                $mch_id = "1558282021";
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/appwechat/business';
                $key = "zhaocheng11637501641762365756888";//
                $wechatAppPay = new WechatAppPay($appid, $mch_id, $notify_url, $key);
                $params['body'] = '会员金币充值';                       //商品描述
                $params['out_trade_no'] = $orderNo;    //自定义的订单号
                $params['total_fee'] = $payAmount;                       //订单金额 只能为整数 单位为分
                $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
                $result = $wechatAppPay->unifiedOrder($params);

                //2.创建APP端预支付参数
                /** @var TYPE_NAME $result */
                $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
                //下边为了拼够参数，多拼了几个给安卓、ios前端
//                $data['body'] = '会员金币充值';
//                $data['notify_url'] = $notify_url;
//                $data['total_fee'] = $payAmount;
//                $data['success'] = 1;
//                $data['spbill_create_ip'] = '127.0.0.1';
//                $data['out_trade_no'] = $orderNo;
//                $data['trade_type'] = 'APP';
//                $payParam = $data;
                $payParam = array(
                    'appid' => $data['appid'],
                    'partnerid' => $mch_id,
                    'out_trade_no' => $orderNo,
                    'prepayId' => $data['prepayid'],
                    'package' => $data['package'],
                    'nonce_str' => $data['noncestr'],
                    'timestamp' => $data['timestamp'],
                    'sign' => $data['sign'],
                );
            }

            if ($way == 3) {
                //三方支付，拼接支付地址
                $payParam = PayFacade::typay($orderNo, $amount, $member->code);
            }
            if ($way == 1) {
                //拼多支付(微信)
                $res = PayFacade::pddPay($orderNo, $amount, 'wechat', 'business');

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 2) {
                //拼多支付(支付宝)
                $res = PayFacade::pddPay($orderNo, $amount, 'alipay', 'business');
                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 8){
                //聚合支付(支付宝H5)
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/typay/business';
                $params['notify_url'] = $notify_url; //
                $jhpay = JhFacade::jhPay($params);
                if ($jhpay['status']){
                    return $this->succeed($jhpay['data'],'聚合支付链接返回成功!');
                }
                return $this->failure($jhpay,'第三方支付返回失败!');
            }
            if ($way == 9) {
                return $this->succeed( 'http://' . $_SERVER['HTTP_HOST'] . '/common/alipays?no=' . $orderNo, '支付宝发起支付成功');
            }
            if($way == 10 || $way == 11 || $way == 12){
                //恒云支付
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/typay/business';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                $planorder = MemberPlanOrder::where('order_no',$orderNo)->first();
                $planorder->pay_url = $jhpay;
                $planorder->save();
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 22){
                //恒云微信
                $params['out_trade_no'] = $orderNo; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 13){
                //铭科支付
                $params['orderNo'] = $orderNo; //订单号
                $params['money'] = $amount; //交易金额
                $params['way'] = $way; //铭科支付
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/mkpayback';
                $params['notifyUrl'] = $notify_url; //回调地址
                $params['productName'] = '商务订单支付';                       //商品描述
                $jhpay = MkFacade::mkPay($params);
                return $this->succeed($jhpay,'铭科支付链接返回成功!');
            }
            return $this->succeed($payParam, '邀约申请成功，跳转支付...');

        } catch (\Exception $ex) {
            $this->logs('操作异常', $ex);
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 商务邀约订单处理
    * */
    public function dealPlanOrder(Request $request, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            $id = $request['orderid']; //订单ID
            $status = $request['status']; //订单处理状态（1,2,3,4,5,6,7）
            $cancel_type = empty($request['cancel_type']) ? 0 : $request['cancel_type'];
            if (!isset($id) || !isset($status)) {
                return $this->validation('请输入所有必填参数！');
            }
            if (!in_array($status, [1, 2, 3, 4, 5, 6, 7])) {
                return $this->validation('处理状态参数错误！');
            }
            $data = $memberPlanOrderRepository->find($id);
            if (!isset($data)) {
                return $this->validation('未找到待处理订单！');
            }
            return MemberFacade::dealPlanOrder($data, $status, $cancel_type); //调用处理方法

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 商务订单评价
     * */
    public function evaluatePlanOrder(Request $request, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            $id = $request['orderid']; //订单ID
            $data = $memberPlanOrderRepository->find($id);
            if (!isset($data) || $data['status'] != 6) {
                return $this->validation('未找到可评价的商务订单！');
            }
            if ($data['member_id'] != $member['id']) {
                return $this->validation('只能评价自己发起的商务订单！');
            }
            if (isset($data['evaluation']) || $data['status'] == 8) {
                return $this->validation('该商务订单您已经评价过！');
            }
            $score = $request['score']; //请输入评分
            $evaluation = $request['evaluation']; //评价内容
            if (!isset($score)) {
                return $this->validation('必须输入评分和评价内容！');
            }
            $data['score'] = $score;
            $data['evaluation'] = $evaluation;
            $data['status'] = 8;
            if ($memberPlanOrderRepository->update($data)) {
                return $this->succeed();
            }
            return $this->failure(1, '评价操作失败！');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
    * 我的邀约订单列表
    * */
    public function myOrderList(Request $request, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $memberId = $member->id;
            $from = intval($request['from']);
            $addwhere = array();
            if ($from == 1) {
                //主播查询
                $addwhere['member_id'] = $memberId;
            } else {
                $addwhere['to_member_id'] = $memberId;
            }
            $list = $memberPlanOrderRepository->lists($addwhere, ['member', 'tomember']);
            $request['view_type'] = 'list';
            $request['search_from'] = 0;
            return $this->succeed(MemberPlanOrderResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 商务邀约订单详情
     * */
    public function planOrderInfo(Request $request, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $orderid = $request['orderid'];
            if (!isset($orderid)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }
            $order = $memberPlanOrderRepository->find($orderid);
            if (!isset($order)) {
                return $this->validation('未找到订单！');
            }
            return $this->succeed(new MemberPlanOrderResource($order));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     *  商务列表
     * */
    public function swList(Request $request, MemberInfoRepository $infoRepository)
    {
        try {
            $addWhere = array();
            //默认条件
            $addWhere['status'] = 1;
            $addWhere['sex'] = 1; //性别为女1
            $addWhere['business_check'] = 1; //通过商务认证的
            $addWhere['realname_check'] = 1; //实名认证(0否 1是)
            $request['open_business'] = 1; //是开启了商务的
            $request['view_place'] = 'swlist'; //显示
            $noun = $infoRepository->orderBy('sort', 'desc')->lists($addWhere);
            return $this->succeed(MemberInfoResource::collection($noun));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 商务主播详情接口
    * */
    public function swInfo(Request $request, MemberInfoRepository $infoRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $zbid = $request['zbid']; //主播Id
            if (!isset($zbid)) {
                return $this->validation('主播ID不能为空！');
            }
            $zbinfo = $infoRepository->find($zbid);
            if (!isset($zbinfo) && ($zbinfo['business_check'] == 0 || $zbinfo['open_business'] == 0)) {
                return $this->validation('未找到商务主播！');
            }
            $request['view_place'] = 'swinfo';
            //资源封面
            $covers = $zbinfo->covers;
            $coverArray = array();
            foreach ($covers as $key => $item) {
                $coverArray[] = $item['url'];
            }
            //项目列表
            $row = DB::table('member_plan')
                ->where('member_id', $zbid)
                ->where('deleted_at', null)
                ->where('status', 1)
                ->select('project', DB::raw('count(*) as counts'))
                ->groupBy('project')->get();

            $resultAray = [];
            foreach ($row as $item) {
                $projectList = [];
                $projectArray = MemberPlan::where(['member_id' => $zbid, 'project' => $item->project])
                    ->orderBy('project', 'asc')->orderBy('created_at', 'asc')->get();

                foreach ($projectArray as $newitem) {
                    //查询图片
                    $pics = MemberPlanPic::where('plan_id', $newitem['id'])->get(['pic'])->toArray();
                    $picList = [];
                    foreach ($pics as $pic) {
                        $picList[] = $pic['pic'];
                    }
                    $project = array(
                        'proid' => $newitem['id'],
                        'content' => $newitem['content'],
                        'pics' => $picList,
                    );
                    $projectList[] = $project;
                }
                $pro = array(
                    'key' => $item->project,
                    'items' => $item->counts,
                    'value' => $projectList,
                );
                $resultAray[] = $pro;

            }
            //输出
            $request['projectList'] = $resultAray;
            $request['cover_array'] = $coverArray;
            $config = Cache::get('SystemBasic'); //取平台配置
            $request['sin_fee'] = $config['business_sin_fee'];
            $request['mul_fee'] = $config['business_mul_fee'];

            return $this->succeed(new MemberInfoResource($zbinfo), '操作成功');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
    * 商务订单发起支付
    * */
    public function toPay(Request $request, MemberPlanOrderRepository $memberPlanOrderRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $orderno = $request['order_no']; //订单编号
            if (!isset($orderno)) {
                return $this->validation('请输入所有必填参数！');
            }
            $model = $memberPlanOrderRepository->findBy('order_no', $orderno);
            if (!isset($model)) {
                return $this->validation('订单不存在！');
            }
            if ($model->status != 0) {
                return $this->validation('订单不能再进行支付！');
            }
            $pay_url = $model->pay_url;//支付链接
            $way = $model->way; //支付方式
            $amount = $model->amount; //金额
            //调用微信或支付宝统一下单接口处理
            if ($way == 6) {
                //微信支付
                //1.统一下单方法
                $payAmount = $amount * 100;
                $appid = "wxf40312b43b92191c";
                $mch_id = "1558282021";
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/appwechat/business';
                $key = "zhaocheng11637501641762365756888";//
                $wechatAppPay = new WechatAppPay($appid, $mch_id, $notify_url, $key);
                $params['body'] = '会员金币充值';                       //商品描述
                $params['out_trade_no'] = $orderno;    //自定义的订单号
                $params['total_fee'] = $payAmount;                       //订单金额 只能为整数 单位为分
                $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
                $result = $wechatAppPay->unifiedOrder($params);

                //2.创建APP端预支付参数
                /** @var TYPE_NAME $result */
                $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
                //下边为了拼够参数，多拼了几个给安卓、ios前端
//                $data['body'] = '会员金币充值';
//                $data['notify_url'] = $notify_url;
//                $data['total_fee'] = $payAmount;
//                $data['success'] = 1;
//                $data['spbill_create_ip'] = '127.0.0.1';
//                $data['out_trade_no'] = $orderno;
//                $data['trade_type'] = 'APP';
//                $payParam = $data;
                $payParam = array(
                    'appid' => $data['appid'],
                    'partnerid' => $mch_id,
                    'out_trade_no' => $orderno,
                    'prepayId' => $data['prepayid'],
                    'package' => $data['package'],
                    'nonce_str' => $data['noncestr'],
                    'timestamp' => $data['timestamp'],
                    'sign' => $data['sign'],
                );
            }
            if ($way == 3) {
                //三方支付，拼接支付地址
                $payParam = PayFacade::typay($orderno, $amount, $member->code);
            }
            if ($way == 1) {
                //拼多支付(微信)
                $res = PayFacade::pddPay($orderno, $amount, 'wechat', 'business');

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 2) {
                //拼多支付(支付宝)
                $res = PayFacade::pddPay($orderno, $amount, 'alipay', 'business');

                if (!array_key_exists('code', $res) || $res['code'] != 200) {
                    return $this->validation($res['msg']);
                }
                $payParam = $res['data']['h5_url'];
            }
            if ($way == 8){
                //聚合支付(支付宝H5)
                $params['out_trade_no'] = $orderno; //订单号
                $params['amount'] = $amount; //交易金额
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/appwechat/business';
                $params['notify_url'] = $notify_url;
                $jhpay = JhFacade::jhPay($params);
                if ($jhpay['status']){
                    return $this->succeed($jhpay['data'],'聚合支付链接返回成功!');
                }
                return $this->failure($jhpay,'第三方支付返回失败!');
            }
            if ($way == 9) {
                return $this->succeed( 'http://' . $_SERVER['HTTP_HOST'] . '/common/alipays?no=' . $orderno, '支付宝发起支付成功');
            }
            if($way == 10 || $way == 11 || $way == 12){
                if (isset($pay_url)){
                    return $this->succeed($pay_url,'恒云支付链接返回成功');
                }
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/callback/pay/typay/business';
                //恒云支付
                $params['out_trade_no'] = $orderno; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                $model->pay_url = $jhpay;
                $model->save();
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 22){
                //恒云微信
                $params['out_trade_no'] = $orderno; //订单号
                $params['amount'] = $amount; //交易金额
                $params['way'] = $way; //恒云支付1
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/hypayback';
                $params['notify_url'] = $notify_url; //恒云支付1
                $jhpay = HyFacade::hyPay($params);
                return $this->succeed($jhpay,'恒云支付链接返回成功!');
            }
            if($way == 13){
                //铭科支付
                $params['orderNo'] = $orderno; //订单号
                $params['money'] = $amount; //交易金额
                $params['way'] = $way; //铭科支付
                $notify_url = 'http://' . $_SERVER['HTTP_HOST'] . '/common/mkpayback';
                $params['notifyUrl'] = $notify_url; //回调地址
                $params['productName'] = '商务订单支付';                       //商品描述
                $jhpay = MkFacade::mkPay($params);
                return $this->succeed($jhpay,'铭科支付链接返回成功!');
            }
            return $this->succeed($payParam, '跳转支付...');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

}
