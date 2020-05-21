<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/*
 * 会员实名认证
 * */
class MemberRealName extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_realname';
    protected $guarded = [];
    protected $fillable = [
        'member_id','cert_no','name','cert_zm','cert_fm','cert_sc','selfie_pic', 'deal_reason', 'deal_user', 'deal_time', 'status'
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
