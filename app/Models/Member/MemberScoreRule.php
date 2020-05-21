<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员积分规则
 * */

class MemberScoreRule extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_score_rule';
    protected $guarded = [];
    protected $fillable = [
        'type', 'desc', 'score', 'status', 'remark'
    ];

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
            //创建当前数据连续签到次数
//            $preModel = MemberSignIn::where('member_id', $model->member_id)->orderBy('qd_date', 'desc')->first();
//            if ($preModel != null) {
//                //比较两个日期是否相差一天
//                $date1 = strtotime($model->qd_date);
//                $date2 = strtotime($preModel->qd_date);
//                $result = floor((strtotime($date1) - strtotime($date2)) / 86400);
//                if ($result == 1) {
//                    $model->lx_days += 1;
//                    $model->save();
//                }
//            }
        });
    }

    protected $appends = ['status_cn', 'type_cn', 'desc_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::statusList()[$this->status];
        }
        return '';
    }

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::scoreType()[$this->type];
        }
        return '';
    }

    public function getDescCnAttribute()
    {
        if (isset($this->desc)) {
            return SelectList::scoreRuleType()[$this->desc];
        }
        return '';
    }
}
