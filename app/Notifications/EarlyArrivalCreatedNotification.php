<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
// use Illuminate\Notifications\Messages\MailMessage; // إذا تريد بريد

class EarlyArrivalCreatedNotification extends Notification
{
    use Queueable;

    public $earlyArrival;

    public function __construct($earlyArrival)
    {
        $this->earlyArrival = $earlyArrival;
    }

    public function via($notifiable)
    {
        return ['database']; // يمكن إضافة 'mail' أو قنوات أخرى حسب الحاجة
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage([
            'title' => 'نداء مبكر جديد',
            'message' => 'تم إضافة نداء مبكر للطالب ' . $this->earlyArrival->student->name_ar,
            'early_arrival_id' => $this->earlyArrival->id,
            'pickup_date' => $this->earlyArrival->pickup_date,
            'pickup_time' => $this->earlyArrival->pickup_time,
        ]);
    }

    /*
    // إذا تريد إرسال البريد، يمكنك تعديل الدالة toMail()
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('نداء مبكر جديد')
                    ->line('تم إضافة نداء مبكر للطالب ' . $this->earlyArrival->student->name_ar)
                    ->action('عرض النداء', url('/admin/early-arrivals/' . $this->earlyArrival->id));
    }
    */
}
