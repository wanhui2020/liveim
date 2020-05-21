<?php

namespace App\Repositories\Criteria\Merchant;

use App\Repositories\Criteria\Criteria;
use App\Repositories\Contracts\RepositoryInterface as Repository;
use Illuminate\Support\Facades\Auth;

class MerchantValid extends Criteria
{


    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
//        $model = $model->where('merchant_id', Auth::guard('merchant')->user()->id);
    return $model;
}
}
