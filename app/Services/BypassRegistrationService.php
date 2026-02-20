<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Destination;
use App\Models\FormLink;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\RegistrationImport;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BypassRegistrationService
{
    /**
     * Import bypass registrations with atomic transaction
     */
    public function importRegistrations(
        array $registrationData,
        Destination $destination,
        Admin $admin,
        string $filename
    ): array {
        $successful = 0;
        $failed = 0;
        $errors = [];

        try {
            $successful = DB::transaction(function () use ($registrationData, $destination, &$errors) {
                $count = 0;

                foreach ($registrationData as $regData) {
                    try {
                        $this->createBypassRegistration($regData, $destination);
                        $count++;
                    } catch (\Exception $e) {
                        Log::warning('Failed to create bypass reg: ' . $e->getMessage(), [
                            'email' => $regData['representative_email'] ?? 'unknown'
                        ]);
                        $errors[] = [
                            'email' => $regData['representative_email'] ?? 'unknown',
                            'error' => $e->getMessage()
                        ];
                    }
                }

                return $count;
            }, attempts: 3); // Retry up to 3 times for lock conflicts

            $failed = count($errors);
            $totalParticipants = array_sum(array_map(fn($r) => count($r['participants']), $registrationData));

            // Log import
            $importLog = RegistrationImport::create([
                'admin_id' => $admin->id,
                'destination_id' => $destination->id,
                'filename' => $filename,
                'total_registrations' => count($registrationData),
                'total_participants' => $totalParticipants,
                'successful' => $successful,
                'failed' => $failed,
                'error_details' => !empty($errors) ? $errors : null,
                'status' => $failed === 0 ? 'completed' : 'partial',
                'notes' => $failed > 0 ? "Partial import: {$successful} succeeded, {$failed} failed" : null,
            ]);

            return [
                'success' => true,
                'import_id' => $importLog->id,
                'successful' => $successful,
                'failed' => $failed,
                'total' => count($registrationData),
                'errors' => $errors,
                'message' => $failed === 0
                    ? "{$successful} registrasi imported successfully!"
                    : "{$successful} registrasi berhasil, {$failed} gagal"
            ];
        } catch (QueryException $e) {
            Log::error('Database error during bypass import: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Terjadi kesalahan database: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected error during bypass import: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a single bypass registration with synthetic FormLink
     */
    protected function createBypassRegistration(array $regData, Destination $destination): Registration
    {
        $email = $regData['representative_email'];

        // ✅ CRITICAL: Check email hasn't been taken (pessimistic lock)
        // This handles race condition between validation & creation
        $existingFormLink = FormLink::lockForUpdate()
            ->where('email', $email)
            ->first();

        if ($existingFormLink) {
            throw new \Exception("Email '{$email}' sudah terdaftar (race condition detected)");
        }

        // ✅ CREATE: Synthetic FormLink
        $formLink = FormLink::create([
            'email' => $email,
            'status' => 'submitted',
            'used_at' => now(),
            'token' => 'bypass:' . Str::uuid(),
            'is_synthetic' => true,
            'expired_at' => now()->addDays(config('mudik.form_link_expiry_days', 3)),
        ]);

        // ✅ CREATE: Registration with bypass marker
        $registration = Registration::create([
            'form_link_id' => $formLink->id,
            'destination_id' => $destination->id,
            'representative_name' => $regData['representative_name'],
            'representative_nik' => $regData['representative_nik'],
            'representative_birth_date' => $regData['representative_birth_date'],
            'family_count' => $regData['family_count'],
            'kk_number' => $regData['kk_number'],
            'kk_document_path' => 'dummy/bypass.png', // Use dummy image for bypass
            'has_child_under_4' => $this->hasChildUnder4($regData['participants']),
            'is_bypass' => true,
        ]);

        // ✅ CREATE: Participants
        foreach ($regData['participants'] as $partData) {
            Participant::create([
                'registration_id' => $registration->id,
                'full_name' => $partData['full_name'],
                'nik_kia' => $partData['nik_kia'],
                'birth_date' => $partData['birth_date'],
                'is_child_under_4' => $partData['is_child_under_4'],
            ]);
        }

        return $registration;
    }

    /**
     * Determine if registration has any child under 4
     */
    protected function hasChildUnder4(array $participants): bool
    {
        foreach ($participants as $participant) {
            if ($participant['is_child_under_4'] === true) {
                return true;
            }
        }
        return false;
    }
}
