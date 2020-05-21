<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AliApiFacade extends Facade
{
    /**
     * 获取组件注册名称
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'AliApiFacade';
    }
}