<?php

namespace App\Enums;

enum ErrorCode: string
{
    use EnumTraits;

    case InvalidEmail = 'INVALID_EMAIL';
    case EmailExists = 'EMAIL_EXISTS';
    case RateLimit = 'RATE_LIMIT';
    case ServerError = 'SERVER_ERROR';
    case LinkInvalid = 'LINK_INVALID';
    case LinkExpired = 'LINK_EXPIRED';
    case QuotaFull = 'QUOTA_FULL';
    case DuplicateKK = 'DUPLICATE_KK';
    case DuplicateNIK = 'DUPLICATE_NIK';
    case DuplicateKIA = 'DUPLICATE_KIA';
    case DuplicateKTPKIAParticipant = 'DUPLICATE_KTP_KIA_PARTICIPANT';
    case InvalidNIK = 'INVALID_NIK';
    case InvalidFile = 'INVALID_FILE';
    case InvalidQR = 'INVALID_QR';
    case AlreadyScanned = 'ALREADY_SCANNED';
    case NotValidDate = 'NOT_VALID_DATE';
    case ScanFailed = 'SCAN_FAILED';
    case QrNotFound = 'QR_NOT_FOUND';
    case QrInvalidDate = 'QR_INVALID_DATE';
    case QrAlreadyScanned = 'QR_ALREADY_SCANNED';
    case DailyQuotaNotSet = 'DAILY_QUOTA_NOT_SET';
    case DailyQuotaFull = 'DAILY_QUOTA_FULL';
    case DestinationQuotaFull = 'DESTINATION_QUOTA_FULL';
    case QuotaExceededGlobal = 'QUOTA_EXCEEDED_GLOBAL';
    case QuotaExceededDestination = 'QUOTA_EXCEEDED_DESTINATION';

    /**
     * Get error message for the code.
     */
    public function getMessage(): string
    {
        return match ($this) {
            self::InvalidEmail => "Format email tidak valid.\nMohon periksa kembali email Anda.",
            self::EmailExists => "Email sudah terdaftar dan data pendaftaran sedang/sudah diproses.\nGunakan email lain untuk melanjutkan pendaftaran.",
            self::RateLimit => "Terlalu banyak percobaan.\nMohon tunggu 10 menit sebelum mencoba lagi.",
            self::ServerError => "Terjadi kesalahan pada server.\nMohon coba beberapa saat lagi.",
            self::LinkInvalid => "Link formulir pendaftaran yang Anda gunakan tidak valid atau sudah pernah digunakan.",
            self::LinkExpired => "Link pendaftaran sudah kadaluarsa.\nSilakan buat link pendaftaran baru melalui halaman utama.",
            self::QuotaFull => "Kuota pendaftaran hari ini sudah penuh.\nSilahkan coba lagi besok menggunakan link yang sama jika masih tersedia dan link tidak kadaluarsa.",
            self::DuplicateKK => "Nomor Kartu Keluarga (KK) sudah terdaftar.",
            self::DuplicateNIK => "Nomor KTP yang anda gunakan sudah terdaftar.",
            self::DuplicateKIA => "Nomor KIA yang anda gunakan sudah terdaftar.",
            self::DuplicateKTPKIAParticipant => "Nomor KTP/KIA peserta yang anda gunakan sudah terdaftar.",
            self::InvalidNIK => "Format NIK tidak valid.\nNIK harus 16 digit angka.",
            self::InvalidFile => "File yang diupload tidak valid.\nGunakan format JPG, PNG, atau PDF dengan ukuran maksimal 2MB.",
            self::InvalidQR => "QR Code tidak valid.\nMohon periksa kembali QR Code Anda.",
            self::AlreadyScanned => "QR Code sudah pernah digunakan\ndan tidak dapat digunakan lagi.",
            self::NotValidDate => "QR Code belum atau sudah tidak berlaku.\nQR Code hanya berlaku pada tanggal yang ditentukan.",
            self::ScanFailed => "Scan gagal.\nMohon coba lagi atau hubungi petugas.",
            self::QrNotFound => "QR Code tidak ditemukan.\nPastikan QR Code benar dan coba lagi.",
            self::QrInvalidDate => "QR Code tidak valid untuk hari ini.\nPastikan QR Code digunakan pada tanggal yang sesuai.",
            self::QrAlreadyScanned => "QR Code sudah pernah dipindai.\nTidak dapat digunakan lagi.",
            self::DailyQuotaNotSet => "Kuota harian untuk tujuan ini belum diatur.\nSilakan kembali lagi nanti atau hubungi admin.",
            self::DailyQuotaFull => "Kuota harian untuk tujuan ini sudah penuh.\nSilakan coba tujuan lain atau kembali besok.",
            self::DestinationQuotaFull => "Kuota untuk tujuan ini sudah habis.\nSilakan pilih tujuan lain.",
            self::QuotaExceededGlobal => "Total alokasi kuota destinasi melebihi kuota global.",
            self::QuotaExceededDestination => "Total kuota harian melebihi kuota destinasi.",
        };
    }
}
