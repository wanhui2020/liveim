<?php


namespace App\Http\Controllers\Api\Common\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\MemberScoreRuleRepository;
use App\Http\Resources\ScoreRuleResource;
use App\Http\Resources\SystemRecResource;
use Illuminate\Http\Request;

/*
 * 积分管理接口
 * */

class ScoreController extends ApiController
{
    public function __construct(MemberScoreRuleRepository $repository)
    {
        $this->repository = $repository;
    }

    /*
     * 通过类型获取类型项
     * */
    public function getListByType(Request $request)
    {
        try {
            $type = $request['type'];
            if (!in_array($type, [0, 1, 2], false)) {
                return $this->validation('类型参数错误！');
            }
            $noun = $this->repository->orderBy('score', 'asc')->findWhere(['type' => $type, 'status' => 1]);
            return $this->succeed(ScoreRuleResource::collection($noun));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }
}
