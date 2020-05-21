<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * 系统banner
 * */

class SystemBanner extends Model
{
    //
    use  SoftDeletes;
    protected $guarded = [];
    protected $table = 'system_banner';
    protected $fillable = ['id', 'name', 'url', 'status', 'sort'];

}
