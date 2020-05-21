<?php
/**
 *  会员充值管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\RechargeFacade;
use App\Http\Controllers\Controller;
use App\Repositories\MemberRechargeRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use App\Utils\WechatAppPay;
use Illuminate\Http\Request;

class MemberRechargeController extends Controller
{
    public function __construct(MemberRechargeRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        $recPayWay = SelectList::recPayWay();
        $payStatus = SelectList::payStatus();
        $rechargeType = SelectList::rechargeType();
        return view('member.recharge.index', compact('recPayWay', 'payStatus', 'rechargeType'));
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists(null, ['member']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }


    /**
     *  状态变更(支付成功)
     * @param Request $request
     * @return array|mixed
     */
    public function status(Request $request)
    {
        try {
            $list = $this->repository->find($request->id);
            if ($list['status'] != 0) {
                return $this->failure(1, '该订单已处理过!');
            }
            if ($list->way == 6) {
                //微信APP支付，查询订单
                $wechatAppPay = new WechatAppPay();
                $ret = $wechatAppPay->orderQuery($list->order_no);
                if (!$ret) {
                    $this->failure(1, '微信查单失败，请重试');
                }
                if ($ret['trade_state'] = 'SUCCESS' && $ret['trade_state_desc'] == '支付成功') {
                    //支付成功
                    $list['status'] = 1;
                    $list['pay_time'] = date('Y-m-d H:i:s', strtotime($ret['time_end']));
                } else {
                    $list['status'] = 2;
                    $list['remark'] = $ret['trade_state_desc'];
                }
            } else {
                $list['status'] = $request->status;
                $list['pay_time'] = Helper::getNowTime();
            }
            return RechargeFacade::rechargeDeal($list, 0);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * 删除
     * @param Request $request
     * @return array|mixed
     */
    public function destroy(Request $request)
    {
        try {
            $result = $this->repository->destroy($request->ids);
            return $result;
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

}
