<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员信誉评价
 * */

class MemberCredit extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_credit';
    protected $guarded = [];
    protected $fillable = ['member_id', 'to_member_id', 'xy_score', 'yz_score', 'myd_score'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    //评价会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    //被评价会员
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_member_id', 'id')->withDefault();
    }

}
