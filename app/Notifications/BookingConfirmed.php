<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingConfirmed extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Booking Confirmed - ' . $this->booking->ticket->event->title)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Your booking has been confirmed.')
                    ->line('Event: ' . $this->booking->ticket->event->title)
                    ->line('Date: ' . $this->booking->ticket->event->date->format('F j, Y, g:i a'))
                    ->line('Location: ' . $this->booking->ticket->event->location)
                    ->line('Ticket Type: ' . $this->booking->ticket->type)
                    ->line('Quantity: ' . $this->booking->quantity)
                    ->line('Total Amount: $' . number_format($this->booking->total_amount, 2))
                    ->action('View Booking', url('/api/bookings/' . $this->booking->id))
                    ->line('Thank you for using our event booking system!');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'event_id' => $this->booking->ticket->event_id,
            'event_title' => $this->booking->ticket->event->title,
            'quantity' => $this->booking->quantity,
            'total_amount' => $this->booking->total_amount,
            'status' => $this->booking->status
        ];
    }
}