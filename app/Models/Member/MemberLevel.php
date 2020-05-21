<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\SoftDeletes;
/*
 * 会员等级
 * */
class MemberLevel extends BaseModel
{
    //
    use  SoftDeletes;
    protected $table = 'member_level';
    protected $guarded = [];
    protected $fillable = ['type','lvl','name','min_score','max_score','status','remark'];
    protected  static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function($model){

        });
    }
    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class,'member_id','id')->withDefault();
    }

    protected $appends = ['status_cn', 'type_cn'];

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
            return SelectList::levelType()[$this->type];
        }
        return '';
    }
}
