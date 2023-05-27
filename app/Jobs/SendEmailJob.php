<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Focus\general\RosemailerRepository;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $emailData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users, $emailData)
    {
        $this->users = $users;
        $this->emailData = $emailData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       // dd($this->emailData);
        $mailer = new RosemailerRepository;
        $result = [];
        foreach($this->users as $user){
            //dd($user->employee->email);
            $this->emailData['mail_to'] = $user->employee->email;
           // $this->emailData['file'] = 'C:\LaravelApps\Spine\storage\app\public\files\1682418275ERP FLYER FA.pdf';
            $result= $mailer->send($user->employee->email, $this->emailData);
        }
        dd($result);
    }
}
