<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员账户表
 * */

class MemberAccount extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_account';
    protected $guarded = [];
    protected $fillable = ['member_id', 'surplus_gold', 'notuse_gold', 'cantx_gold', 'surplus_rmb', 'notuse_rmb', 'total_consume', 'total_income'
        , 'sys_plus', 'sys_minus', 'score', 'ml_score', 'fh_score', 'sign_days', 'bq_count', 'visit_count', 'lx_login_days'
        , 'lx_login_max_days', 'text_charge', 'voice_charge', 'video_charge', 'picture_view_charge', 'video_view_charge', 'vip_level', 'vip_expire_date'
        , 'gift_count', 'xy_score', 'myd_score', 'yz_score'
    ];


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

    protected $appends = ['balance_gold', 'cantx_rmb', 'is_vip'];

    //实时可用金币
    public function getBalanceGoldAttribute()
    {
        if (isset($this->surplus_gold)) {
            return $this->surplus_gold - $this->notuse_gold;
        }
        return 0;
    }

    //可提现人民币
    public function getCantxRmbAttribute()
    {
        if (isset($this->surplus_rmb)) {
            if ($this->notuse_rmb < 0){
                return $this->surplus_rmb + $this->notuse_rmb;
            }else{
                return $this->surplus_rmb - $this->notuse_rmb;
            }
        }
        return 0;
    }

    public function getIsVipAttribute()
    {
        if (isset($this->vip_expire_date)) {
            if ($this->vip_expire_date > date("Y-m-d")) {
                return true;
            }
        }
        return false;
    }


    /**
     * 会员实名
     */
    public function realname()
    {
        return $this->hasOne(MemberRealName::class, 'member_id', 'id');
    }
}
