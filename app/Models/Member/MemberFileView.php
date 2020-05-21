<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 会员资源文件查看记录
 * */

class MemberFileView extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_file_view';
    protected $guarded = [];
    protected $fillable = ['member_id', 'to_member_id', 'member_file_id', 'gold', 'type'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    //查看会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    //主播会员
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_member_id', 'id')->withDefault();
    }


    protected $appends = ['type_cn'];

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::fileLibrary()[$this->type];
        }
        return '';
    }
}
