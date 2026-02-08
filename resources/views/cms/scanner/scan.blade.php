@extends('layouts.cms')

@section('title', 'Detail QR Code')
@section('page-title', 'Detail QR Code')

@section('content')
<div style="background:white; border-radius:16px; padding:30px;">
    
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('cms.scanner.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Scanner
        </a>
    </div>

    <!-- QR Status Badge -->
    <div class="text-center mb-4">
        @if($qrCode->isScanned())
            <span class="badge bg-warning text-dark fs-5 px-4 py-3">
                <i class="bi bi-check-circle-fill me-2"></i>Sudah Di-scan
            </span>
        @elseif($qrCode->isExpired())
            <span class="badge bg-danger fs-5 px-4 py-3">
                <i class="bi bi-x-circle-fill me-2"></i>Expired
            </span>
        @elseif($qrCode->canBeScanned())
            <span class="badge bg-success fs-5 px-4 py-3">
                <i class="bi bi-shield-check-fill me-2"></i>Valid - Siap Di-scan
            </span>
        @else
            <span class="badge bg-secondary fs-5 px-4 py-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Tidak Valid
            </span>
        @endif
    </div>

    <!-- QR Code Display -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header" style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>QR Code</h5>
        </div>
        <div class="card-body text-center">
            {!! QrCode::size(250)->generate($token) !!}
            <p class="text-muted mt-3 mb-0 small">Token: {{ substr($token, 0, 32) }}...</p>
        </div>
    </div>

    <!-- Registration Info -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Informasi Pendaftar</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Nama Perwakilan:</strong><br>
                    {{ $qrCode->registration->representative_name }}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>NIK:</strong><br>
                    {{ $qrCode->registration->representative_nik }}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>No. KK:</strong><br>
                    {{ $qrCode->registration->kk_number }}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Status:</strong><br>
                    <span class="badge bg-{{ $qrCode->registration->status === 'approved' ? 'success' : 'warning' }}">
                        {{ ucfirst($qrCode->registration->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Participants -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-people me-2"></i>
                Daftar Peserta ({{ $qrCode->registration->participants->count() }} orang)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Lengkap</th>
                            <th>NIK/KIA</th>
                            <th>Tanggal Lahir</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($qrCode->registration->participants as $index => $participant)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $participant->full_name }}</td>
                            <td>{{ $participant->nik_kia }}</td>
                            <td>{{ $participant->birth_date->format('d/m/Y') }}</td>
                            <td>
                                @if($participant->is_child_under_4)
                                    <span class="badge bg-info">Anak < 4 tahun</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- QR Info -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Informasi QR Code</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Berlaku Dari:</strong><br>
                    {{ $qrCode->valid_from->format('d/m/Y H:i') }} WIB
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Berlaku Sampai:</strong><br>
                    {{ $qrCode->valid_until->format('d/m/Y H:i') }} WIB
                </div>
                @if($qrCode->scanned_at)
                <div class="col-md-6 mb-3">
                    <strong>Di-scan Pada:</strong><br>
                    {{ $qrCode->scanned_at->format('d/m/Y H:i') }} WIB
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Di-scan Oleh:</strong><br>
                    {{ $qrCode->scannedBy->name ?? 'N/A' }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Button -->
    @if($qrCode->canBeScanned())
    <div class="text-center">
        <form action="{{ route('cms.api.scan.consume') }}" method="POST" id="scanForm">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <button type="submit" class="btn btn-success btn-lg px-5 py-3" style="border-radius: 12px;">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Konfirmasi Scan QR Code</strong>
            </button>
        </form>
        <p class="text-muted mt-3 small">
            <i class="bi bi-info-circle me-1"></i>
            Pastikan semua data sudah benar sebelum konfirmasi
        </p>
    </div>
    @elseif($qrCode->isScanned())
    <div class="alert alert-warning text-center">
        <i class="bi bi-exclamation-triangle me-2"></i>
        QR Code ini sudah pernah di-scan pada {{ $qrCode->scanned_at->format('d/m/Y H:i') }} WIB
        oleh {{ $qrCode->scannedBy->name ?? 'Admin' }}
    </div>
    @else
    <div class="alert alert-danger text-center">
        <i class="bi bi-x-circle me-2"></i>
        QR Code ini tidak dapat di-scan. {{ $qrCode->getValidationMessage() }}
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.getElementById('scanForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (confirm('⚠️ Yakin ingin men-scan QR Code ini?\n\nTindakan ini tidak dapat dibatalkan!')) {
        this.submit();
    }
});
</script>
@endpush