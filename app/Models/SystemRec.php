<?php

namespace App\Models;

use App\Utils\SelectList;
use Illuminate\Database\Eloquent\SoftDeletes;
/*
 * 平标充值项维护
 * */
class SystemRec extends BaseModel
{
    //
    use  SoftDeletes;
    protected $guarded = [];
    protected $table = 'system_rec';
    protected  static function boot()
    {
        parent::boot();
        /*
         * 创建开始
         * */
        static::creating(function($model){

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
            return SelectList::recType()[$this->type];
        }
        return '';
    }
}
