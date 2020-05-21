<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Facades\CommonFacade;
use Illuminate\Support\Facades\Cache;

/*
 * 平台配置表
 * */

class SystemBasic extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'system_basic';
    protected $fillable = [
        'yebz_remind', 'rate', 'gift_rate', 'bk_kf', 'dh_min', 'dh_rate', 'tx_min', 'tx_rate', 'tx_nofee_count', 'tx_max_count'
        , 'tx_max_amount', 'ds_rate', 'pj_dstj', 'reg_give_gold', 'yqzc_give_gold', 'invite_rate', 'selfie_check_award', 'hy_rate', 'consume_rate', 'free_text'
        , 'business_sin_fee', 'business_mul_fee', 'business_rate', 'yield_rate','invite_gift_rate'
    ];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });

        /**
         * 保存后
         */
        static::saved(function ($model) {
            Cache::forever('SystemBasic', $model);
        });
    }
}
