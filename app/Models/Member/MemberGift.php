<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员(主播)发/收礼物记录
 * */

class MemberGift extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_gift';
    protected $guarded = [];
    protected $fillable = ['member_id', 'to_member_id', 'gift_id', 'gift_name', 'quantity', 'gold', 'is_sys'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    //所属礼物
    public function gift()
    {
        return $this->belongsTo(SystemGift::class, 'gift_id', 'id')->withDefault();
    }

    //所属会员（赠送方）
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }


    //所属会员（接收方）
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_member_id', 'id')->withDefault();
    }
}
