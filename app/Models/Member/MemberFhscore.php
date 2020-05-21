<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员富豪积分明细记录
 * */

class MemberFhscore extends BaseModel
{
    //
    use  SoftDeletes;
    protected $table = 'member_fh_score';
    protected $guarded = [];
    protected $fillable = ['member_id', 'type', 'score', 'remark'];

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
            //创建成功后更新积分
            $memberInfo = MemberInfo::find($model->member_id);
            $account = $memberInfo->account;
            $account->fh_score += $model->score;
            $account->save();

            //当前富豪积分
            $score = $account->fh_score;
            //查询积分所在等级
            $level = MemberLevel::where(['type' => 1, 'status' => 1])->where('max_score', '>=', $score)->orderBy('max_score', 'asc')->first();
            if (isset($level)) {
                //存在等级
                $memberInfo->haoqi = $level->lvl;
                $memberInfo->save();
            }
        });
    }

    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    protected $appends = ['type_cn'];

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::scoreRuleType()[$this->type];
        }
        return '';
    }
}
