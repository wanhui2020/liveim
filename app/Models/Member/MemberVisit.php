<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员访问记录
 * */

class MemberVisit extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_visit';
    protected $guarded = [];

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
            //创建成功后更新访问次数
            $account = MemberAccount::where('member_id', $model->to_member_id)->first();
            if (isset($account)) {
                $account->visit_count += 1;
                $account->save();
            }
        });
    }

    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }
}
