<?php

namespace App\Notifications;

use App\Channels\EasySmsChannel;
use App\Gateways\AliyunGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Overtrue\EasySms\Message;


/**
 * 充值成功通知
 * Class RechargePaid
 * @package App\Notifications
 */
class RechargePaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['easySms'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return Message
     */
    public function toSms($notifiable)
    {
        return (new Message())->setContent($notifiable->username . ',您好！ 这是短信内容。');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
