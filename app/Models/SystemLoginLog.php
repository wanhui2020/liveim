<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;

class SystemLoginLog extends BaseModel
{
    protected $fillable = ['relevance_type', 'relevance_id', 'name', 'device', 'browser', 'ip',];

    protected static function boot()
    {
        parent::boot();
        Relation::morphMap([
            'SystemUser' => 'App\Models\SystemUser',
            'AccountAgent' => 'App\Models\AccountAgent',
            'AccountMember' => 'App\Models\AccountMember',
            'AccountCustomer' => 'App\Models\AccountCustomer',
        ]);

        /**
         * 创建开始
         */
        static::creating(function ($model) {

        });
        /**
         * 创建成功后
         */
        static::created(function ($model) {

        });

        /**
         * 更新成功
         */
        static::updating(function ($model) {

        });
        /**
         * 删除成功
         */
        static::deleted(function ($model) {

        });

        /**
         * 创建开始
         */
        static::saving(function ($model) {

        });
        /**
         * 创建开始
         */
        static::saved(function ($model) {
        });


    }

    public function relevance()
    {
        return $this->morphTo();
    }
}
