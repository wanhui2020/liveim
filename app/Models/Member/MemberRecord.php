<?php

namespace App\Models;

use App\Facades\RecordFacade;
use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

/*
 * 会员资金流水
 * */

class MemberRecord extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_record';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });

        static::created(function ($model) {
            RecordFacade::memberInviteAward($model);
            RecordFacade::memberDayCount($model); //日统计
            RecordFacade::memberScoreCount($model); //积分统计

            //创建一条账户表 如果是消耗金币 要扣除发生金额 如果扣除后小于0就直接等于0
            $account = MemberAccount::where('member_id',$model->member_id)->first();
            if ($model->account_type == 1 && in_array($model->type, [2, 11, 12, 13, 14, 15])) {
                $lock_gold = $account->lock_gold + $model->amount;
                if ($lock_gold < 0) {
                    $account->lock_gold = 0;
                }else{
                    $account->lock_gold = $lock_gold;
                }
                $account->save();
            }
        });


    }

    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    protected $appends = ['status_cn', 'type_cn', 'account_type_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::recordStatus()[$this->status];
        }
        return '';
    }

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::recordType()[$this->type];
        }
        return '';
    }

    public function getAccountTypeCnAttribute()
    {
        if (isset($this->account_type)) {
            return SelectList::recordAccountType()[$this->account_type];
        }
        return '';
    }
}
