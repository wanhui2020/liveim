<?php
/**
 *  会员赠送礼物管理控制器
 */

namespace App\Http\Controllers\System\Member;

use App\Facades\MemberFacade;
use App\Facades\RecordFacade;
use App\Http\Controllers\Controller;
use App\Http\Repositories\SystemGiftRepository;
use App\Models\MemberAccount;
use App\Models\MemberExtend;
use App\Models\MemberGift;
use App\Models\MemberInfo;
use App\Models\MemberRecord;
use App\Models\SystemData;
use App\Models\SystemGift;
use App\Repositories\MemberGiftRepository;
use App\Repositories\MemberInfoRepository;
use App\Utils\Helper;
use App\Utils\SelectList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MemberGiftController extends Controller
{
    public function __construct(MemberGiftRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 显示列表
     * */
    public function index()
    {
        return view('member.gift.index');
    }

    /*
    *  显示列表(获取数据)
    * */
    public function lists()
    {
        try {
            $list = $this->repository->lists(null, ['member', 'tomember']);
            return $this->paginate($list);
        } catch (\Exception $ex) {
            return $this->exception($ex);
        }
    }

    /*
     * 添加视图
     * */
    public function create()
    {
        $member = MemberInfo::where(['status' => 1, 'sex' => 0])->get(['id', 'code', 'user_name', 'nick_name']); //赠送会员
        $tomember = MemberInfo::where(['status' => 1, 'sex' => 1])->get(['id', 'code', 'user_name', 'nick_name']); //接收会员（主播）
        $gift = SystemGift::where(['status' => 1])->orderBy('sort', 'desc')->get(['id', 'title', 'gold']); //礼物列表
        return view('member.gift.create', compact('member', 'tomember', 'gift'));
    }

    /*
    * 保存到数据库
    * */
    public function store(Request $request, MemberInfoRepository $memberInfoRepository, SystemGiftRepository $giftRepository)
    {
        try {
            $data = $request->all();
            $memberId = $data['member_id']; //赠送会员
            $toMemberId = $data['to_member_id']; //接收会员
            $zbinfo = $memberInfoRepository->findWhere(['id' => $toMemberId, 'sex' => 1])->first();
            if ($zbinfo == null) {
                return $this->failure(1, '主播不存在！');
            }
            $gift = $giftRepository->find($data['gift_id']);
            if (!isset($gift) || $gift->status == 0) {
                return $this->validation('礼物不存在！');
            }

//            $result = $this->repository->store($data);

            return MemberFacade::giveGift($data, $data['url']);

//            //2.判断查看会员余额是否足够
//            if ($result['status']) {
//                if ($gold > 0) {
//
//                    $memberAccount = MemberAccount::where('member_id', $memberId)->first(); //会员
//                    if ($memberAccount->balance_gold < $gold) {
//                        return $this->failure(1, '该会员可用金币不足，不能赠送该礼物！');
//                    }
//                    //1.会员扣除资费默认先扣可用余额
//                    $beforeAmount = $memberAccount->surplus_gold;
//                    $afterAmount = $beforeAmount - $gold;
//
//                    $record = new MemberRecord();
//                    $record->member_id = $memberId;
//                    $record->type = 2;//送礼物
//                    $record->account_type = 1; //账户类型
//                    $record->amount = -$gold; //发生金额
//                    $record->freeze_amount = 0;//冻结金额
//                    $record->before_amount = $beforeAmount;//变动前额
//                    $record->balance = $afterAmount;//实时余额
//                    $record->status = 1;//交易成功
//                    $record->remark = '送礼物';//交易备注
//
//                    //2.主播收益
//                    //获取平台基础数据设置，抽成比例
//                    $rate = $memberExtend->gift_rate; //默认取主播自身的，如果未单独设置，则取平台默认值
//                    if ($rate == 0) {
//                        $config = Cache::get('SystemBasic'); //取平台
//                        $rate = $config->rate;
//                    }
//                    $sxf = round($gold * ($rate / 100)); //手续费，四舍五入
//                    $syGold = $gold - $sxf; //主播收益
//
//                    $zhuboAccount = MemberAccount::where('member_id', $toMemberId)->first(); //主播账户
//                    $remark = '收益(' . $syGold . ')=' . $gold . ' - 手续费(' . $sxf . ')';
//                    $beforeAmount = $zhuboAccount->surplus_gold;
//
//                    $zbRecord = new MemberRecord();
//                    $zbRecord->member_id = $toMemberId; //主播ID
//                    $zbRecord->type = 9;//收到礼物
//                    $zbRecord->account_type = 1; //账户类型
//                    $zbRecord->amount = $syGold; //发生金额
//                    $zbRecord->freeze_amount = 0;//冻结金额
//                    $zbRecord->before_amount = $beforeAmount;//变动前额
//                    $zbRecord->balance = $beforeAmount + $syGold;//实时余额
//                    $zbRecord->status = 1;//交易成功
//                    $zbRecord->remark = $remark;//交易备注
//                    return RecordFacade::addRecord($record, $zbRecord); //调用新增资金流水
//                }
//                return $this->succeed($result);
//            }
//            return $this->failure(1, $result['msg']);

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