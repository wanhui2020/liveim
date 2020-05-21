<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 平台短信发送记录表
 * */

class Sms extends Model
{
    //
    use  SoftDeletes;
    protected $guarded = [];
    protected $table = 'sms';
    protected $fillable = ['type', 'phone', 'content', 'verify_code'];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
    }

    protected $appends = ['type_cn'];

    public function getTypeCnAttribute()
    {
        if (isset($this->type)) {
            return SelectList::smsType()[$this->type];
        }
        return '';
    }
}
