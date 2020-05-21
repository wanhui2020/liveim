<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SystemUser extends Authenticatable
{
    use SoftDeletes, Notifiable;
    public $incrementing = false;
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'type', 'password', 'status',
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


    }

    protected $guarded = ['password'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    /**
     * 登录日志
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logins()
    {
        return $this->morphMany(SystemLoginLog::class, 'relevance', 'relevance_type', 'relevance_id');
    }
}
