<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class WeixinMiniJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, array $params)
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
                case 'message.paid'://待付订单提醒
                    $order = OrderRoom::find(data_get($this->params, 'id'));
                    if (isset($order) && $order->pay_status == 9) {
                        $user = $order->user;
                        $formid = $user->formids()->first();
                        if (isset($formid)) {
                            $data = ['touser' => $user->openid,
                                'template_id' => 'SxdVfZBE9uCgAKFcfdD8SuqbDDH7Kc9S3T_z7RnCM0Y',
                                'page' => 'pages/orderDetail_noPay/orderDetail_noPay?orderId=' . $order->id,
                                'form_id' => $formid->form_id,
                                'data' => [
                                    'keyword1' => data_get($order, 'no'),
                                    'keyword2' => data_get($order, 'name'),
                                    'keyword3' => data_get($order, 'room_name'),
                                    'keyword4' => data_get($order, 'arrival_date'),
                                    'keyword5' => data_get($order, 'total_amount'),
                                    'keyword6' => '请在' . Carbon::parse(data_get($order, 'created_at'))->addMinutes(5)->toDateTimeString() . '之前完成支付',
                                ]];
                            $resp = WeixinMiniFacade::init()->sendTemplate($data);
                            Result::logs('小程序模板信息', $resp);
                            $formid->forceDelete();
                        } else {
                            Result::logs('小程序模板信息', '无$formid');
                        }
                    }
                    break;
            }
        } catch (Exception $ex) {
            Result::exception($ex);
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
