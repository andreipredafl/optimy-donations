<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $donation;

    /**
     * Create a new notification instance.
     */
    public function __construct($donation)
    {
        $this->donation = $donation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Thank You for Your Donation!')
            ->greeting('Hello!')
            ->line('Thank you for your generous donation.')
            ->line('Your donation of $' . number_format($this->donation->amount, 2) . ' has been successfully processed.')
            ->line('We truly appreciate your support!')
            ->line('Transaction ID: ' . $this->donation->transaction_id)
            ->line('Date: ' . $this->donation->created_at->format('F j, Y'))
            ->line('Thank you for making a difference!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
