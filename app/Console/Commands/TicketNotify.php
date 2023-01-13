<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use App\Models\lead\Lead;
use Illuminate\Support\Facades\DB;
use App\Notifications\TicketNotification;
use Carbon\Carbon;
use App\Models\hrm\Hrm;
use Illuminate\Support\Facades\Notification;
use Illuminate\Console\Command;

class TicketNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ticket Notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Log::info(Auth::id());
        $leads = DB::table('leads')->get();
        foreach($leads as $lead) {
           // $diffInDays = $lead->reminder_date->diff(Carbon::now())->days;
           $ticketDate = Carbon::parse($lead->reminder_date);
           $todayDate = Carbon::parse(Carbon::now()->toDateString());
            if ($ticketDate->eq($todayDate)) {
                Log::info('They are equal');
                $ticket_user = DB::table('users')->where('id', $lead->user_id)->get();
                Log::info($ticket_user);
                // foreach ($ticket_user as $ticket_users) {
                //     var_dump($ticket_user);
                //     Log::info($ticket_users);
                //     $ticket_users->notify(new TicketNotification($lead->reference));
                //     // Log::info($ticket_users);
                //     //  Notification::send($ticket_users, new TicketNotification($lead->reference));
                // }
                Log::info($lead->reference);
               // Notification::send($ticket_user, new TicketNotification($lead->reference));
               $ticket_user->each->notify(new TicketNotification($lead->reference));
            }
           // Log::info('They are NOT equal');
            

            //$lead->notify("Your deadline is in $diffInDays day!");
        }
    }
}
