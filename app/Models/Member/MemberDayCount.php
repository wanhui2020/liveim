<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 *
 * */

class MemberDayCount extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_day_count';
    protected $guarded = [];
    protected $fillable = ['member_id', 'dayint', 'rec_gold', 'award_gold', 'profit_gold', 'consume_gold', 'rec_money', 'take_money', 'profit_money', 'consume_money'];

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

}
