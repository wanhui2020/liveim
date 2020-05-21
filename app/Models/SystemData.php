<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 平台基础数据表
 * */

class SystemData extends Model
{
    //
    use  SoftDeletes;
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function ($model) {

        });
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
            return SelectList::dataTypeList()[$this->type];
        }
        return '';
    }
}
