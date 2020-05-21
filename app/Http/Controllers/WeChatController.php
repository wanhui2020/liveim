<?php

namespace App\Http\Controllers;

use App\Facades\AliApiFacade;
use App\Facades\CommonFacade;
use App\Facades\ContractFacade;
use App\Facades\EntrustFacade;
use App\Facades\FinanceFacade;
use App\Facades\ImFacade;
use App\Http\Controllers\Controller;
use App\Models\CustomerUser;
use App\Services\MnService;
use App\Traits\ResultTrait;

class WeChatController extends Controller
{
    use ResultTrait;

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {

        $app = app('wechat.official_account');
        $app->server->push(function($message){
            return "欢迎关注 overtrue！";
        });

        return $app->server->serve();
    }
}