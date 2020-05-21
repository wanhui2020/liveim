<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员举报
 * */

class MemberReport extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_report';
    protected $guarded = [];
    protected $fillable = ['member_id', 'to_member_id', 'type', 'explain', 'status', 'deal_user', 'deal_reason', 'deal_time'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    //所属举报会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    //被举报会员
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_member_id', 'id')->withDefault();
    }

    protected $appends = ['status_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::dealStatus()[$this->status];
        }
        return '';
    }
}
