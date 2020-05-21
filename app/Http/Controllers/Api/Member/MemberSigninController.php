<?php

namespace App\Http\Controllers\Api\Member;

use App\Facades\MemberFacade;
use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\MemberSignInRepository;
use App\Models\MemberSignIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//会员签到
class MemberSigninController extends ApiController
{

    public function __construct(MemberSignInRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 会员当天签到
     * @param Request $request
     * @return array
     */
    public function today(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $qd_date = date('y-m-d 00:00:00');
            $res = $this->repository->findWhere(['member_id' => $memberId, 'qd_date' => $qd_date])->first();
            if ($res != null) {
                return $this->failure(1, '今天已经签过到！');
            }
            $data['member_id'] = $memberId;
            $data['qd_date'] = $qd_date;
            $result = $this->repository->store($data);
            if (!$result['status']) {
                return $this->failure($result['msg']);
            }
            return $this->succeed();
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

    /**
     * 会员补签
     * @param Request $request
     * @return array
     */
    public function buqian(Request $request)
    {
        try {
            $member = $request->user('api');
            if (!$member) {
                return $this->validation('api_token错误');
            }
            //会员ID
            $memberId = $member->id;
            $qd_date = $request['date']; //补签日期
            if (!isset($qd_date)) {
                return $this->validation('请输入补签日期！');
            }
            if (strtotime($qd_date) >= strtotime(date('y-m-d'))) {
                return $this->validation('补签日期错误！');
            }
            $res = $this->repository->findWhere(['member_id' => $memberId, 'qd_date' => $qd_date])->first();
            if ($res != null) {
                return $this->failure(1, $qd_date . '已经签过到！');
            }
            $data = new MemberSignIn();
            $data['member_id'] = $memberId;
            $data['qd_date'] = $qd_date;
            $data['bq_date'] = $qd_date;
            return MemberFacade::buqian($data);
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }

}
