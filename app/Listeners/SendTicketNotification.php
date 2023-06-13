<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTicketNotification
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
    public function handle($event)
    {
        $admins = Hrm::whereHas('role', function ($query) {
            $query->where('id', 2)->orWhere('id',4);
        })->get();
        Notification::send($admins, new NewUserNotification($event->user));
    }
}
