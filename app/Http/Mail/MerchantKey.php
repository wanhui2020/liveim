<?php

namespace App\Mail;

use App\Models\MerchantUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MerchantKey extends Mailable
{
    use Queueable, SerializesModels;

    private $merchant;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MerchantUser $merchant)
    {
        $this->merchant = $merchant;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.merchant.key')->with(['merchant' => $this->merchant])
            ->subject(env('APP_NAME') . '-商户密钥');
    }
}
