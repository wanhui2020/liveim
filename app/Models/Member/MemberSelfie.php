<?php

namespace App\Models;

use App\Facades\RecordFacade;
use App\Utils\SelectList;
use function foo\func;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/*
 * 会员自拍认证
 * */

class MemberSelfie extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_selfie';
    protected $guarded = [];
    protected $fillable = [
        'pic', 'member_id', 'deal_reason', 'deal_user', 'deal_time', 'status'
    ];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });

        /*
         * 更新后
         * */
        static::updated(function ($model) {
            if ($model->status == 1) {
                //审核通过
                $config = Cache::get('SystemBasic'); //取平台配置
                $gold = $config->selfie_check_award; //自拍认证奖励
                if ($gold > 0) {
                    $memberId = $model->member_id;
                    //添加一条资金流水
                    $memberAccount = MemberAccount::where(['member_id' => $memberId])->first();
                    //1.会员余币
                    $beforeAmount = $memberAccount->surplus_gold;
                    $afterAmount = $beforeAmount + $gold;
                    //2.添加记录
                    $record = new MemberRecord();
                    $record->member_id = $memberId;
                    $record->type = 10;//自拍认证奖励
                    $record->account_type = 1; //账户类型
                    $record->amount = $gold; //发生金额
                    $record->freeze_amount = 0;//冻结金额
                    $record->before_amount = $beforeAmount;//变动前额
                    $record->balance = $afterAmount;//实时余额
                    $record->status = 1;//交易成功
                    $record->remark = '自拍奖励金币';//交易备注
                    RecordFacade::addRecord($record);
                }
            }
        });
    }

    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
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
