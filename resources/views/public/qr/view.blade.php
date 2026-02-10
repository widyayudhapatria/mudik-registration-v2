@extends('layouts.app')

@section('title', 'QR Code Tiket Mudik - Mudik Gratis Lebaran 2026')

@push('styles')
<style>
    .qr-header {
        background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
        padding: 40px 20px;
        color: white;
        text-align: center;
    }
    
    .qr-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        max-width: 600px;
        margin: -60px auto 40px;
        position: relative;
        z-index: 10;
    }
    
    .qr-code-container {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        text-align: center;
        margin-bottom: 30px;
    }
    
    .qr-code-img {
        max-width: 300px;
        width: 100%;
        height: auto;
        margin: 0 auto;
        display: block;
    }
    
    .status-badge {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }
    
    .status-valid {
        background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
        color: white;
    }
    
    .status-scanned {
        background: linear-gradient(135deg, #F57C00 0%, #E65100 100%);
        color: white;
    }
    
    .status-expired {
        background: linear-gradient(135deg, #D32F2F 0%, #B71C1C 100%);
        color: white;
    }
    
    .info-section {
        background: #f8f9fa;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 20px;
    }
    
    .info-title {
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 15px;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: var(--gray-text);
        font-weight: 500;
    }
    
    .info-value {
        font-weight: 600;
        color: var(--dark-text);
        text-align: right;
    }
    
    .participant-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .participant-item {
        background: white;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 10px;
        box-shadow: var(--shadow-sm);
    }
    
    .important-note {
        background: linear-gradient(135deg, #FFF3E0 0%, #FFE0B2 100%);
        border-left: 4px solid var(--secondary-color);
        padding: 20px;
        border-radius: 12px;
        margin-top: 30px;
    }
    
    .note-title {
        font-weight: 700;
        color: var(--secondary-dark);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    @media (max-width: 768px) {
        .qr-card {
            padding: 25px 20px;
            margin: -40px 15px 30px;
        }
        
        .qr-code-container {
            padding: 20px;
        }
    }

    @media (max-width: 768px) {
        .qr-header {
            padding: 30px 16px;
        }
        
        .qr-card {
            padding: 25px 20px;
            margin: -40px 15px 30px;
        }
        
        .qr-code-container {
            padding: 20px;
        }
        
        .qr-code-img {
            max-width: 250px;
        }
        
        .status-badge {
            font-size: 0.95rem;
            padding: 10px 20px;
        }
        
        .info-section {
            padding: 20px;
        }
        
        .info-title {
            font-size: 1.1rem;
        }
        
        .info-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            padding: 10px 0;
        }
        
        .info-value {
            text-align: left;
        }
        
        .participant-item {
            padding: 12px;
        }
        
        .important-note {
            padding: 16px;
        }
        
        .note-title {
            font-size: 1rem;
        }
        
        .important-note ul {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .qr-header h1 {
            font-size: 1.75rem;
        }
        
        .qr-card {
            padding: 20px 16px;
        }
        
        .qr-code-img {
            max-width: 220px;
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
        }
        
        .info-section {
            padding: 16px;
        }
        
        .participant-item .fw-semibold {
            font-size: 0.95rem;
        }
        
        .participant-item .small {
            font-size: 0.8rem;
        }
    }

</style>
@endpush

@section('content')
<div class="qr-header">
    <div class="container">
        <h1 class="heading-font mb-2" style="font-size: clamp(2rem, 5vw, 3rem);">
            TIKET MUDIK GRATIS 2026
        </h1>
        <p class="mb-0">QR Code Penukaran Tiket</p>
    </div>
</div>

<div class="container" style="padding-top: 80px; padding-bottom: 60px;">
    <div class="qr-card">
        <!-- Status Badge -->
        <div class="text-center">
            @if($qrCode->isScanned())
                <div class="status-badge status-scanned">
                    <i class="bi bi-check-circle-fill me-2"></i>Sudah Digunakan
                </div>
            @elseif($qrCode->isExpired())
                <div class="status-badge status-expired">
                    <i class="bi bi-x-circle-fill me-2"></i>Expired
                </div>
            @elseif($qrCode->isValid())
                <div class="status-badge status-valid">
                    <i class="bi bi-shield-check-fill me-2"></i>Valid
                </div>
            @else
                <div class="status-badge status-expired">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Tidak Valid
                </div>
            @endif
        </div>
        
        <!-- QR Code -->
        <div class="qr-code-container">
            {!! QrCode::size(300)->generate($qrCode->token_qr) !!}
            <p class="small text-muted mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Simpan atau screenshot QR Code ini
            </p>
        </div>
        
        <!-- Registration Info -->
        <div class="info-section">
            <div class="info-title">
                <i class="bi bi-person-badge-fill"></i>
                Informasi Perwakilan
            </div>
            <div class="info-item">
                <span class="info-label">Nama:</span>
                <span class="info-value">{{ $registration->representative_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">NIK:</span>
                <span class="info-value">{{ $registration->representative_nik }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Nomor KK:</span>
                <span class="info-value">{{ $registration->kk_number }}</span>
            </div>
        </div>
        
        <!-- Participants -->
        <div class="info-section">
            <div class="info-title">
                <i class="bi bi-people-fill"></i>
                Daftar Peserta Mudik ({{ $participants->count() }} orang)
            </div>
            <ul class="participant-list">
                @foreach($participants as $index => $participant)
                <li class="participant-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $index + 1 }}. {{ $participant->full_name }}</div>
                            <div class="small text-muted">NIK/KIA: {{ $participant->nik_kia }}</div>
                            <div class="small text-muted">
                                Lahir: {{ $participant->birth_date->format('d/m/Y') }}
                                @if($participant->is_child_under_4)
                                    <span class="badge bg-warning text-dark ms-2">Anak < 4 tahun</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        
        <!-- QR Info -->
        <div class="info-section">
            <div class="info-title">
                <i class="bi bi-qr-code"></i>
                Informasi QR Code
            </div>
            <div class="info-item">
                <span class="info-label">Berlaku Dari:</span>
                <span class="info-value">{{ $qrCode->valid_from->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Berlaku Sampai:</span>
                <span class="info-value">{{ $qrCode->valid_until->format('d/m/Y H:i') }}</span>
            </div>
            @if($qrCode->scanned_at)
            <div class="info-item">
                <span class="info-label">Di-scan Pada:</span>
                <span class="info-value">{{ $qrCode->scanned_at->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>
        
        <!-- Important Notes -->
        <div class="important-note">
            <div class="note-title">
                <i class="bi bi-exclamation-triangle-fill"></i>
                INFORMASI PENTING
            </div>
            <ul class="mb-0">
                <li class="mb-2">
                    <strong>QR Code One-Time:</strong> QR Code hanya dapat digunakan sekali untuk penukaran tiket
                </li>
                <li class="mb-2">
                    <strong>Dokumen yang Harus Dibawa:</strong>
                    <ul class="mt-2">
                        <li>QR Code ini (cetak atau screenshot)</li>
                        <li>KTP Asli perwakilan</li>
                        <li>Kartu Keluarga (KK) Asli</li>
                    </ul>
                </li>
                @if($registration->has_child_under_4)
                <li class="mb-2">
                    <strong>Anak Dibawah 4 Tahun:</strong> Wajib dipangku selama perjalanan
                </li>
                @endif
                <li class="mb-0">
                    <strong>Datang Tepat Waktu:</strong> Keterlambatan dapat menyebabkan pembatalan
                </li>
            </ul>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-grid gap-2 mt-4">
            <button class="btn btn-primary btn-lg" onclick="window.print()" style="border-radius: 12px;">
                <i class="bi bi-printer-fill me-2"></i>Cetak QR Code
            </button>
            <a href="{{ route('public.landing') }}" class="btn btn-outline-primary btn-lg" style="border-radius: 12px;">
                <i class="bi bi-house-fill me-2"></i>Ke Beranda
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .qr-header,
        .btn,
        .important-note {
            display: none !important;
        }
        
        .qr-card {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 20px !important;
        }
        
        body {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
@endpush
@endsection