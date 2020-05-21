<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员充值记录
 * */

class MemberRecharge extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_recharge';
    protected $guarded = [];

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

    protected $appends = ['status_cn', 'type_cn', 'way_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::payStatus()[$this->status];
        }
        return '';
    }

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::rechargeType()[$this->type];
        }
        return '';
    }

    public function getWayCnAttribute()
    {
        if (isset($this->way)) {
            return SelectList::recPayWay()[$this->way];
        }
        return '';
    }
}
