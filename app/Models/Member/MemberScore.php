<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员积分明细记录
 * */

class MemberScore extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_score';
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
            $account = MemberAccount::where(['member_id' => $model->member_id])->first();
            $account->score += $model->score;
            $account->save();
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
