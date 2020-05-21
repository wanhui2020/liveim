<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 系统消息
 * */

class SystemMessage extends Model
{
    //
    use  SoftDeletes;
    protected $guarded = [];
    protected $table = 'system_message';
    protected $fillable = ['id', 'title', 'content', 'to_id', 'type', 'member_id', 'push_token', 'is_push', 'is_read', 'read_time'];


    //接收会员
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_id', 'id')->withDefault();
    }

    //相关会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }


    protected $appends = ['type_cn'];

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::messageType()[$this->type];
        }
        return '';
    }

}
