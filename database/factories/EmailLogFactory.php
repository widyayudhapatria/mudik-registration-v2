<?php

namespace Database\Factories;

use App\Enums\EmailType;
use App\Models\EmailLog;
use App\Models\FormLink;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailLogFactory extends Factory
{
    protected $model = EmailLog::class;

    public function definition(): array
    {
        return [
            'form_link_id' => FormLink::factory(),
            'recipient_email' => fake()->safeEmail(),
            'subject' => fake()->sentence(),
            'email_type' => EmailType::FormLink->value,
            'status' => 'sent',
            'sent_at' => Carbon::now(),
            'failed_at' => null,
            'retry_count' => 0,
            'error_message' => null,
        ];
    }

    /**
     * Form link email type.
     */
    public function formLink(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_type' => EmailType::FormLink->value,
            'subject' => 'Link Pendaftaran Mudik Gratis Lebaran 2026',
        ]);
    }

    /**
     * QR code email type.
     */
    public function qrCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_type' => EmailType::QrCode->value,
            'subject' => 'QR Code Tiket Mudik Gratis Lebaran 2026',
        ]);
    }

    /**
     * Rejection email type.
     */
    public function rejection(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_type' => EmailType::Rejection->value,
            'subject' => 'Pemberitahuan Pendaftaran Mudik Gratis Lebaran 2026',
        ]);
    }

    /**
     * Sent successfully.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => Carbon::now(),
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    /**
     * Pending (not sent yet).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'sent_at' => null,
            'failed_at' => null,
        ]);
    }

    /**
     * Failed to send.
     */
    public function failed(string $errorMessage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'sent_at' => null,
            'failed_at' => Carbon::now(),
            'error_message' => $errorMessage ?? 'Failed to send email',
            'retry_count' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * With specific retry count.
     */
    public function withRetryCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'retry_count' => $count,
        ]);
    }

    /**
     * Max retry reached.
     */
    public function maxRetry(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'retry_count' => 3,
            'failed_at' => Carbon::now(),
            'error_message' => 'Max retry attempts reached',
        ]);
    }
}