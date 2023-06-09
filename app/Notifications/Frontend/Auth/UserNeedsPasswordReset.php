<?php

namespace App\Notifications\Frontend\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Repositories\Focus\general\RosemailerRepository;

/**
 * Class UserNeedsPasswordReset.
 */
class UserNeedsPasswordReset extends Notification
{
    use Queueable;
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * UserNeedsPasswordReset constructor.
     *
     * @param $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param mixed $notifiable
     *
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //dd($notifiable);
        $reset_password_route = route('frontend.auth.password.reset.form', $this->token);
        $email_input = [
            'text' => 'Password Reset',
            'subject' => $reset_password_route,
            'mail_to' => $notifiable['email'],
             'customer_name' => $notifiable['first_name'],
            'file' => 'null'
        ];
        
        $mailer = new RosemailerRepository;
        dd($email_input);
        $result = $mailer->send($notifiable['email'], $email_input);
        dd($result);
        return view('emails.reset-password', ['reset_password_url' => $reset_password_route]);
    }
}
