<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WeixinMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $type;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $user,$type,array $params)
    {
        $this->user = $user;
        $this->type = $type;
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function handle()
    {
        try {
            $app=WeixinFacade::init();
            switch ($this->type) {
                case 'message.notice':
                    $touser = $this->params['touser'];
                    $templatid = $this->params['templatid'];
                    $data = $this->params['data'];
                    $url = $this->params['url'];
                    $app->noticeSend($touser, $templatid, $data, $url);
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
