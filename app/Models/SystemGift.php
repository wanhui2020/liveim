<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 平台礼物维护表
 * */

class SystemGift extends Model
{
    //
    use  SoftDeletes;
    protected $guarded = [];
    protected $table = 'system_gift';
    protected $fillable = ['title', 'gold', 'url', 'sort', 'status', 'remark'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }
}
