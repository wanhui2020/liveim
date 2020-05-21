<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Facades\CommonFacade;
/*
 * 平标会员标签表
 * */
class SystemTag extends Model
{
    //
    use  SoftDeletes;
    protected $guarded = [];
    protected $table = 'system_tag';
    protected  static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function($model){

        });
    }
}
