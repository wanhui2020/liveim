<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 主播商务服务订单行程内容
 * */

class MemberPlanOrderContent extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_planorder_content';
    protected $guarded = [];
    protected $fillable = ['order_id', 'project', 'content', 'sort'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    //所属订单
    public function order()
    {
        return $this->belongsTo(MemberPlanOrder::class, 'order_id', 'id')->withDefault();
    }

}
