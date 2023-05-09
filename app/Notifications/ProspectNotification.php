<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\prospect\Prospect;

class TicketNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $message;

    protected $prospect;

    public function __construct(Prospect $prospect)
    {
        // $this->reference = $reference;
        $this->prospect = $prospect;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            // 'reference' => $this->reference

            'data' => [
                'title' => 'Reminder to call Prospect',
                'data' => 'Prospect - ' . $this->prospect->contact_person . 'From '. $this->prospect->company,
                'background' =>  $this->prospect->note,
                
            ],

        ];
    }
}
