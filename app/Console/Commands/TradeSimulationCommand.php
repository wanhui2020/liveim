<?php

namespace App\Console\Commands;

use App\Events\DealEntrustEvent;
use App\Events\DealPositionEvent;
use App\Traits\ResultTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Input\InputArgument;


/**
 * 股票交易测试
 * Class StockMarketCommand
 * @package App\Console\Commands
 */
class TradeSimulationCommand extends Command
{
    use ResultTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TradeSimulation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '股票交易测试数据同步';

    public function handle()
    {
        echo '准备股票交易测试数据同步';
        $this->fire();
    }

    public function fire()
    {

        while (true) {
            try {

                $entrusts = Redis::hVals('deal:entrusts');
                foreach ($entrusts as $entrust) {
                    $entrust = json_decode($entrust);
                    if ($entrust->entrust_status == 9) {

                        if ($entrust->cancel_status == 9) {
                            $entrust->cancel_num = $entrust->entrust_num - $entrust->success_num;
                        }
                        $entrust->success_num = $entrust->entrust_num - $entrust->cancel_num;
                        $entrust->success_price = $entrust->entrust_price * (mt_rand(90, 110) / 100);
                        event(new DealEntrustEvent($entrust));
                        Redis::set('system:listen:stock', Carbon::now()->toDateTimeString());
                    }
                }
            } catch (\Exception $ex) {
                $this->exception($ex);
            }
            sleep(3);
        }


    }


    private function getSocket($command)
    {
        try {
            set_time_limit(0);
            $host = env('STOCK_IP');
            $port = env('STOCK_PORT');
            //创建一个socket
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("cannot create socket\n");
            $conn = socket_connect($socket, $host, $port) or die("cannot connect server\n");

            socket_write($socket, $command) or die("cannot write data\n");
            $result = '';
            while ($callback = socket_read($socket, 1024, PHP_BINARY_READ)) {
                $result = $result . $callback;
            }
            socket_close($socket);
            $resp = json_decode($result);
            return $resp;
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
