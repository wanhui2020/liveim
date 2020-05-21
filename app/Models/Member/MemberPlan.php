<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 主播商务服务计划安排管理
 * */

class MemberPlan extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_plan';
    protected $guarded = [];
    protected $fillable = ['member_id', 'project', 'content', 'sort','status'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    public function pics()
    {
        return $this->hasMany(MemberPlanPic::class, 'plan_id', 'id');
    }

    protected $appends = ['pic_count'];

    public function getPicCountAttribute()
    {
        return $this->pics()->count();
    }

}
