<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, array $params = null)
    {
        $this->type = $type;
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function handle()
    {
        try {

            switch ($this->type) {
                case 'order.affirm':
                    $order = OrderRoom::find(data_get($this->params, 'id'));
                    if (isset($order)) {
                        $resp = VmsFacade::singleCallByTts($order->merchant->tel, 'TTS_136389082', ['name' => $order->merchant->name]);
                        if ($resp->status == 0) {
                            $order->saveLog('电话通知商家成功！');
                        } else {
                            $order->saveLog('电话通知商家失败功！', $resp->msg);
                        }
                    }
                    break;

            }
        } catch (Exception $ex) {
            Result::exception($ex);
            throw new \Exception($ex);
        }

    }

    /**
     * 要处理的失败任务。
     *
     * @param  Exception $exception
     * @return void
     */
    public function failed(Exception $ex)
    {
        Result::exception($ex);
    }
}
