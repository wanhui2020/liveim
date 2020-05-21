<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\MemberFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\SystemGiftRepository;
use App\Http\Resources\Member\MemberFileResource;
use App\Http\Resources\Member\MemberGiftResource;
use App\Models\MemberFileView;
use App\Models\MemberGift;
use App\Models\MemberReward;
use App\Repositories\MemberGiftRepository;
use App\Repositories\MemberInfoRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//主播会员礼物管理
class MemberGiftController extends ApiController
{

    public function __construct(MemberGiftRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 会员查看主播收到的礼物
     * */
    public function lists(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $zbid = $request['zbid']; //主播Id
            if (empty($zbid)) {
                return $this->validation('主播ID不能为空！');
            }
            $zbinfo = $memberInfoRepository->findWhere(['id' => $zbid, 'sex' => 1])->first();
            if ($zbinfo == null) {
                return $this->validation('未找到主播！');
            }
            $row = DB::table('member_gift')
                ->where('to_member_id', $zbid)
                ->leftJoin('system_gift', function ($join) {
                    $join->on('member_gift.gift_id', '=', 'system_gift.id');
                })
                ->select(DB::raw('system_gift.id as gift_id,system_gift.title as gift_name,system_gift.url as url,
            ifnull(sum(quantity),0) as quantity'))
                ->groupBy('gift_id')
                ->get();
            return $this->succeed(MemberGiftResource::collection($row));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
     * 我收到的礼物
     * */
    public function myLists(Request $request)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            $row = DB::table('member_gift')
                ->where('to_member_id', $member->id)
                ->leftJoin('system_gift', function ($join) {
                    $join->on('member_gift.gift_id', '=', 'system_gift.id');
                })
                ->select(DB::raw('system_gift.id as gift_id,system_gift.title as gift_name,system_gift.url as url,
            ifnull(sum(quantity),0) as quantity'))
                ->groupBy('gift_id')
                ->get();
            $request['view_place'] = 'mylists';
            return $this->succeed(MemberGiftResource::collection($row));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     * 会员赠送礼物
     * @param Request $request
     * @return array
     */
    public function giveGift(Request $request, MemberInfoRepository $memberInfoRepository, SystemGiftRepository $giftRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $zbid = $request['zbid']; //主播Id
            $giftid = $request['giftid']; //礼物ID
            $num = $request['num']; //数量
            if (!isset($zbid) || !isset($giftid) || !isset($num)) {
                return $this->validation('请输入所有必填参数！');
            }
            if ($num <= 0) {
                return $this->validation('赠送数量错误！');
            }
            $zbinfo = $memberInfoRepository->findWhere(['id' => $zbid, 'sex' => 1])->first();
            if ($zbinfo == null) {
                return $this->validation('主播不存在！');
            }
            $gift = $giftRepository->find($giftid);
            if (!isset($gift) || $gift->status == 0) {
                return $this->validation('礼物不存在！');
            }
            //计算价值
            $price = $gift['gold'];//单价
            $gold = $price * $num;// 总价

            $data = new MemberGift();
            $data['member_id'] = $memberId;
            $data['to_member_id'] = $zbid;
            $data['gift_id'] = $giftid;
            $data['gift_name'] = $gift['title'];
            $data['quantity'] = $num; //数量
            $data['gold'] = $gold; //金币
            $gift_url = $gift->url;
            return MemberFacade::giveGift($data, $gift_url);
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 会员打赏主播
     * */
    public function reward(Request $request, MemberInfoRepository $memberInfoRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $zbid = $request['zbid']; //主播Id
            $gold = $request['gold']; //打赏金币
            $remark = $request['reason']; //打赏原因
            if (!isset($zbid) || !isset($gold)) {
                return $this->validation('请输入所有必填参数！');
            }
            if ($gold <= 0) {
                return $this->validation('打赏金币数量错误！');
            }
            $zbinfo = $memberInfoRepository->findWhere(['id' => $zbid, 'sex' => 1])->first();
            if ($zbinfo == null) {
                return $this->validation('主播不存在！');
            }
            //计算价值
            $data = new MemberReward();
            $data['member_id'] = $memberId;
            $data['to_member_id'] = $zbid;
            $data['gold'] = $gold; //金币
            $data['remark'] = $remark; //打赏原因
            return MemberFacade::reward($data);
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    public function test(Request $request)
    {
        $ret = DB::select('SELECT f_continuty_days(1,DATE(\'2019-01-01\'),DATE(\'2019-06-11\'),\'signin\') as days;');
        return $this->succeed($ret[0]->days);
    }

}
