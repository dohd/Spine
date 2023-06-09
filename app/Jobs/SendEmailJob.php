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
        $mailer = new RosemailerRepository;
        $result = [];
        foreach($this->users as $user){
            $pdf = new \Mpdf\Mpdf(config('pdf') + ['margin_left' => 4, 'margin_right' => 4]);
            $html = view('focus.bill.payslip', ['user' =>$user])->render();
            $pdf->WriteHTML($html);
            $pdfFilePath = storage_path('app/public/files/' . uniqid() . '.pdf');
            $pdf->Output($pdfFilePath, 'F');
            $this->emailData['mail_to'] = $user->employee->email;
            $this->emailData['file'] = $pdfFilePath;
            $result= $mailer->send($user->employee->email, $this->emailData);
        }
        //dd($result);
    }
}
