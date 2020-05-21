<?php

namespace App\Console\Commands;

use App\Facades\StockFacade;
use App\Jobs\TradeJob;
use Illuminate\Console\Command;

class InitBaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '基础数据初始化';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        TradeJob::withChain([
            StockFacade::StockBasicsInit(),//基础分类、交易所维护
            StockFacade::StockClosedsSync(),//交易日历
            StockFacade::StockBasicsSync(),//交易股票
            StockFacade::StockMarketSync(),//交易行情
            StockFacade::StockTodaySync(),//今日可交易股票
        ])->dispatch();
    }
}
