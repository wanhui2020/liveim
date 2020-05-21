<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员打赏记录
 * */

class MemberReward extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_reward';
    protected $guarded = [];
    protected $fillable = ['member_id', 'to_member_id', 'gold', 'remark'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    //打赏会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    //所属会员（接收打赏的主播）
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_member_id', 'id')->withDefault();
    }
}
