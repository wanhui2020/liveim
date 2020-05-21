<?php

namespace App\Repositories\Criteria;

use App\Repositories\Criteria\Criteria;
use App\Repositories\Contracts\RepositoryInterface as Repository;
use Illuminate\Support\Facades\Auth;

class MerchantCriteria extends Criteria
{


    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {

        $model = $model->where('merchant_id', Auth::guard('merchant')->user()->id);
//        if (isset($model->merchant_id)) {
//            $model = $model->where('merchant_id', Auth::guard('merchant')->user()->id);
//        }
//        $model = $model->where('merchant_id', Auth::guard('merchant')->user()->id);
//        if (isset($model->merchant_id)){
//            $model->merchant_id=Auth::guard('merchant')->user()->id;
//        }
        return $model;
    }
}