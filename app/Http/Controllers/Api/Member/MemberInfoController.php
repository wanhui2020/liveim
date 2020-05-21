<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\BaseFacade;
use App\Facades\CommonFacade;
use App\Facades\ImFacade;
use App\Facades\SmsFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\SystemMessageRepository;
use App\Http\Resources\Member\MemberExtendResource;
use App\Http\Resources\Member\MemberInfoResource;
use App\Http\Resources\Member\MemberRealNameResource;
use App\Http\Resources\Member\MemberSelfieResource;
use App\Http\Resources\SystemMessageResource;
use App\Models\MemberAccount;
use App\Models\MemberAttention;
use App\Models\MemberDayCount;
use App\Models\MemberExtend;
use App\Models\MemberFriends;
use App\Models\MemberInfo;
use App\Models\MemberInviteAward;
use App\Models\MemberLogin;
use App\Models\MemberRecord;
use App\Models\MemberTalk;
use App\Models\MemberVisit;
use App\Models\SystemBasic;
use App\Repositories\MemberBusinessRepository;
use App\Repositories\MemberCreditRepository;
use App\Repositories\MemberExtendRepository;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberRealNameRepository;
use App\Repositories\MemberSelfieRepository;
use App\Repositories\MemberTagsRepository;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberInfoController extends ApiController
{

    public function __construct(MemberInfoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 会员注册/登录接口
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {
        try {
            $retData = $request->all();
//            Log::info('调用登录接口:',[$retData]);
//            $this->logs('调用登录接口',$retData);
            $openid = $request['openid']; //微信OPENID
            $nick_name = $request['nick_name']; //会员昵称
            $sex = $request['sex']; //性别 0:男 1:女
            $pic = $request['pic']; //头像PIC
            $parent_code = $request['parent_code']; //推荐人编号
//            $parent_code = $request['userid']; //推荐人编号
            $ip = $request['ip']; //客户端IP
            $push_token = $request['push_token']; //推送token
            $platform = $request['platform']; //推送token
            if (empty($openid)) {
                return $this->validation('微信OPENID为空！');
            }
            $memberInfo = $this->repository->findBy('openid', $openid);
            if (!isset($memberInfo)) {
                $request['is_reg'] = 0;
                //注册，不填性别
                if ($request->filled($nick_name)) {
                    return $this->validation('注册失败，未获取到用户昵称!');
                }
                if ($request->filled($pic)) {
                    return $this->validation('注册失败，未获取到用户头像!');
                }
                $data['openid'] = $openid;
                $data['code'] = CommonFacade::randStr(6, 'NUMBER'); //会员编号
                $data['user_name'] = 'u' . $data['code']; //会员用户名
                $data['nick_name'] = $nick_name; //昵称
                $data['password'] = '000000'; //密码默认6个0
                $data['invitation_code'] = CommonFacade::randStr(7, 'NUMBER');//邀请码
                $data['reg_time'] = Helper::getNowTime(); //注册时间
                $data['reg_ip'] = $ip; //客户端IP地址
                $data['sex'] = -1; //性别
                $data['head_pic'] = $pic; //头像
                $data['push_token'] = $push_token; //推送token
                $data['platform'] = $platform; //
                //注册新用户时，查看是否有邀请人，有的话添加邀请注册奖励金币
                //增加邀请注册流水
                if (!empty($parent_code)) {
                    //推荐人编号不为空
                    $tjr = MemberInfo::where('code',$parent_code)->first(); //上级的用户

                    if (isset($tjr) && $tjr->status == 1) { //状态正确
                        $data['pid'] = $tjr->id;
                        //增加邀请人   增加经纪人
                        /**
                         * 两个权限都没有的时候直接继承上级用户的
                         * 其中一个有那么就继承其中一个另外一个写无
                         * 两个都有那么就两个继承上级
                         */
                        if ($tjr->is_inviter != 1 && $tjr->is_inviter_zb != 1){ //上级一个权限都没有 被邀请人直接继承上级的权限
                            $data['inviter_id'] = $tjr->inviter_id; //邀请人
                            $data['inviter_zbid'] = $tjr->inviter_zbid; //经纪人
                        }elseif ($tjr->is_inviter != 1 && $tjr->is_inviter_zb == 1){//上级有经纪人邀请权限，只给被邀请人分配经纪人，邀请人为空
                            $data['inviter_id'] = 0;//邀请人
                            $data['inviter_zbid'] = $tjr->id;//经纪人
                        }elseif ($tjr->is_inviter == 1 && $tjr->is_inviter_zb != 1){//上级有邀请人邀请权限，只给被邀请人分配邀请人，经纪人为空
                            $data['inviter_id'] = $tjr->id;//邀请人
                            $data['inviter_zbid'] = 0;//经纪人
                        }elseif ($tjr->is_inviter == 1 && $tjr->is_inviter_zb == 1){//上级拥有两个权限，下级直接继承该用户
                            $data['inviter_id'] = $tjr->id;//邀请人
                            $data['inviter_zbid'] = $tjr->id;//经纪人
                        }
                        $config = Cache::get('SystemBasic'); //取平台配置
                        if ($config) {
                            $yqgold = $config->yqzc_give_gold;
                        } else {
                            $yqgold = SystemBasic::first()->yqzc_give_gold;
                        }
                        if ($yqgold > 0) {  //注册赠送上级可提现金币
                            $beforeAmount = $tjr->account->surplus_gold;//变动前额
                            $tjr->account->surplus_gold +=  $yqgold;
                            //添加一条资金流水
                            $record = new MemberRecord();
                            $record->member_id = $tjr->id;
                            $record->type = 24;//注册赠送邀请人
                            $record->account_type = 1; //账户类型 金币
                            $record->amount = $yqgold; //发生金额
                            $record->freeze_amount = 0;//冻结金额
                            $record->before_amount = $beforeAmount;//变动前额
                            $record->balance = $beforeAmount + $yqgold;//实时余额
                            $record->status = 1;//交易成功
                            $record->remark = '注册赠送邀请人';//交易备注
                            $record->save();
                        }
                        $tjr->account->save();
                        if ($tjr->is_agent == 1) {
                            //推荐人是代理商
                            $data['agent_id'] = $tjr->id;
                        } else {
                            //不是代理商,取上级人的代理商
                            $data['agent_id'] = $tjr->agent_id;
                        }
                    }
                }
                //执行注册
                $result = $this->repository->store($data);
                if (!$result['status']) {
                    return $this->failure(1, '操作失败,请稍后重试！', $result);
                }
                $memberInfo = $result['data']; //会员信息
            } else {
                if ($memberInfo->status == 0){
                    return $this->validation('已被禁用，请联系管理员!');
                }
                $memberInfo['online_status'] = 1;
                $memberInfo['push_token'] = $push_token; //推送token
                $memberInfo['platform'] = $platform; //所属平台
                $memberInfo->save();
            }
            //添加登录日志
            $loginInfo = new MemberLogin();
            $loginInfo->member_id = $memberInfo['id'];
            $loginInfo->login_time = Helper::getNowTime();
            $loginInfo->login_ip = $ip;
            $loginInfo->save();
            ImFacade::userSetInfo($memberInfo->id, $memberInfo->nick_name, $memberInfo->head_pic);
            return $this->succeed(new MemberInfoResource($memberInfo), '操作成功');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
     * 会员个人详细资料接口
     * */
    public function info(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $memberInfo = $this->repository->with(['account'])->find($member->id);
            if ($member->extend == null) {
                $extend = new MemberExtend();
                $extend->member_id = $memberInfo->id;
                $extend->save();
            }
            return $this->succeed(new MemberExtendResource($memberInfo), '操作成功');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 通过code查询会员信息
     * */
    public function getInfoById(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $id = $request['userid'];
            if (!isset($id)) {
                return $this->validation('请输入必填参数');
            }
            $memberInfo = $this->repository->find($id);
            $request['view_place'] = "userinfo";
            return $this->succeed(new MemberInfoResource($memberInfo), '操作成功');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    //编辑资料
    public function editInfo(Request $request, MemberExtendRepository $extendRepository, MemberInfoRepository $infoRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            //可修改字段
            $upkey = $request['upkey']; //修改字段
            $upvalue = $request['upvalue']; //修改值
            if (!isset($upkey) || !isset($upvalue)) {
                return $this->validation('参数错误，请传入必填参数值！');
            }
            $keyList = ['head_pic', 'nick_name', 'sex', 'signature', 'hobbies', 'city', 'address', 'stature', 'weight', 'height', 'constellation', 'text_fee', 'voice_fee', 'video_fee', 'picture_view_fee', 'video_view_fee', 'coat_fee', 'is_business'];
            if (!in_array($upkey, $keyList, false)) {
                return $this->validation('修改字段参数错误！');
            }

            $data[$upkey] = $upvalue;
            if ($upkey == 'sex'){
                if ($member->sex == -1 ){
                    $data['id'] = $member->id;
                    return $infoRepository->update($data);
                }else{
                    return $this->succeed();
                }
            }else{
                if ($upkey == 'head_pic' || $upkey == 'nick_name') {
                    //修改用户头像或昵称
                    $data['id'] = $member->id;
                    if ($upkey == 'head_pic') {
                        $data['new_head_pic'] = $upvalue;
                        unset($data['head_pic']);
                    }
                    return $infoRepository->update($data);
                }
                //修改扩展资料
                $data['member_id'] = $member->id;
                return $extendRepository->update($data, 'member_id');
            }
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }

    /*
     * 主播列表
     * */
    public function zblist(Request $request, MemberTagsRepository $memberTagsRepository)
    {
        try {
            $type = $request['type']; //主播类型
            $addWhere = array();
            //默认条件
            $addWhere['status'] = 1;
            $addWhere['sex'] = 1;  //性别1为女
            $addWhere['selfie_check'] = 1; //自拍认证 1已经认证
            if (isset($type)) {
                if ($type == 'recommend') {
                    //查询推荐的主播
//                    $addWhere['is_recommend'] = 1;

                } else {
                    //标签ID
                    $request['tag_id'] = $type;
                }
            }
            if (isset($request['tag_id'])) {
                //通过标签分类搜索主播
//                DB::connection()->enableQueryLog();
                $list = $this->repository
                    ->with(['extend:id,video_fee,member_id'])
                    ->orderBy('online_status', 'desc') //降序
//                    ->orderBy('vv_busy', 'asc') //升序
                    ->orderBy('sort', 'desc')
                    ->lists($addWhere);
                $request['view_place'] = 'zblist_tag'; //显示
//                dd(DB::getQueryLog());
                return $this->succeed(MemberInfoResource::collection($list));
            } else {
//                DB::connection()->enableQueryLog();
                $noun = $this->repository
                    ->orderBy('online_status', 'desc') //降序
//                    ->orderBy('vv_busy', 'asc') //升序
                    ->orderBy('sort', 'desc')
                    ->lists($addWhere);
//                dd(DB::getQueryLog());
                $request['view_place'] = 'zblist'; //显示
//                foreach ($noun as $k=>$v){
//                    $count = MemberTalk::where('member_id', $v->id)->where('status', '<', 2)->count();
//                    if ($count > 0){
//                        $v->vv_busy = 1;
//                        $v->save();
//                    }
//                }
                return $this->succeed(MemberInfoResource::collection($noun));
            }
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 主播详情
     * */
    public function zbinfo(Request $request, MemberCreditRepository $memberCreditRepository)
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
            $zbinfo = $this->repository->findWhere(['id' => $zbid,'sex'=>1])->first();
            if ($zbinfo == null) {
                $zbinfo = $this->repository->findWhere(['id' => $zbid])->first();
                $coverArray = array();
                $coverArray['url'] = url('/images/backpic.png');
                $coverArray['type'] = 1;
            }else{
                //资源封面
                $covers = $zbinfo->covers()->get();
                if ($covers->isEmpty()){
                    $coverArray = array();
                    $coverArray['url'] = url('/images/backpic.png');
                    $coverArray['type'] = 1;
                }else{
                    $coverArray = array();
                    foreach ($covers as $key => $item) {
                        $coverArray[$key]['url'] = $item['url'];
                        $coverArray[$key]['type'] = $item['type'];
                    }
                }
            }
            $request['view_place'] = 'zbinfo';
            //是否关注,大于0就是已关注了
            $is_attention = MemberAttention::where(['member_id' => $member->id, 'to_member_id' => $zbid])->count();
            //是否好友
            $is_friend = MemberFriends::where(['member_id' => $member->id, 'to_member_id' => $zbid, 'status' => 1])->count();
            //访问次数
            $visit_count = MemberVisit::where(['member_id' => $member->id, 'to_member_id' => $zbid])->count();
            //信誉评价
            $credit = array();
            if (!$member->is_vip) {
                //是vip，才能看主播信誉评价
                $creditList = $memberCreditRepository->findWhere(['to_member_id' => $zbid]);
                foreach ($creditList as $key => $item) {
                    $credit[$key]['member'] = $item->member->nick_name;
                    $credit[$key]['xy_score'] = $item['xy_score']; //信誉评分
                    $credit[$key]['yz_score'] = $item['yz_score']; //颜值评分
                    $credit[$key]['myd_score'] = $item['myd_score']; //满意度评分
                }
            }
            //输出
            $request['cover_array'] = $coverArray;
            $request['is_attention'] = $is_attention;
            $request['is_friend'] = $is_friend;
            $request['visit_count'] = $visit_count;
            $request['credit'] = $credit;
            //添加访问记录
            $visit = new MemberVisit();
            $visit->member_id = $member->id; //访问人
            $visit->to_member_id = $zbid;
            $visit->save();
            return $this->succeed(new MemberInfoResource($zbinfo), '操作成功');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
     * 男神列表
     * */
    public function manList(Request $request)
    {
        try {
            $addWhere = array();
            //默认条件
            $addWhere['status'] = 1;
            $addWhere['sex'] = 0;
            $noun = $this->repository->orderBy('updated_at', 'desc')->lists($addWhere,null,1);
            $request['view_place'] = 'manlist'; //显示
            return $this->succeed(MemberInfoResource::collection($noun));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     * 会员自拍认证接口
     * @param Request $request
     * @return array
     */
    public function selfieCheck(Request $request, MemberSelfieRepository $selfieRepository, MemberBusinessRepository $businessRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            if ($member['selfie_check'] == 1) {
                return $this->validation('已通过自拍认证！');
            }
            //会员ID
            $memberId = $member->id;
            $is_business = $request['tbsw']; //是否同步到商务认证,1是
            $pic = $request['pic']; //自拍照片
            if (!isset($pic)) {
                return $this->validation('请输入所有必填参数！');
            }
            //先查询是否有未审核的认证
            $count = $selfieRepository->findWhere(['status' => 0, 'member_id' => $memberId])->count();
            if ($count > 0) {
                return $this->validation('已提交自拍认证，请等待审核！');
            }
            //提交
            $selfieModel['member_id'] = $memberId;
            $selfieModel['pic'] = $pic;
            //保存自拍认证
            $result = $selfieRepository->store($selfieModel);
            if (!$result['status']) {
                return $this->failure(1, '操作失败,请稍后重试！');
            }
            //同步到商务认证
            if ($is_business == 1 && $member['business_check'] != 1) {
                $count = $businessRepository->findWhere(['status' => 0, 'member_id' => $memberId])->count();
                if ($count == 0) {
                    $businessModel['member_id'] = $memberId;
                    $businessModel['pic'] = $pic;
                    $businessRepository->store($businessModel);
                }
            }
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 自拍认证详情
     * */
    public function selfieInfo(Request $request, MemberSelfieRepository $selfieRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $selfieModel = $selfieRepository->orderBy('updated_at', 'desc')->findBy('member_id', $memberId);
            if (!isset($selfieModel)) {
                return $this->succeed(null, '未找到自拍认证记录');
            }
            return $this->succeed(new MemberSelfieResource($selfieModel));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     * 会员实名认证接口
     * @param Request $request
     * @return array
     */
    public function realNameCheck(Request $request, MemberRealNameRepository $realNameRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            if ($member['realname_check'] == 1) {
                return $this->validation('已通过实名认证！');
            }
            //会员ID
            $memberId = $member->id;
            $certno = $request['certno']; //身份证号
            $name = $request['name']; //真实姓名
            $zm = $request['zm']; //身份证正面
            $fm = $request['fm']; //身份证反面
            $sc = $request['sc']; //手持身份证
            $selfie = $request['selfie']; //自拍认证
            if (!isset($certno) || !isset($name) || !isset($zm) || !isset($fm) || !isset($sc)) {
                return $this->validation('请输入所有必填参数！');
            }
            if (!Helper::is_idcard($certno)) {
                return $this->validation('身份证号码错误！');
            }
            //先查询是否有未审核的认证
            $count = $realNameRepository->findWhere(['status' => 0, 'member_id' => $memberId])->count();
            if ($count > 0) {
                return $this->validation('已提交实名认证，请等待审核！');
            }
            //提交
            $model['member_id'] = $memberId;
            $model['cert_no'] = $certno;
            $model['name'] = $name;
            $model['cert_zm'] = $zm;
            $model['cert_fm'] = $fm;
            $model['cert_sc'] = $sc;
            $model['selfie_pic'] = $selfie;
            $model['status'] = 0;
            //保存自拍认证
            $result = $realNameRepository->store($model);
            if (!$result['status']) {
                return $this->failure(1, '操作失败,请稍后重试！');
            }
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 实名认证详情
     * */
    public function realNameInfo(Request $request, MemberRealNameRepository $realNameRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $realNameModel = $realNameRepository->orderBy('updated_at', 'desc')->findBy('member_id', $memberId);
            if (!isset($realNameModel)) {
                return $this->succeed(null, '未找到实名认证记录');
            }
            return $this->succeed(new MemberRealNameResource($realNameModel));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     * 主播商务认证接口
     * @param Request $request
     * @return array
     */
    public function businessCheck(Request $request, MemberBusinessRepository $businessRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            if ($member['business_check'] == 1) {
                return $this->validation('已通过商务认证！');
            }
            /**
             * 商务认证  判断实名认证  是否绑定手机号
             */
            if ($member['mobile'] == null) {
                return $this->validation('请先绑定手机号！');
            }
            if ($member['realname_check'] == 0) {
                return $this->validation('请先进行实名认证！');
            }
            //会员ID
            $memberId = $member->id;
            $pic = $request['pic']; //自拍照片
            if (!isset($pic)) {
                return $this->validation('请输入所有必填参数！');
            }
            //先查询是否有未审核的认证
            $count = $businessRepository->findWhere(['status' => 0, 'member_id' => $memberId])->count();
            if ($count > 0) {
                return $this->validation('已提交商务认证，请等待审核！');
            }
            //提交
            $model['member_id'] = $memberId;
            $model['pic'] = $pic;
            //保存自拍认证
            $result = $businessRepository->store($model);
            if (!$result['status']) {
                return $this->failure(1, '操作失败,请稍后重试！');
            }
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
         * 商务认证详情
         * */
    public function businessInfo(Request $request, MemberBusinessRepository $businessRepository)
    {
        try {
            //必须登录会员才能查看
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $checkModel = $businessRepository->orderBy('updated_at', 'desc')->findBy('member_id', $memberId);
            if (!isset($checkModel)) {
                return $this->succeed(null, '未找到商务认证记录');
            }
            return $this->succeed(new MemberSelfieResource($checkModel));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
     * 会员分享页面
     * */
    public function shareInfo(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            $memberId = $member->id;
            $inviteAwardList = MemberInviteAward::where('member_id', $memberId)->get();
            $totalGold = round($inviteAwardList->sum('gold'),3); //累计奖励金币
            $totalMoney = round($inviteAwardList->sum('money'),3); //累计奖励金额
            $inviteCount = MemberInfo::where('pid', $memberId)->count(); //累计邀请人
            $canTx = round(MemberAccount::where('member_id', $memberId)->first()->cantx_rmb,3); //可提现金额

            //奖励规则
            $config = Cache::get('SystemBasic'); //取平台配置
            $invite_rate = $config->invite_rate; //邀请人充值奖励比例
            $consume_rate = $config->consume_rate; //邀请人下级消费奖励比例
            $yield_rate = $config->yield_rate; //下级商务收益比例

            $awardRules = [];
            if ($invite_rate > 0) {
                $recRule = array(
//                    'title' => $invite_rate . '%直接下级充值提成金币奖励',
                    'title' => '邀请5个用户',
                    'remark' => '分享邀请5个有效用户可免费领取价值188会员大礼包',
//                    'remark' => '直接邀请下级会员每笔充值即可获得充值金币' . $invite_rate . '%的奖励',
                );
                $awardRules[] = $recRule;
            }
            if ($consume_rate > 0) {
                $consumeRule = array(
//                    'title' => $consume_rate . '%间接下级充值提成金币奖励',
                    'title' => '邀请10个用户',
//                    'remark' => '间接邀请下级会员每笔充值即可获得充值金币' . $consume_rate . '%的奖励',
                    'remark' => '分享邀请10个有效用户可申请开通邀请奖励权限',
                );
                $awardRules[] = $consumeRule;
            }
            if ($yield_rate > 0) {
                $yieldRule = array(
//                    'title' => $yield_rate . '%下级主播旅游服务收益奖励',
                    'title' => '更多奖励',
//                    'remark' => '邀请下级主播会员产生每笔旅游服务收益即可获得收益金额' . $yield_rate . '%的奖励',
                    'remark' => '更多奖励请咨询平台客服',
                );
                $awardRules[] = $yieldRule;
            }

            //奖励排行排行榜
            $awardFromRow = DB::table('member_invite_award')
                ->where('member_id', $memberId)//
                ->leftJoin('member_info', function ($join) {
                    $join->on('member_invite_award.from_member_id', '=', 'member_info.id');
                })
                ->select('from_member_id'
                    , 'member_info.code'
                    , 'member_info.nick_name'
                    , 'member_info.head_pic'
                    , DB::raw('ifnull(sum(member_invite_award.gold),0) as total_gold,ifnull(sum(member_invite_award.money),0) as total_money'))
                ->orderBy('total_gold', 'desc')
                ->orderBy('total_money', 'desc')
                ->groupBy('from_member_id')
                ->take(10)
                ->get();

            $awardArray = [];
            foreach ($awardFromRow as $key => $item) {
                $awardArray[$key]['top'] = $key + 1;
                $awardArray[$key]['from_id'] = $item->from_member_id;
                $awardArray[$key]['code'] = $item->code;
                $awardArray[$key]['nick_name'] = $item->nick_name;
                $awardArray[$key]['head_pic'] = $item->head_pic;
                $awardArray[$key]['gold'] = $item->total_gold;
                $awardArray[$key]['money'] = floatval($item->total_money);
            }

            $result = array(
                'total_gold' => $totalGold,
                'total_money' => floatval($totalMoney),
                'total_count' => $inviteCount,
                'can_takenow' => floatval($canTx),
                'rules' => $awardRules,
                'tops' => $awardArray
            );

            return $this->succeed($result);

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
     * 会员所有邀请的用户列表
     * */
    public function inviteLists(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            $memberId = $member->id;
            $addWhere = array();
            //默认条件
            $addWhere['pid'] = $memberId;
            //查询列表
            $noun = $this->repository->lists($addWhere, ['account']);
            return $this->succeed(MemberInfoResource::collection($noun));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /*
     * 会员排行榜列表
     * */
    public function topLists(Request $request)
    {
        try {

            $type = $request['type']; //排行榜类型 1:魅力 0:富豪
            $dateType = $request['date']; //统计纬度 1:日榜 2:周榜 3:月榜 4:年榜

            if (!isset($type) || !isset($dateType)) {
                return $this->validation('请传入必填参数');
            }
            if (!in_array($type, [0, 1])) {
                return $this->validation('排行榜类型参数错误');
            }
            if (!in_array($dateType, [1, 2, 3, 4])) {
                return $this->validation('统计纬度参数错误');
            }
            $orderBy = $type == 1 ? 'profit_gold' : 'consume_gold'; //
            if ($dateType == 1) {
                //日榜
                $today = date('Ymd');//今日
                $todayRow = DB::table('member_day_count')
                    ->where('dayint', $today)
                    ->where('member_info.sex', $type)//
                    ->leftJoin('member_info', function ($join) {
                        $join->on('member_day_count.member_id', '=', 'member_info.id');
                    })
                    ->select('member_id', 'member_info.nick_name', 'member_info.head_pic', 'member_info.level_id', 'member_info.meili', 'member_info.haoqi', 'profit_gold', 'consume_gold')->orderBy($orderBy, 'desc')
                    ->take(10)
                    ->get();
                $yesToday = date("Ymd", strtotime("-1 day"));
                $resultArray = [];
                foreach ($todayRow as $key => $item) {
                    $tb = 'none';
                    $toji = MemberDayCount::where(['member_id' => $item->member_id, 'dayint' => $yesToday])->get()->sum($orderBy);
                    if ($toji > $item->$orderBy) {
                        $tb = 'down';
                    }
                    if ($toji < $item->$orderBy) {
                        $tb = 'up';
                    }
                    $resultArray[$key]['top'] = $key + 1;
                    $resultArray[$key]['id'] = $item->member_id;
                    $resultArray[$key]['nick_name'] = $item->nick_name;
                    $resultArray[$key]['head_pic'] = $item->head_pic;
                    $resultArray[$key]['lvl'] = $item->level_id == null ? '' : $item->level_id;
//                    $resultArray[$key]['meili'] = $item->meili == 0 ? '' : '魅' . $item->meili;
                    $resultArray[$key]['meili'] = $item->profit_gold == 0 ? '' : '魅' . round($item->profit_gold/1800,0);
                    $resultArray[$key]['haoqi'] = $item->haoqi == 0 ? '' : '壕' . $item->haoqi;
                    $resultArray[$key]['gold'] = $item->$orderBy;
                    $resultArray[$key]['tb'] = $tb;
                }
            } else {
                if ($dateType == 2) {
                    //周榜
                    //本周
                    $bdate = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));
                    $edate = date("Ymd", mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y")));
                    //上周
                    $last_bdate = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1 - 7, date("Y")));
                    $last_edate = date("Ymd", mktime(23, 59, 59, date("m"), date("d") - date("w") + 7 - 7, date("Y")));
                }
                if ($dateType == 3) {
                    //月榜
                    //本月
                    $bdate = date("Ymd", mktime(0, 0, 0, date("m"), 1, date("Y")));
                    $edate = date("Ymd", mktime(23, 59, 59, date("m"), date("t"), date("Y")));
                    //上月
                    $last_bdate = date("Ymd", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
                    $last_edate = date("Ymd", mktime(23, 59, 59, date("m"), 0, date("Y")));
                }
                if ($dateType == 4) {
                    //年榜
                    //本年
                    $bdate = date('Y0101');
                    $edate = date('Y1231');
                    //上年
                    $last_bdate = date('Y0101', strtotime('-1 year'));
                    $last_edate = date('Y1231', strtotime('-1 year'));
                }
                $thisweekRow = DB::table('member_day_count')
                    ->whereBetween('dayint', [$bdate, $edate])
                    ->where('member_info.sex', $type)//
                    ->leftJoin('member_info', function ($join) {
                        $join->on('member_day_count.member_id', '=', 'member_info.id');
                    })
                    ->select('member_id', 'member_info.nick_name', 'member_info.head_pic', 'member_info.level_id', 'member_info.meili', 'member_info.haoqi', DB::raw('ifnull(sum(member_day_count.profit_gold),0) as profit_gold,ifnull(sum(member_day_count.consume_gold),0) as consume_gold'))
                    ->orderBy($orderBy, 'desc')
                    ->groupBy('member_id')
                    ->take(10)
                    ->get();
                //同比上周
                $resultArray = [];
                foreach ($thisweekRow as $key => $item) {
                    $toji = MemberDayCount::where('member_id', $item->member_id)->whereBetween('dayint', [$last_bdate, $last_edate])->get()->sum($orderBy);
                    $tb = 'none';
                    if ($toji > $item->$orderBy) {
                        $tb = 'down';
                    }
                    if ($toji < $item->$orderBy) {
                        $tb = 'up';
                    }
                    $resultArray[$key]['top'] = $key + 1;
                    $resultArray[$key]['id'] = $item->member_id;
                    $resultArray[$key]['nick_name'] = $item->nick_name;
                    $resultArray[$key]['head_pic'] = $item->head_pic;
                    $resultArray[$key]['lvl'] = $item->level_id == null ? '' : $item->level_id;
//                    $resultArray[$key]['meili'] = $item->meili == 0 ? '' : '魅' . $item->meili;
                    $resultArray[$key]['meili'] = $item->profit_gold == 0 ? '' : '魅' . round($item->profit_gold/1800,0);
                    $resultArray[$key]['haoqi'] = $item->haoqi == 0 ? '' : '壕' . $item->haoqi;
                    $resultArray[$key]['gold'] = $item->$orderBy;
                    $resultArray[$key]['tb'] = $tb;
                }
            }
            return $this->succeed($resultArray);

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /*
    * 会员的系统消息
    * */
    public function myMessageList(Request $request, SystemMessageRepository $messageRepository)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            //默认条件
            $request['toid'] = $memberId;
            //查询列表
            $noun = $messageRepository->lists(null, ['member']);
            return $this->succeed(SystemMessageResource::collection($noun));

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


    /**
     *  会员发送手机验证码
     * */
    public function sendSmsByPhone(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $phone = $request['phone']; //手机号码
            if (!isset($phone)) {
                return $this->validation('请输入正确手机号码！');
            }
            return SmsFacade::sendCode($phone);
            //验证码短信，先查询1分钟内有没有验证码
//            $dateTime = date("Y-m-d H:i:s", strtotime("-1 minute"));
//            $smsModel = Sms::where(['phone' => $phone, 'type' => 0])->where('created_at', '<', $dateTime)->orderBy('created_at', 'desc')->first();
//            if (isset($smsModel)) {
//                return $this->validation('请1分钟后再重新发送');
//            }
//            $verfiyCode = CommonFacade::randStr(6, 'NUMBER'); //6位随机数
//            $data['verify_code'] = $verfiyCode;
//            $data['content'] = '验证码:' . $verfiyCode . '。请勿泄露给他人。';
//            $result = $smsRepository->store($data);
//            if ($result['status']) {
//
//                //调用发送短信
//                // ....
//
//
//
//                return $this->succeed();
//            }
//            return $this->failure(1, '发送手机验证码错误，请稍后重试！');

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }


    /**
     *  会员绑定手机号(修改)
     * */
    public function bindMobile(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }

            $phone = $request['phone']; //手机号码
            $verifyCode = $request['verify_code']; //验证码
            if (!isset($phone)) {
                return $this->validation('请输入正确手机号码！');
            }
            if (!isset($verifyCode)) {
                return $this->validation('请输入验证码！');
            }
            //匹配
            if (SmsFacade::verifyCode($phone, $verifyCode)) {
                //修改手机号码
                $data['id'] = $member->id;
                $data['mobile'] = $phone;
                if ($this->repository->update($data)) {
                    return $this->succeed();
                }

                return $this->failure(1, '绑定手机失败，请稍后重试！');
            }
            return $this->failure(1, '验证码错误');


        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }

    /**
     *  会员手机验证码，已绑定了手机号的才发送
     * */
    public function sendSms(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            if (!isset($member['mobile'])) {
                return $this->validation('会员未绑定手机号！');
            }
            return SmsFacade::sendCode($member['mobile']);

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     *  会员修改提现密码
     * */
    public function updateTakeNowPwd(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $phone = $member['mobile']; //手机号码
            $verifyCode = $request['verify_code']; //验证码
            if (!isset($verifyCode)) {
                return $this->validation('请输入验证码！');
            }
            if (empty($request['pwd'])) {
                return $this->validation('请输入提现密码！');
            }
            if (strlen($request['pwd']) < 6) {
                return $this->validation('提现密码不能少于6位！');
            }
            //匹配
            if (!SmsFacade::verifyCode($phone, $verifyCode)) {
                //修改手提现密码
                $data['id'] = $member->id;
                $data['take_pwd'] = bcrypt($request['pwd']);
                if ($this->repository->update($data)) {
                    return $this->succeed();
                }
                return $this->failure(1, '修改提现密码失败，请稍后重试！');
            }
            return $this->failure(1, '验证码错误');

        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }

    }


}
