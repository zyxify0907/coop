<?php

namespace App\Notifications;

use App\Services\AhliImportResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AhliImportCompleted extends Notification
{
    use Queueable;

    public function __construct(private readonly AhliImportResult $result) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Import Data Ahli Selesai')
            ->line("Berjaya mengimport {$this->result->importedCount} rekod. Gagal: {$this->result->failedCount} rekod.")
            ->line('Sila semak laporan ralat pada halaman Senarai Ahli jika ada rekod gagal.');
    }
}
