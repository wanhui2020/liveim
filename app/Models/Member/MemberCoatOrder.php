<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 主播换衣订单记录
 * */

class MemberCoatOrder extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_coat_order';
    protected $guarded = [];
    protected $fillable = ['member_id', 'to_member_id', 'member_coat_id', 'gold', 'status'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    //衣服
    public function coat()
    {
        return $this->belongsTo(MemberCoat::class, 'member_coat_id', 'id')->withDefault();
    }

    //查看会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    //主播会员
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_member_id', 'id')->withDefault();
    }


    protected $appends = ['status_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::coatOrderStatus()[$this->status];
        }
        return '';
    }
}
