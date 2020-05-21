<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\AliyunFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Member\MemberAccountResource;
use App\Http\Resources\Member\MemberRealNameResource;
use App\Models\MemberRealName;
use App\Repositories\MemberAccountRepository;
use App\Repositories\MemberRealNameRepository;
use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * 人脸实名
 * Class MemberDescribeController
 * @package App\Http\Controllers\Api\Member
 */
class MemberDescribeController extends ApiController
{

    public function __construct(MemberRealNameRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 请求认证
     * @param Request $request
     * @return array
     */
    public function DescribeVerifyToken(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
//            if (!isset($request->name)) {
//                return $this->validation('姓名不能为空！');
//            }
//            if (!isset($request->idcard)) {
//                return $this->validation('身份证号不能为空！');
//            }
//            if (!isset($member->realname)) {
//                $account = MemberRealName::firstOrCreate(['member_id' => $member->id, 'name' => $request->name, 'cert_no' => $request->idcard]);
//            }

            if (isset($member->realname)&&$member->realname->status == 1) {
                return $this->validation('已实名,不需再实名！');
            }
            /**
             * 用于实名认证
             */
            if (empty($member->realname_id)){
                $member->realname_id = Carbon::now()->timestamp;
                $member->save();
            }
            $resp = AliyunFacade::DescribeVerifyToken($member->realname_id);
            if ($resp['status']) {
                return $this->succeed($resp['data']);
            }
            return $this->failure($resp, '申请认证失败');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     *查询认证
     * @param Request $request
     * @return array
     */
    public function DescribeVerifyResult(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('会员api_token错误');
            }
            $realname = $member->realname;
            if ($realname !=null ){
                return $this->validation('已查询过会员实名认证');
            }
            if ($member->realname_id == 0){
                return $this->validation('该会员未提交实名认证');
            }
            $resp = AliyunFacade::DescribeVerifyResult($member->realname_id);
            if ($resp['status']) {
                return $this->succeed($resp['data']);
            }
            return $this->succeed($member, '操作成功');
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }


}
