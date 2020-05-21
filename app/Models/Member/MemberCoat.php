<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 主播会员衣服管理
 * */

class MemberCoat extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_coat';
    protected $guarded = [];
    protected $fillable = ['member_id', 'title', 'url', 'status', 'sort'];

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

    protected $appends = ['status_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::statusList()[$this->status];
        }
        return '';
    }
}
