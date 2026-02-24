<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BypassRegistrationValidator
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $seenEmails = [];

    /**
     * Validate Excel data before import
     */
    public function validate(array $rows): array
    {
        $this->errors = [];
        $this->warnings = [];
        $this->seenEmails = [];

        if (empty($rows)) {
            $this->errors[] = ['type' => 'no_data', 'message' => 'File Excel kosong atau tidak ada data.'];
            return $this->getResult();
        }

        // Phase 1: Data Type & Format Validation
        $this->validateDataTypes($rows);

        if (!empty($this->errors)) {
            return $this->getResult();
        }

        // Phase 2: Duplicate & Uniqueness Validation
        $this->validateEmailUniqueness($rows);
        $this->validateEmailWithinBatch($rows);
        $this->validateRepresentativeNik($rows);
        $this->validateParticipantNik($rows);

        // Phase 3: Business Logic Validation
        $this->validateFamilyCount($rows);
        $this->validateBirthDates($rows);
        $this->validateAgeConsistency($rows);

        return $this->getResult();
    }

    /**
     * Phase 1: Validate data types and formats
     */
    protected function validateDataTypes(array &$rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $rn = $rowIndex + 1;

            // Text fields
            if (empty($row['representative_name']) || !is_string($row['representative_name'])) {
                $this->errors[] = ['row' => $rn, 'column' => 'representative_name', 'message' => 'Nama representative harus teks dan tidak boleh kosong'];
            }

            if (empty($row['participant_full_name']) || !is_string($row['participant_full_name'])) {
                $this->errors[] = ['row' => $rn, 'column' => 'participant_full_name', 'message' => 'Nama peserta harus teks dan tidak boleh kosong'];
            }

            if (empty($row['kk_number']) || strlen(str_replace('-', '', (string)$row['kk_number'])) !== 16) {
                $this->errors[] = ['row' => $rn, 'column' => 'kk_number', 'message' => 'KK Number harus 16 digit'];
            }

            // Email validation
            $email = $row['representative_email'] ?? '';
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = ['row' => $rn, 'column' => 'representative_email', 'message' => 'Email representative harus valid'];
            }

            // NIK validation
            $repNik = (string)($row['representative_nik'] ?? '');
            if (strlen($repNik) !== 16 || !ctype_digit($repNik)) {
                $this->errors[] = ['row' => $rn, 'column' => 'representative_nik', 'message' => 'NIK representative harus 16 digit'];
            }

            $partNik = (string)($row['participant_nik'] ?? '');
            if (strlen($partNik) !== 16 || !ctype_digit($partNik)) {
                $this->errors[] = ['row' => $rn, 'column' => 'participant_nik', 'message' => 'NIK peserta harus 16 digit'];
            }

            // Birth date validation
            if (!$this->isValidDate($row['representative_birth_date'] ?? '')) {
                $this->errors[] = ['row' => $rn, 'column' => 'representative_birth_date', 'message' => 'Tanggal lahir representative format harus YYYY-MM-DD'];
            }

            if (!$this->isValidDate($row['participant_birth_date'] ?? '')) {
                $this->errors[] = ['row' => $rn, 'column' => 'participant_birth_date', 'message' => 'Tanggal lahir peserta format harus YYYY-MM-DD'];
            }

            // Family count validation
            $familyCount = (int)($row['family_count'] ?? 0);
            if ($familyCount < 1 || $familyCount > 10) {
                $this->errors[] = ['row' => $rn, 'column' => 'family_count', 'message' => 'Family count harus angka antara 1-10'];
            }

            // is_child_under_4 validation
            $isChild = $row['is_child_under_4'] ?? '';
            if (!in_array(strtolower($isChild), ['true', 'false', '1', '0', 'yes', 'no'])) {
                $this->errors[] = ['row' => $rn, 'column' => 'is_child_under_4', 'message' => 'is_child_under_4 harus true/false atau 1/0'];
            }
        }
    }

    /**
     * Phase 2a: Check email uniqueness globally
     */
    protected function validateEmailUniqueness(array $rows): void
    {
        $emails = array_column($rows, 'representative_email');
        $emails = array_filter($emails);

        if (empty($emails)) {
            return;
        }

        // normalize to lowercase to avoid case-sensitivity issues
        $normalized = array_map('mb_strtolower', $emails);

        // Check against database
        $existingEmails = DB::table('form_links')
            ->whereIn(DB::raw('LOWER(email)'), $normalized)
            ->whereIn('status', ['submitted', 'approved', 'pending'])
            ->pluck('email')
            ->map(fn($e) => mb_strtolower(trim($e)))
            ->toArray();

        foreach ($rows as $rowIndex => $row) {
            $email = $row['representative_email'] ?? '';
            $email = mb_strtolower(trim($row['representative_email'] ?? ''));
            if ($email && in_array($email, $existingEmails, true)) {
                $this->errors[] = [
                    'row' => $rowIndex + 1,
                    'email' => $row['representative_email'] ?? '',
                    'type' => 'duplicate_email_global',
                    'message' => "Email '{$row['representative_email']}' sudah terdaftar di sistem dengan status submitted/approved."
                ];
            }
        }
    }

    /**
     * Phase 2b: Check email uniqueness within batch
     */
    protected function validateEmailWithinBatch(array $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $email = mb_strtolower(trim($row['representative_email'] ?? ''));
            if ($email === '') {
                continue;
            }

            $repNik = (string)($row['representative_nik'] ?? '');

            if (isset($this->seenEmails[$email])) {
                // Same email but different representative
                if ($this->seenEmails[$email] !== $repNik) {
                    $this->errors[] = [
                        'row' => $rowIndex + 1,
                        'email' => $email,
                        'type' => 'duplicate_email_batch',
                        'message' => "Email '{$email}' muncul lebih dari sekali di file dengan representative berbeda."
                    ];
                }
            }
            $this->seenEmails[$email] = $repNik;
        }
    }

    /**
     * Phase 2c: Check representative NIK per destination
     */
    protected function validateRepresentativeNik(array $rows): void
    {
        $niks = array_column($rows, 'representative_nik');
        $niks = array_filter($niks);

        $existingNiks = DB::table('registrations')
            ->whereIn('representative_nik', $niks)
            ->pluck('representative_nik')
            ->toArray();

        foreach ($rows as $rowIndex => $row) {
            $nik = (string)($row['representative_nik'] ?? '');
            if (in_array($nik, $existingNiks)) {
                $this->warnings[] = [
                    'row' => $rowIndex + 1,
                    'nik' => $nik,
                    'type' => 'duplicate_rep_nik',
                    'message' => "NIK representative '{$nik}' sudah pernah terdaftar."
                ];
            }
        }
    }

    /**
     * Phase 2d: Check participant NIK per destination
     */
    protected function validateParticipantNik(array $rows): void
    {
        $niks = array_column($rows, 'participant_nik');
        $niks = array_filter($niks);

        $existingNiks = DB::table('participants')
            ->whereIn('participants.nik_kia', $niks)
            ->pluck('participants.nik_kia')
            ->toArray();

        foreach ($rows as $rowIndex => $row) {
            $nik = (string)($row['participant_nik'] ?? '');
            if (in_array($nik, $existingNiks)) {
                $this->errors[] = [
                    'row' => $rowIndex + 1,
                    'nik' => $nik,
                    'type' => 'duplicate_participant_nik',
                    'message' => "NIK peserta '{$nik}' sudah terdaftar."
                ];
            }
        }
    }

    /**
     * Phase 3a: Validate family count matches participant rows
     */
    protected function validateFamilyCount(array &$rows): void
    {
        $grouped = $this->groupByRepresentativeNik($rows);

        foreach ($grouped as $repNik => $group) {
            $expectedCount = null;
            foreach ($group as $rowIndex => $row) {
                $familyCount = (int)($row['family_count'] ?? 0);
                if ($expectedCount === null) {
                    $expectedCount = $familyCount;
                } elseif ($familyCount !== $expectedCount) {
                    $this->errors[] = [
                        'row' => $rowIndex + 1,
                        'type' => 'inconsistent_family_count',
                        'message' => "Family count harus konsisten untuk representative '{$repNik}'"
                    ];
                }
            }

            $actualCount = count($group);
            if ($actualCount !== $expectedCount) {
                $this->errors[] = [
                    'nik' => $repNik,
                    'type' => 'family_count_mismatch',
                    'expected' => $expectedCount,
                    'actual' => $actualCount,
                    'message' => "Representative '{$repNik}': family_count ({$expectedCount}) ≠ peserta yang ada ({$actualCount})"
                ];
            }
        }
    }

    /**
     * Phase 3b: Validate birth dates
     */
    protected function validateBirthDates(array $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $rn = $rowIndex + 1;

            $repDob = trim((string)($row['representative_birth_date'] ?? ''));
            if ($repDob) {
                $repTimestamp = $this->parseToTimestamp($repDob);
                if ($repTimestamp && $repTimestamp > now()->timestamp) {
                    $this->errors[] = [
                        'row' => $rn,
                        'column' => 'representative_birth_date',
                        'message' => 'Tanggal lahir representative tidak boleh di masa depan'
                    ];
                }
            }

            $partDob = trim((string)($row['participant_birth_date'] ?? ''));
            if ($partDob) {
                $partTimestamp = $this->parseToTimestamp($partDob);
                if ($partTimestamp && $partTimestamp > now()->timestamp) {
                    $this->errors[] = [
                        'row' => $rn,
                        'column' => 'participant_birth_date',
                        'message' => 'Tanggal lahir peserta tidak boleh di masa depan'
                    ];
                }
            }
        }
    }

    /**
     * Phase 3c: Validate age & is_child_under_4 consistency
     */
    protected function validateAgeConsistency(array $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $rn = $rowIndex + 1;
            $dob = $row['participant_birth_date'] ?? '';
            $isChild = strtolower($row['is_child_under_4'] ?? 'false');

            if ($dob) {
                $age = $this->calculateAge($dob);
                $isUnder4 = in_array($isChild, ['true', '1', 'yes']);

                if ($age < 4 && !$isUnder4) {
                    $this->warnings[] = [
                        'row' => $rn,
                        'message' => "Peserta berusia {$age} tahun, tapi is_child_under_4 = false"
                    ];
                }
                if ($age >= 4 && $isUnder4) {
                    $this->warnings[] = [
                        'row' => $rn,
                        'message' => "Peserta berusia {$age} tahun, tapi is_child_under_4 = true"
                    ];
                }
            }
        }
    }

    /**
     * Helper: Check if string is valid date
     */
    protected function isValidDate(string $date): bool
    {
        //documentation
        if (empty($date)) {
            return false;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Helper: Parse date string or Excel serial to timestamp
     */
    protected function parseToTimestamp(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // If numeric (Excel serial), convert
        if (is_numeric($value) && strlen($value) >= 4 && strlen($value) <= 6) {
            try {
                if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);
                    if ($dt instanceof \DateTimeInterface) {
                        return $dt->getTimestamp();
                    }
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Try to parse string date
        $ts = @strtotime($value);
        return ($ts !== false && $ts > 0) ? $ts : null;
    }

    /**
     * Helper: Calculate age from DOB
     */
    protected function calculateAge(string $dob): int
    {
        $date = new \DateTime($dob);
        $today = new \DateTime();
        return $today->diff($date)->y;
    }

    /**
     * Helper: Group rows by representative NIK
     */
    protected function groupByRepresentativeNik(array $rows): array
    {
        $grouped = [];
        foreach ($rows as $index => $row) {
            $nik = (string)($row['representative_nik'] ?? '');
            if (!isset($grouped[$nik])) {
                $grouped[$nik] = [];
            }
            $grouped[$nik][$index] = $row;
        }
        return $grouped;
    }

    /**
     * Get validation result
     */
    protected function getResult(): array
    {
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'error_count' => count($this->errors),
            'warning_count' => count($this->warnings),
        ];
    }
}
