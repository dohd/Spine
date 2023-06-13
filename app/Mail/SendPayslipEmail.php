<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPayslipEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $payslipFilePath;
    public $recipientName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $payslipFilePath, string $recipientName)
    {
        $this->payslipFilePath = $payslipFilePath;
        $this->recipientName = $recipientName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('focus.bill.send_payslip')
            ->subject('Your Monthly Payslip')
            ->attach($this->payslipFilePath, [
                'as' => 'Payslip.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
