<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Services\PdfRenderer;
use App\Contracts\ReportManager;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FinanceMonthlyReportNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data = [
            'sections' => app(ReportManager::class)->generate(),
            'currency' => config('hisabi.currency'),
            'range' => now()->format('M Y')
        ];

        return (new MailMessage)
                    ->subject('Finance report for ' . $data['month'])
                    ->line('Please find the finance report attachment for ' . $data['month'])
                    ->attachData(PdfRenderer::render('report', $data), 'hisabi-report.pdf');
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
            //
        ];
    }
}
