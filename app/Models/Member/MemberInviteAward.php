<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员邀请奖励明细记录
 * */

class MemberInviteAward extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_invite_award';
    protected $guarded = [];
    protected $fillable = ['member_id', 'from_member_id', 'type', 'gold', 'money'];

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

    //贡献会员
    public function frommember()
    {
        return $this->belongsTo(MemberInfo::class, 'from_member_id', 'id')->withDefault();
    }

}
