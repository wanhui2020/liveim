<?php


namespace App\Http\Controllers\Api\Common\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\SystemGiftRepository;
use App\Http\Resources\SystemGiftResource;

//平台礼物接口
class GiftController extends ApiController
{
    public function __construct(SystemGiftRepository $repository)
    {
        $this->repository = $repository;
    }


    public function getList()
    {
        try {
            $list = $this->repository->orderBy('sort', 'desc')->findWhere(['status' => 1]);
            return $this->succeed(SystemGiftResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex);
        }
    }
}
