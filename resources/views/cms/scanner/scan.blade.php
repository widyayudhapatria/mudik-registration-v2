@extends('layouts.cms')

@section('title', 'Detail QR Code')
@section('page-title', 'Detail QR Code')

@push('styles')
    <style>
        .print-only {
            display: none;
        }

        @media print {

            .sidebar,
            .topbar,
            .modal,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 10px !important;
            }

            body {
                font-size: 12pt;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #bbb !important;
                margin-bottom: 12px !important;
                page-break-inside: avoid;
            }

            .card-header,
            .badge,
            .btn {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table td,
            .table th {
                border: 1px solid #999 !important;
                padding: 4px 8px !important;
            }

            .print-only {
                display: block !important;
            }
        }
    </style>
@endpush

@section('content')
    <div style="background:white; border-radius:16px; padding:30px;">

        {{-- Header khusus print --}}
        <div class="print-only mb-4 pb-3" style="text-align:center; border-bottom: 2px solid #333;">
            <h3 style="margin:0; font-weight:bold;">TIKET MUDIK LEBARAN 2026</h3>
            <p style="margin:4px 0 0; font-size: 11pt; color:#555;">Bukti Pendaftaran Resmi</p>
        </div>

        {{-- Back Button & Print Button --}}
        <div class="mb-4 d-flex gap-2 no-print">
            <a href="{{ route('cms.scanner.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Scanner
            </a>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Tiket
            </button>
        </div>

        {{-- QR Status Badge --}}
        <div class="text-center mb-4">
            @if ($qrCode->isScanned())
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

        {{-- QR Code Display --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header" style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); color: white;">
                <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>QR Code</h5>
            </div>
            <div class="card-body text-center">
                {!! QrCode::size(250)->generate($qrCode->qr_data) !!}
                <p class="text-muted mt-3 mb-0 small">Token: {{ substr($token, 0, 32) }}...</p>
            </div>
        </div>

        {{-- Registration Info --}}
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
                            {{ ucfirst($qrCode->registration->getStatus()) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Participants --}}
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
                            @foreach ($qrCode->registration->participants as $index => $participant)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $participant->full_name }}</td>
                                    <td>{{ $participant->nik_kia }}</td>
                                    <td>{{ $participant->birth_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($participant->is_child_under_4)
                                            <span class="badge bg-info">Anak &lt; 4 tahun</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Seat Allocations (hanya tampil jika sudah di-scan) -->
        @if ($qrCode->isScanned() && $qrCode->registration->seatAllocations->isNotEmpty())
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-grid-3x3-gap me-2"></i>
                        Alokasi Kursi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Peserta</th>
                                    <th>Kode Kursi</th>
                                    <th>Bus</th>
                                    <th>Nomor Kursi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($qrCode->registration->seatAllocations as $seat)
                                    <tr>
                                        <td>{{ $seat->participant->full_name }}</td>
                                        <td>
                                            @if ($seat->isNoSeat())
                                                <span class="badge bg-info">Dipangku</span>
                                            @else
                                                <span class="badge bg-success">{{ $seat->seat_code }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $seat->bus_name }}</td>
                                        <td>{{ $seat->seat_label }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

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
                    @if ($qrCode->scanned_at)
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

        {{-- Action Button --}}
        <div class="no-print">
            @if ($qrCode->canBeScanned())
                <div class="text-center">
                    <button type="button" class="btn btn-success btn-lg px-5 py-3" style="border-radius: 12px;"
                        onclick="confirmScan()">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Konfirmasi Scan QR Code</strong>
                    </button>
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

    </div>

    {{-- Success Modal --}}
    <div class="modal fade no-print" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Scan Berhasil!
                    </h5>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <i class="bi bi-check-circle" style="font-size: 80px; color: #4CAF50;"></i>
                    </div>
                    <h5 id="successName" class="mb-3"></h5>
                    <p id="successDetails" class="text-muted mb-0"></p>
                    <div id="successWarning" class="alert alert-warning mt-3" style="display:none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="warningText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('cms.scanner.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Scanner
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Error Modal --}}
    <div class="modal fade no-print" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle-fill me-2"></i>
                        Scan Gagal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <i class="bi bi-x-circle" style="font-size: 80px; color: #f44336;"></i>
                    </div>
                    <h5 id="errorMessage" class="text-danger mb-3"></h5>
                    <p id="errorDetails" class="text-muted mb-0"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="{{ route('cms.scanner.index') }}" class="btn btn-primary">Kembali ke Scanner</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const token = '{{ $token }}';
        const csrfToken = '{{ csrf_token() }}';

        async function confirmScan() {
            if (!confirm('⚠️ Yakin ingin men-scan QR Code ini?\n\nTindakan ini tidak dapat dibatalkan!')) {
                return;
            }

            // Show loading
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

            try {
                const response = await fetch('/cms/scanner/api/consume', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token_qr: token
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showSuccess(result.data);
                } else {
                    showError(result.message || 'Scan gagal', result.error_details || '');
                }
            } catch (error) {
                console.error('Scan error:', error);
                showError('Terjadi kesalahan saat melakukan scan', error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }

        function showSuccess(data) {
            document.getElementById('successName').textContent = data.registration.representative_name;
            document.getElementById('successDetails').textContent =
                `${data.registration.family_count} orang • KK: ${data.registration.kk_number}`;

            if (data.warnings && data.warnings.length > 0) {
                document.getElementById('warningText').textContent = data.warnings[0];
                document.getElementById('successWarning').style.display = 'block';
            }

            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        }

        function showError(message, details) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorDetails').textContent = details;

            const modal = new bootstrap.Modal(document.getElementById('errorModal'));
            modal.show();
        }
    </script>
@endpush
