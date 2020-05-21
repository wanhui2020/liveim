<?php

namespace App\Providers;

use App\Events\DealEntrustEvent;
use App\Events\DealPositionEvent;
use App\Events\StockMarketEvent;
use App\Listeners\DealEntrustNotification;
use App\Listeners\DealPositionNotification;
use App\Listeners\StockMarketNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        DealEntrustEvent::class => [
            DealEntrustNotification::class
        ],

        DealPositionEvent::class => [
            DealPositionNotification::class
        ],

        StockMarketEvent::class => [
            StockMarketNotification::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
