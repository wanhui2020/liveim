<?php

namespace App\Events;

use App\Facades\DealFacade;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 委托事件
 * Class DealEntrustEvent
 * @package App\Events
 */
class DealEntrustEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $entrust;

    /**
     * 创建一个新的事件实例.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->entrust = $data;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     */
    public function broadcastOn()
    {
//        return new PrivateChannel('entrust.' . $this->entrust->id);
        return new  Channel('entrust');
    }


    /**
     * 事件的广播名称.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'deal.entrust';
    }
}