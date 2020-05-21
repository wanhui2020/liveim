<?php

namespace App\Jobs;

use App\Facades\DealFacade;
use App\Traits\ResultTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EntrustSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ResultTrait;
    protected $entrustId;
//    /**
//     * 任务可以尝试的最大次数。
//     *
//     * @var int
//     */
//    public $tries = 10;
//    public $time = 10;
//    /**
//     * 任务可以执行的秒数 (超时时间)。
//     *
//     * @var int
//     */
//    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($entrustId)
    {
        $this->entrustId = $entrustId;

    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DealFacade::entrustSend($this->entrustId);

    }

    public function failed(Exception $exception)
    {
        $this->logs('队列执行失败', $exception->getMessage());
    }
}
