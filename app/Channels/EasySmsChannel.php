<?php


namespace App\Channels;


use App\Facades\EasySmsFacade;

class EasySmsChannel
{
    /**
     * 发送给定通知
     * @param Model $notifiable
     * @param Notification $notification
     * @return mixed
     */
    public function send($notifiable, $notification)
    {
        return EasySmsFacade::send($notifiable->routeNotificationFor('easySms'), $notification->toSms($notifiable));
    }
}