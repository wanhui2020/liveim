<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Facades\CommonFacade;
/*
 * 平台文件存储表
 * */
class SystemFile extends Model
{
    //
    use  SoftDeletes;
    protected $guarded = [];
    protected $table = 'system_file';
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
