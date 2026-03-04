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

                <div class="col-md-3 col-12">
                    <label class="form-label fw-semibold small">Status Scan</label>
                    <select name="scan_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="scanned" {{ request('scan_status') == 'scanned' ? 'selected' : '' }}>Sudah Scan
                        </option>
                        <option value="not_scanned" {{ request('scan_status') == 'not_scanned' ? 'selected' : '' }}>Belum
                            Scan</option>
                    </select>
                </div>

                <div class="col-md-4 col-12">
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
                            <th class="text-center">Detail</th>
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
                                        <div class="fw-semibold">{{ $registration->representative_name }}</div>
                                        <small class="text-muted d-block">{{ $registration->formLink->email }}</small>
                                        <small class="text-muted d-block">NIK: {{ $registration->representative_nik }}</small>
                                        <small class="text-muted d-block">KK: {{ $registration->kk_number }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $registration->destination->name }}</span>
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
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary btn-action" onclick="showDetail({{ $registration->id }})">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
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
                <div class="registration-card">
                    <div class="registration-card-header">
                        <div>
                            <h6 class="fw-bold mb-1">#{{ $registration->id }} - {{ $registration->representative_name }}
                            </h6>
                            <small class="text-muted">{{ $registration->formLink->email }}</small>
                        </div>
                        @if ($registration->qrCode && $registration->qrCode->scanned_at)
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
                        @if ($registration->qrCode && $registration->qrCode->scanned_at)
                            <div class="info-row">
                                <span class="info-label">Waktu Scan</span>
                                <span class="info-value">{{ $registration->qrCode->scanned_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-primary btn-sm" onclick="showDetail({{ $registration->id }})">
                            <i class="bi bi-eye me-1"></i>Detail
                        </button>
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

                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Informasi Umum</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">ID Pendaftaran</div>
                                <div class="modal-detail-value">#${registration.id}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">Nama Perwakilan</div>
                                <div class="modal-detail-value">${registration.representative_name}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">Jumlah Peserta</div>
                                <div class="modal-detail-value">${registration.family_count} orang</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">NIK Perwakilan</div>
                                <div class="modal-detail-value">${registration.representative_nik}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">Nomor KK</div>
                                <div class="modal-detail-value">${registration.kk_number}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">Tujuan</div>
                                <div class="modal-detail-value">${registration.destination.name}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">Nomor KK</div>
                                <div class="modal-detail-value">${registration.kk_number}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">Tujuan</div>
                                <div class="modal-detail-value">${registration.destination.name}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-detail-row">
                                <div class="modal-detail-label">Email</div>
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
    </script>
@endpush
