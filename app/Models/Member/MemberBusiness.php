<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/*
 * 主播会员商务自拍认证
 * */
class MemberBusiness extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_business';
    protected $guarded = [];
    protected $fillable = [
        'pic', 'member_id', 'deal_reason', 'deal_user', 'deal_time', 'status'
    ];
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


    protected $appends = ['status_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::checkStatus()[$this->status];
        }
        return '';
    }
}
