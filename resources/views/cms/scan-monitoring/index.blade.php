@extends('layouts.cms')

@section('title', 'Scan Monitoring - CMS Admin')
@section('page-title', 'Scan Monitoring')

@push('styles')
    <style>
        .pagination {
            margin: 0;
            flex-wrap: wrap;
            gap: 4px;
        }

        .pagination .page-item .page-link {
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 6px !important;
            line-height: 1.5;
        }

        .pagination .page-item .page-link svg {
            width: 14px;
            height: 14px;
        }

        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .table-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-scanned {
            background: #d4edda;
            color: #155724;
        }

        .badge-not-scanned {
            background: #fff3cd;
            color: #856404;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        /* Mobile Card View */
        .registration-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .registration-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .registration-card-body {
            display: grid;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .info-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .info-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #212529;
            text-align: right;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }

        .action-buttons .btn {
            flex: 1;
        }

        /* Desktop Table View */
        .desktop-table {
            display: block;
        }

        .mobile-cards {
            display: none;
        }

        /* Modal Styles */
        .modal-detail-row {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-detail-row:last-child {
            border-bottom: none;
        }

        .modal-detail-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 4px;
        }

        .modal-detail-value {
            color: #212529;
        }

        .participant-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .participant-name {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 8px;
            color: #212529;
        }

        .participant-info {
            display: grid;
            gap: 6px;
            font-size: 0.875rem;
        }

        .participant-info-row {
            display: flex;
            justify-content: space-between;
        }

        .participant-info-label {
            color: #6c757d;
        }

        .participant-info-value {
            font-weight: 500;
            color: #212529;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .filter-card {
                padding: 16px;
            }

            .table-card {
                padding: 16px;
            }

            .desktop-table {
                display: none;
            }

            .mobile-cards {
                display: block;
            }

            .status-badge {
                font-size: 0.75rem;
                padding: 4px 10px;
            }

            .registration-card-header h6 {
                font-size: 0.9rem;
                margin: 0;
            }

            .info-row {
                padding: 6px 0;
            }

            .info-label {
                font-size: 0.8rem;
            }

            .info-value {
                font-size: 0.85rem;
            }

            .action-buttons .btn {
                font-size: 0.85rem;
                padding: 8px 12px;
            }

            /* Filter responsive */
            .filter-card .row {
                gap: 12px !important;
            }

            .filter-card .col-md-3 {
                margin-bottom: 0;
            }

            .filter-card label {
                font-size: 0.85rem;
                margin-bottom: 6px;
            }

            .filter-card .form-select,
            .filter-card .form-control {
                font-size: 0.9rem;
            }

            .filter-card .btn {
                width: 100%;
                margin-bottom: 8px;
            }

            .filter-card .col-12 {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.25rem !important;
            }

            .table-card h5 {
                font-size: 1rem;
            }

            .registration-card {
                padding: 12px;
            }
        }
    </style>
@endpush

@section('content')
    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3 col-12">
                    <label class="form-label fw-semibold small">Tujuan</label>
                    <select name="destination_id" class="form-select">
                        <option value="">Semua Tujuan</option>
                        @foreach ($destinations as $destination)
                            <option value="{{ $destination->id }}"
                                {{ request('destination_id') == $destination->id ? 'selected' : '' }}>
                                {{ $destination->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-12">
                    <label class="form-label fw-semibold small">Status Scan</label>
                    <select name="scan_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="scanned" {{ request('scan_status') == 'scanned' ? 'selected' : '' }}>Sudah Scan
                        </option>
                        <option value="not_scanned" {{ request('scan_status') == 'not_scanned' ? 'selected' : '' }}>Belum
                            Scan</option>
                    </select>
                </div>

                <div class="col-md-2 col-12">
                    <label class="form-label fw-semibold small">Status Email</label>
                    <select name="email_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="sent" {{ request('email_status') == 'sent' ? 'selected' : '' }}>Terkirim</option>
                        <option value="failed" {{ request('email_status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                        <option value="pending" {{ request('email_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="not_sent" {{ request('email_status') == 'not_sent' ? 'selected' : '' }}>Belum Terkirim</option>
                    </select>
                </div>

                <div class="col-md-3 col-12">
                    <label class="form-label fw-semibold small">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama/Email/NIK/KK"
                        value="{{ request('search') }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Filter
                    </button>
                    <a href="{{ route('cms.scan-monitoring.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Alert container (placed between filters and data) -->
    <div id="scanMonitoringAlertContainer" class="mb-3">
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">
                Daftar Pendaftaran Approved ({{ $registrations->total() }})
            </h5>
        </div>

        <!-- Desktop Table View -->
        <div class="desktop-table">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Perwakilan</th>
                            <th>Tujuan</th>
                            <th>Jumlah</th>
                            <th>Status Scan</th>
                            <th>Status Email</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registrations as $registration)
                            <tr>
                                <td>
                                    <span class="fw-bold">#{{ $registration->id }}</span>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-semibold">
                                            {{ $registration->representative_name }}
                                            @if ($registration->isBypass())
                                                <span class="status-badge badge bg-info text-dark ms-2">
                                                    <i class="bi bi-bookmark-check me-1"></i>Bypass
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block">{{ $registration->formLink->email }}</small>
                                        <small class="text-muted d-block">NIK: {{ $registration->representative_nik }}</small>
                                        <small class="text-muted d-block">KK: {{ $registration->kk_number }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $registration->destination->name }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $registration->family_count }} orang</span>
                                </td>
                                <td>
                                    @if ($registration->qrCode && $registration->qrCode->scanned_at)
                                        <span class="status-badge badge-scanned">
                                            <i class="bi bi-check-circle me-1"></i>Sudah Scan
                                        </span>
                                        <small class="d-block mt-1 text-muted">
                                            {{ $registration->qrCode->scanned_at->format('d/m/Y H:i') }}
                                        </small>
                                    @else
                                        <span class="status-badge badge-not-scanned">
                                            <i class="bi bi-clock me-1"></i>Belum Scan
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $emailLog = $registration->formLink->emailLogs()
                                            ->where('email_type', 'seat_allocation')
                                            ->latest('created_at')
                                            ->first();
                                        $isScanned = $registration->qrCode && $registration->qrCode->scanned_at;
                                    @endphp
                                    @if (!$isScanned)
                                        <span class="badge bg-secondary">-</span>
                                    @elseif (!$emailLog)
                                        <span class="status-badge bg-secondary text-white">
                                            <i class="bi bi-envelope-exclamation-fill me-1"></i>Belum terkirim
                                        </span>
                                    @elseif ($emailLog->status === 'sent')
                                        <span class="status-badge bg-success text-white">
                                            <i class="bi bi-envelope-check-fill me-1"></i> Terkirim
                                        </span>
                                        <small class="d-block mt-1 text-muted">
                                            {{ $emailLog->sent_at->format('d/m/Y H:i') }}
                                        </small>
                                    @elseif ($emailLog->status === 'pending')
                                        <span class="status-badge bg-warning text-dark">
                                            <i class="bi bi-envelope-arrow-up-fill me-1"></i>Pending
                                        </span>
                                    @elseif ($emailLog->status === 'failed')
                                        <span class="status-badge bg-danger text-white">
                                            <i class="bi bi-envelope-x-fill me-1"></i> Gagal
                                        </span>
                                        <small class="d-block mt-1 text-muted">
                                            {{ $emailLog->failed_at?->format('d/m/Y H:i') }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-primary btn-action" onclick="showDetail({{ $registration->id }})" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if ($isScanned)
                                            <button type="button" class="btn btn-warning btn-action" onclick="resendEmailSeatingQuick({{ $registration->id }})" title="Kirim Ulang Email Kursi">
                                                <i class="bi bi-arrow-clockwise"></i> Re-sent Email
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Tidak ada data pendaftaran
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="mobile-cards">
            @forelse ($registrations as $registration)
                @php
                    $emailLog = $registration->formLink->emailLogs()
                        ->where('email_type', 'seat_allocation')
                        ->latest('created_at')
                        ->first();
                    $isScanned = $registration->qrCode && $registration->qrCode->scanned_at;
                @endphp
                <div class="registration-card">
                    <div class="registration-card-header">
                        <div>
                            <h6 class="fw-bold mb-1">#{{ $registration->id }} - {{ $registration->representative_name }}
                                @if ($registration->isBypass())
                                    <span class="status-badge badge bg-info text-dark ms-2">
                                        <i class="bi bi-bookmark-check me-1"></i>Bypass
                                    </span>
                                @endif
                            </h6>
                            <small class="text-muted">{{ $registration->formLink->email }}</small>
                        </div>
                        @if ($isScanned)
                            <span class="status-badge badge-scanned">
                                <i class="bi bi-check-circle"></i> Sudah
                            </span>
                        @else
                            <span class="status-badge badge-not-scanned">
                                <i class="bi bi-clock"></i> Belum
                            </span>
                        @endif
                    </div>
                    <div class="registration-card-body">
                        <div class="info-row">
                            <span class="info-label">NIK</span>
                            <span class="info-value">{{ $registration->representative_nik }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">KK</span>
                            <span class="info-value">{{ $registration->kk_number }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tujuan</span>
                            <span class="info-value">{{ $registration->destination->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Jumlah</span>
                            <span class="info-value">{{ $registration->family_count }} orang</span>
                        </div>
                        @if ($isScanned)
                            <div class="info-row">
                                <span class="info-label">Waktu Scan</span>
                                <span class="info-value">{{ $registration->qrCode->scanned_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status Email</span>
                                <span class="info-value">
                                    @if (!$emailLog)
                                        <span class="badge bg-warning">Belum</span>
                                    @elseif ($emailLog->status === 'sent')
                                        <span class="badge bg-success">Terkirim</span>
                                    @elseif ($emailLog->status === 'pending')
                                        <span class="badge bg-info">Pending</span>
                                    @elseif ($emailLog->status === 'failed')
                                        <span class="badge bg-danger">Gagal</span>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-primary btn-sm" onclick="showDetail({{ $registration->id }})">
                            <i class="bi bi-eye me-1"></i>Detail
                        </button>
                        @if ($isScanned)
                            <button type="button" class="btn btn-warning btn-sm" onclick="resendEmailSeatingQuick({{ $registration->id }})">
                                <i class="bi bi-arrow-clockwise me-1"></i>Re-sent Email
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                    <p class="text-muted">Tidak ada data pendaftaran</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($registrations->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Pendaftaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showDetail(registrationId) {
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            const modalContent = document.getElementById('modalContent');

            // Show loading
            modalContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            modal.show();

            // Fetch data
            fetch(`/cms/scan-monitoring/${registrationId}/detail`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderModalContent(data.data);
                    } else {
                        modalContent.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                ${data.message || 'Gagal memuat data'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Terjadi kesalahan saat memuat data
                        </div>
                    `;
                });
        }

        function renderModalContent(data) {
            const registration = data.registration;
            const scanInfo = data.scan_info;
            const emailInfo = data.email_info;
            const participants = data.participants;

            let scanStatusHtml = '';
            if (scanInfo.is_scanned) {
                scanStatusHtml = `
                    <div class="alert alert-success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-check-circle me-2"></i>Sudah Discan</strong>
                            </div>
                            <div class="text-end">
                                <div><small>Waktu: ${formatDateTime(scanInfo.scanned_at)}</small></div>
                                <div><small>Oleh: ${scanInfo.scanned_by || '-'}</small></div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                scanStatusHtml = `
                    <div class="alert alert-warning">
                        <strong><i class="bi bi-clock me-2"></i>Belum Discan</strong>
                    </div>
                `;
            }

            let emailStatusHtml = '';
            if (!emailInfo.is_scanned) {
                emailStatusHtml = `
                    <div class="alert alert-secondary mb-3">
                        <strong><i class="bi bi-dash-circle me-2"></i>Status Email: -</strong>
                        <small class="d-block mt-1">Peserta belum discan, email belum terkirim</small>
                    </div>
                `;
            } else if (!emailInfo.status) {
                emailStatusHtml = `
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-clock me-2"></i>Status Email: Belum Terkirim</strong>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning" onclick="resendEmailSeating(${registration.id})">
                                <i class="bi bi-send me-1"></i>Re-sent email
                            </button>
                        </div>
                    </div>
                `;
            } else if (emailInfo.status === 'sent') {
                emailStatusHtml = `
                    <div class="alert alert-success mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-check-circle me-2"></i>Status Email: Terkirim</strong>
                                <small class="d-block mt-1">Waktu: ${formatDateTime(emailInfo.sent_at)}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="resendEmailSeating(${registration.id})">
                                <i class="bi bi-send me-1"></i>Re-sent email
                            </button>
                        </div>
                    </div>
                `;
            } else if (emailInfo.status === 'pending') {
                emailStatusHtml = `
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-hourglass-split me-2"></i>Status Email: Pending</strong>
                                <small class="d-block mt-1">Email sedang dalam antrian pengiriman</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="resendEmailSeating(${registration.id})">
                                <i class="bi bi-send me-1"></i>Re-sent email
                            </button>
                        </div>
                    </div>
                `;
            } else if (emailInfo.status === 'failed') {
                emailStatusHtml = `
                    <div class="alert alert-danger mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-exclamation-circle me-2"></i>Status Email: Gagal</strong>
                                <small class="d-block mt-1">Waktu: ${formatDateTime(emailInfo.failed_at)}</small>
                                ${emailInfo.error_message ? `<small class="d-block mt-2 text-danger">${emailInfo.error_message}</small>` : ''}
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" onclick="resendEmailSeating(${registration.id})">
                                <i class="bi bi-send me-1"></i>Re-sent email
                            </button>
                        </div>
                    </div>
                `;
            }

            let participantsHtml = '';
            participants.forEach((participant, index) => {
                let seatInfo = '';
                if (participant.seat) {
                    if (participant.seat.seat_code === 'NO-SEAT') {
                        seatInfo = `
                            <div class="participant-info-row">
                                <span class="participant-info-label">Kursi:</span>
                                <span class="badge bg-secondary">Anak < 4 tahun (No Seat)</span>
                            </div>
                        `;
                    } else {
                        seatInfo = `
                            <div class="participant-info-row">
                                <span class="participant-info-label">Bus:</span>
                                <span class="participant-info-value">${participant.seat.bus_name}</span>
                            </div>
                            <div class="participant-info-row">
                                <span class="participant-info-label">Kursi:</span>
                                <span class="participant-info-value">${participant.seat.seat_label}</span>
                            </div>
                        `;
                    }
                } else {
                    seatInfo = `
                        <div class="participant-info-row">
                            <span class="participant-info-label">Kursi:</span>
                            <span class="badge bg-warning">Belum dialokasikan</span>
                        </div>
                    `;
                }

                participantsHtml += `
                    <div class="col-6">
                        <div class="participant-card">
                            <div class="participant-name">
                                ${index + 1}. ${participant.full_name}
                                ${participant.is_child_under_4 ? '<span class="badge bg-info ms-2">< 4 tahun</span>' : ''}
                            </div>
                            <div class="participant-info">
                                <div class="participant-info-row">
                                    <span class="participant-info-label">NIK/KIA:</span>
                                    <span class="participant-info-value">${participant.nik_kia}</span>
                                </div>
                                <div class="participant-info-row">
                                    <span class="participant-info-label">Tanggal Lahir:</span>
                                    <span class="participant-info-value">${participant.birth_date} (${participant.age} tahun)</span>
                                </div>
                                ${seatInfo}
                            </div>
                        </div>
                    </div>
                `;
            });

            const html = `
                ${scanStatusHtml}
                ${emailStatusHtml}

                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Informasi Umum</h6>
                    <div class="row">
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">ID Pendaftaran</div>
                                <div class="modal-detail-value">#${registration.id}</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">Nama Perwakilan</div>
                                <div class="modal-detail-value">${registration.representative_name}</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">Jumlah Peserta</div>
                                <div class="modal-detail-value">${registration.family_count} orang</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">NIK Perwakilan</div>
                                <div class="modal-detail-value">${registration.representative_nik}</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">Nomor KK</div>
                                <div class="modal-detail-value">${registration.kk_number}</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">Tujuan</div>
                                <div class="modal-detail-value">${registration.destination.name}</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">Nomor KK</div>
                                <div class="modal-detail-value">${registration.kk_number}</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">Tujuan</div>
                                <div class="modal-detail-value">${registration.destination.name}</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-0">
                            <div class="modal-detail-row pb-0">
                                <div class="modal-detail-label mb-0">Email</div>
                                <div class="modal-detail-value">${registration.form_link.email}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold mb-3">Daftar Peserta & Kursi</h6>
                    <div class="row">
                        ${participantsHtml}
                    </div>
                </div>
            `;

            document.getElementById('modalContent').innerHTML = html;
        }

        function resendEmailSeating(registrationId) {
            if (!confirm('Apakah Anda yakin ingin mengirim ulang email alokasi kursi?')) {
                return;
            }

            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

            fetch(`/cms/scan-monitoring/${registrationId}/resend-seat-allocation`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Terkirim!';
                        btn.classList.remove('btn-warning', 'btn-danger', 'btn-outline-success', 'btn-outline-info');
                        btn.classList.add('btn-success');

                        setTimeout(() => {
                            // Reload detail
                            showDetail(registrationId);
                        }, 1500);

                        showAlert('success', data.message);
                    } else {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        showAlert('danger', data.message || 'Gagal mengirim ulang email');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    showAlert('danger', 'Terjadi kesalahan saat mengirim ulang email');
                });
        }

        function showAlert(type, message) {
            // Create bootstrap alert
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="margin-bottom: 0;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            // Insert into dedicated alert container (between filters and data)
            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = alertHtml;
            const container = document.getElementById('scanMonitoringAlertContainer') || document.body;
            container.insertBefore(alertDiv.firstElementChild, container.firstChild);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                    const container = document.getElementById('scanMonitoringAlertContainer') || document.body;
                    const alert = container.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
            }, 5000);
        }

        function formatDateTime(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        function resendEmailSeatingQuick(registrationId) {
            if (!confirm('Yakin ingin mengirim ulang email alokasi kursi?')) {
                return;
            }

            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

            fetch(`/cms/scan-monitoring/${registrationId}/resend-seat-allocation`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Terkirim!';
                        btn.classList.remove('btn-warning');
                        btn.classList.add('btn-success');

                        setTimeout(() => {
                            // Reload halaman dengan preserve query string
                            window.location.href = window.location.href;
                        }, 1500);

                        showAlert('success', data.message);
                    } else {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        showAlert('danger', data.message || 'Gagal mengirim ulang email');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    showAlert('danger', 'Terjadi kesalahan saat mengirim ulang email');
                });
        }
    </script>
@endpush
