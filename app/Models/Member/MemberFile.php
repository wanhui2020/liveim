<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员资源库文件
 * */

class MemberFile extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_file';
    protected $guarded = [];
    protected $fillable = ['member_id', 'type', 'url', 'is_cover', 'status', 'deal_user', 'deal_time', 'deal_reason', 'sort'];

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


    protected $appends = ['status_cn', 'type_cn', 'is_cover_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::checkStatus()[$this->status];
        }
        return '';
    }

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::fileLibrary()[$this->type];
        }
        return '';
    }

    public function getIsCoverCnAttribute()
    {
        if (isset($this->is_cover)) {
            return SelectList::yesOrNo()[$this->is_cover];
        }
        return '';
    }

}
