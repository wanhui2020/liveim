<?php

namespace App\Models;

use function foo\func;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/*
 * 会员签到记录
 * */

class MemberSignIn extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_signin';
    protected $guarded = [];
    protected $fillable = ['member_id', 'award', 'lx_days', 'qd_date', 'qd_date', 'bq_date'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });

        /*
         * 创建成功后
         * */
        static::created(function ($model) {
            //创建成功后，重新计算连续签到天数
            $qddate = $model->qd_date;
            $memberId = $model->member_id;
            if (isset($model->bq_date)) {
                //补签的,查询最后一次签到日期
                $qddate = MemberSignIn::where('member_id', $memberId)->orderBy('qd_date', 'desc')->first()->qd_date;
            }
            $ret = DB::select('SELECT f_continuty_days(' . $memberId . ',DATE(\'2019-01-01\'),DATE(\'' . $qddate . '\'),\'signin\') as days;');
            $days = $ret[0]->days;
            $account = MemberAccount::where('member_id', $memberId)->first();
            $account->sign_days = $days;
            $account->save();
        });
    }

    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }
}
