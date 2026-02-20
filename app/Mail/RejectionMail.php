<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $rejectionReason;
    public string $websiteUrl;
    public array $mudikConfig;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Registration $registration,
        array $mudikConfig = []
    ) {
        $this->websiteUrl = config('app.url', 'http://localhost:8000');
        $this->rejectionReason = $this->registration->rejection_reason ?? 'Tidak ada alasan yang diberikan.';
        $this->mudikConfig = $mudikConfig ?: config('mudik');
    }


    public function build()
    {
        return $this->subject('Pemberitahuan Penolakan - ' . config('app.name'))
            ->view('emails.rejection-v2')
            ->with([
                'registration' => $this->registration,
                'rejectionReason' => $this->rejectionReason,
                'websiteUrl' => $this->websiteUrl,
                'mudikConfig' => $this->mudikConfig,
            ]);
    }
}
