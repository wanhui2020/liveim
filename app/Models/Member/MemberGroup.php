<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/*
 * 会员分组
 * */
class MemberGroup extends BaseModel
{
    //
    use  SoftDeletes;
    protected $table = 'member_group';
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
