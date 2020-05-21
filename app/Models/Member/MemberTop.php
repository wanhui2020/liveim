<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/*
 * 会员排行榜
 * */
class MemberTop extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_top';
    protected $guarded = [];
    protected  static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function($model){

        });
    }
    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class,'member_id','id')->withDefault();
    }
}
