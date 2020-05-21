<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Input\InputArgument;


/**
 * 股票行情数据
 * Class StockMarketCommand
 * @package App\Console\Commands
 */
class RedisSubscribeCommand extends Command
{
    /**
     * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'redis:subscribe';

    /**
     * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Subscribe to a Redis channel';

    /**
     * 执行控制台命令
     *
     * @return mixed
     */
    public function handle()
    {
        Redis::subscribe(['news'], function($message) {
            echo $message;
        });
    }

}
