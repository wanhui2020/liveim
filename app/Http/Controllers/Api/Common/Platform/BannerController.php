<?php


namespace App\Http\Controllers\Api\Common\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\SystemBannerRepository;
use App\Http\Resources\SystemBannerResource;

class BannerController extends ApiController
{
    public function __construct(SystemBannerRepository $repository)
    {
        $this->repository = $repository;
    }


    public function getList()
    {
        try {
            $list = $this->repository->orderBy('sort', 'desc')->findWhere(['status' => 1]);
            return $this->succeed(SystemBannerResource::collection($list));
        } catch (\Exception $ex) {
            return $this->validation('操作失败', $ex);
        }
    }
}
