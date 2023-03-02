<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use App\Models\lead\Lead;
use Illuminate\Support\Facades\DB;
use App\Notifications\TicketNotification;
use Carbon\Carbon;
use App\Models\hrm\Hrm;
use App\Models\Access\User\User;
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
        
        $leads = Lead::whereDate('reminder_date', '<=', Carbon::today())->whereDate('exact_date','>=', Carbon::today())->withoutGlobalScopes()->get();
        if (is_object($leads)) {
            $users = User::whereHas('user_associated_permission', function($query){
                $query->where('name', 'create-lead');
            })->withoutGlobalScopes()->get();
            foreach ($leads as $lead) {
    
                foreach($users as $user){
                    $user->notify(new TicketNotification($lead));
                }
               
            }
        }
       
    }
}
