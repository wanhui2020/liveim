<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class MemberFacade extends Facade
{
    /**
     * 获取组件注册名称
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'MemberFacade';
    }
}