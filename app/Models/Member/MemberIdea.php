<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员意见反馈
 * */

class MemberIdea extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_idea';
    protected $guarded = [];
    protected $fillable = ['member_id', 'content', 'replay', 'replay_user', 'replay_time', 'status'];

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


    protected $appends = ['status_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::replayStatus()[$this->status];
        }
        return '';
    }
}
