<?php


namespace App\Http\Controllers\Api\Common\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\SystemRecRepository;
use App\Http\Resources\SystemRecResource;
use Illuminate\Http\Request;

//平台充值项接口
class RecController extends ApiController
{
    public function __construct(SystemRecRepository $repository)
    {
        $this->repository = $repository;
    }

    //通过类型获取充值项
    public function getListByType(Request $request)
    {
        try {
            $type = $request['type'];
            if (!in_array($type, [0, 1, 2], false)) {
                return $this->validation('类型参数错误！');
            }
            $noun = $this->repository->orderBy('cost', 'asc')->findWhere(['type' => $type, 'status' => 1]);
            return $this->succeed(SystemRecResource::collection($noun));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex->getMessage());
        }
    }
}
