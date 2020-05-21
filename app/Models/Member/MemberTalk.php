<?php

namespace App\Models;

use App\Facades\ImFacade;
use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

/*
 * 会员聊天订单
 * */

class MemberTalk extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_talk';
    protected $guarded = [];
    protected $fillable = ['member_id', 'to_member_id', 'channel_code', 'type', 'price', 'amount', 'profit', 'total_profit', 'times', 'begin_time', 'end_time', 'status'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
        static::saved(function ($model) {
            if ($model->isDirty('status') && $model->status == 2) {
                //IM房间解散
                ImFacade::DissolveRoom($model->id);
            }
        });
    }

    //所属会员
    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }

    //所属会员（主播）
    public function tomember()
    {
        return $this->belongsTo(MemberInfo::class, 'to_member_id', 'id')->withDefault();
    }


    protected $appends = ['status_cn', 'type_cn'];

    public function getStatusCnAttribute()
    {
        if (isset($this->status)) {
            return SelectList::talkStatus()[$this->status];
        }
        return '';
    }

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::talkType()[$this->type];
        }
        return '';
    }
}
