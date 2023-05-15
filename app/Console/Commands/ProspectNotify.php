<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use App\Models\prospectcallresolved\ProspectCallResolved;
use Illuminate\Support\Facades\DB;
use App\Notifications\ProspectNotification;
use Carbon\Carbon;
use App\Models\hrm\Hrm;
use App\Models\Access\User\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Console\Command;

class ProspectNotify extends Command
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
    protected $description = 'Prospect Notification';

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
        
        $prospectcallresolved = ProspectCallResolved::whereDate('reminder_date', '=', Carbon::today())->withoutGlobalScopes()->get();
        if (is_object($prospectcallresolved)) {
            $users = User::whereHas('user_associated_permission', function($query){
                $query->where('name', 'create-lead');
            })->withoutGlobalScopes()->get();
            foreach ($prospectcallresolved as $prospectcall) {
    
                foreach($users as $user){
                    $user->notify(new ProspectNotification($prospectcall));
                }
               
            }
        }
       
    }
}
