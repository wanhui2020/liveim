<?php

namespace App\Models;

use App\Facades\BaseFacade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Facades\CommonFacade;

/*
 * 会员扩展信息表
 * */

class MemberExtend extends Model
{
    //
    use  SoftDeletes;
    protected $table = 'member_extend';
    protected $guarded = [];
    protected $fillable = ['member_id', 'signature', 'city', 'address', 'hobbies', 'height', 'weight', 'constellation', 'talk_rate', 'gift_rate', 'other_rate', 'invit_reg_rate', 'text_fee', 'voice_fee', 'video_fee', 'picture_view_fee', 'video_view_fee', 'coat_fee', 'is_business'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });

        /**
         * 创建开始
         */
        static::saving(function ($model) {
            if ($model->isDirty('hobbies')) {
                if (!BaseFacade::keyword($model->hobbies)) {
                    return false;
                }
            }
        });
    }

    public function member()
    {
        return $this->belongsTo(MemberInfo::class, 'member_id', 'id')->withDefault();
    }
}
