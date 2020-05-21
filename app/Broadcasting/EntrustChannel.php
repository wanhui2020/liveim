<?php

namespace App\Broadcasting;

use App\Models\CustomerUser;
use App\Models\DealEntrust;
use App\Models\SystemUser;

class EntrustChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param \App\Models\SystemUser $user
     * @return array|bool
     */
    public function join(CustomerUser $user, DealEntrust $entrust)
    {
        return $user->id === $entrust->customer_id;
    }
}
