<?php

namespace App\Notifications;

use App\Class_Public\DataInNotifiy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $header,$body,$type,$created_at,$data;
    public $temp;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($header,$type,$body,$created_at,DataInNotifiy $data = null)
    {
        $this->header = $header;
        $this->type = $type;
        $this->body = $body;
        $this->created_at = $created_at;
        $this->data = $data;
        $this->temp = is_null($this->data) ? [] : $this->data->GetAllData();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast','database'];
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
    public function toArray($notifiable):array
    {
        return [
            "Notify_type" => $this->type,
            "header"=>$this->header,
            "body" => $this->body,
            "created_at" => $this->created_at,
            "data" => $this->temp
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            "Notify_type" => $this->type,
            "header"=>$this->header,
            "body" => $this->body,
            "created_at" => $this->created_at,
            "request_next" => $this->temp
        ]);
    }
    //name Event : Illuminate\Notifications\Events\BroadcastNotificationCreated
}
