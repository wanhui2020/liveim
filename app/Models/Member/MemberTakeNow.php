<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员提现记录
 * */

class MemberTakeNow extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_takenow';
    protected $guarded = [];
    protected $fillable = ['member_id', 'order_no', 'amount', 'fee_money', 'real_amount', 'way', 'account_no', 'account_name', 'bank', 'desc', 'status', 'deal_user', 'deal_time', 'deal_reason', 'remark'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });

        /**
         * 创建成功后
         */
        static::created(function ($model) {
//            if ($model->status == 0) {
//                //保存成功后，先冻结可提现余额
//                $account = MemberAccount::where('member_id', $model->member_id)->first();
//                if (isset($account)) {
//                    $account['notuse_rmb'] += $model->amount;
//                    $account->save();
//                }
//            }
        });

        /**
         * 删除成功
         */
        static::deleted(function ($model) {
//            if ($model->status == 0) {
//                //保存成功后，解冻不可提现金额
//                $account = MemberAccount::where('member_id', $model->member_id)->first();
//                if (isset($account)) {
//                    $account['notuse_rmb'] -= $model->amount;
//                    $account->save();
//                }
//            }
        });

        /*
         * 更新成功后
         * */
        static::saved(function ($model) {
//            if ($model->status == 1 || $model->status == 2) {
//                //通过/或拒绝，解冻不可提现金额
//                $account = MemberAccount::where('member_id', $model->member_id)->first();
//                if (isset($account)) {
//                    $account['notuse_rmb'] -= $model->amount;
//                    $account->save();
//                }
//            }
        });

    }


    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    protected $appends = ['status_cn', 'way_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::checkStatus()[$this->status];
        }
        return '';
    }

    public function getWayCnAttribute()
    {
        if (isset($this->way)) {
            return SelectList::takeNowWay()[$this->way];
        }
        return '';
    }
}
