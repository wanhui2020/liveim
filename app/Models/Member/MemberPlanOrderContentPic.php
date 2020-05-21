<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 主播商务服务计划图片
 * */

class MemberPlanOrderContentPic extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_planorder_content_pic';
    protected $guarded = [];
    protected $fillable = ['plan_id', 'pic'];

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
