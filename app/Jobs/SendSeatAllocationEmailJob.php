<?php

namespace App\Jobs;

use App\Mail\SeatAllocationMail;
use App\Models\EmailLog;
use App\Models\Registration;
use App\Models\SeatAllocation;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSeatAllocationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Registration $registration;
    public array $seatAllocationIds;
    public int $tries = 3;
    public int $timeout = 60;
    public $backoff = [60, 300, 600];

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new RateLimited('email-queue')];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(Registration $registration, Collection $seatAllocations)
    {
        $this->registration = $registration;
        $this->seatAllocationIds = $seatAllocations->pluck('id')->toArray();
    }

    /**
     * Execute the job.
     */
    public function handle(QrCodeService $qrCodeService): void
    {
        // Load fresh seat allocations from database with destination relation
        $seatAllocations = SeatAllocation::with('destination')
            ->whereIn('id', $this->seatAllocationIds)
            ->get();

        $email = $this->registration->formLink->email;
        $subject = '🎫 E-Ticket Mudik Gratis 2026 - ' . $this->registration->destination->name;

        // Create email log
        $emailLog = EmailLog::logSeatAllocationEmail(
            $this->registration->form_link_id,
            $email,
            $subject
        );

        try {
            // Send email with QR code service
            Mail::to($email)->send(new SeatAllocationMail(
                $this->registration,
                $seatAllocations,
                $qrCodeService
            ));

            // Mark as sent
            $emailLog->markAsSent();

            Log::info('Seat allocation email sent successfully', [
                'registration_id' => $this->registration->id,
                'email' => $email,
                'seats_count' => $seatAllocations->filter(fn($s) => !$s->isNoSeat())->count(),
            ]);
        } catch (Throwable $e) {
            $emailLog->markAsFailed($e->getMessage());

            Log::error('Failed to send seat allocation email', [
                'registration_id' => $this->registration->id,
                'email' => $email,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }
}
