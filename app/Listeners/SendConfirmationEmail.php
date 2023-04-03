<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\NewOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendConfirmationEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        //
        Mail::to(config('app.admin_email'))->send(new NewOrder($event->order));
    }
}
