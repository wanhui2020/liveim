<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class SystemConfig extends BaseModel
{
    protected $table = 'system_config';
    protected $fillable = [
        'name', 'version', 'ios_version', 'app_pic', 'domain', 'ba_no', 'footer', 'cp_name', 'address', 'tel', 'weixin'
        , 'reg_welcome', 'fwxy', 'vip_explain', 'about_us', 'android_down', 'ios_down', 'warm_prompt', 'vip_privilege', 'app_explain','keyword'
        ,'takenow_explain'
    ];

    protected static function boot()
    {
        parent::boot();

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
         * 创建保存后
         */
        static::saved(function ($model) {
            Cache::forever('SystemConfig', $model);
        });


    }

}
