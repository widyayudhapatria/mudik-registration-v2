@extends('layouts.cms')

@section('title', 'Manajemen Kuota Harian - CMS Admin')
@section('page-title', 'Manajemen Kuota Harian')

@push('styles')
    <style>
        .calendar-header {
            background: white;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .date-selector {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .quota-date-card {
            background: white;
            border-radius: 16px;
            padding: 12px 10px 14px 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 12px;
            border-left: 4px solid #2196F3;
        }

        .quota-date-card.today {
            border-left-color: #4CAF50;
            background: linear-gradient(to right, rgba(76, 175, 80, 0.05) 0%, white 100%);
        }

        .quota-date-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .quota-date-title {
            font-size: 17px;
            font-weight: 700;
            color: #212121;
        }

        .quota-date-info {
            font-size: 12px;
            color: #757575;
        }

        .quota-date-badge {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .quota-table {
            width: 100%;
            margin: 0;
        }

        .quota-table thead th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 7px 8px;
            font-weight: 600;
            color: #212121;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quota-table tbody td {
            border: 1px solid #dee2e6;
            padding: 8px 8px;
            vertical-align: middle;
            color: #424242;
            font-size: 12px;
        }

        .quota-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .quota-destination-name {
            font-weight: 600;
            color: #212121;
        }

        .quota-destination-code {
            font-size: 11px;
            color: #999;
            display: block;
            margin-top: 2px;
        }

        .quota-bar-small {
            height: 5px;
            background-color: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            margin: 3px 0;
        }

        .quota-bar-fill-small {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50 0%, #2E7D32 100%);
        }

        .quota-bar-fill-small.warning {
            background: linear-gradient(90deg, #FF9800 0%, #F57C00 100%);
        }

        .quota-bar-fill-small.danger {
            background: linear-gradient(90deg, #f44336 0%, #d32f2f 100%);
        }

        .quota-percentage {
            font-size: 11px;
            color: #757575;
            margin-top: 2px;
        }

        .quota-action-btn {
            padding: 4px 8px;
            font-size: 11px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 16px 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 16px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .summary-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .summary-label {
            font-size: 11px;
            color: #757575;
        }

        .modal-body {
            padding: 16px;
        }

        .form-floating>.form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.18rem rgba(76, 175, 80, 0.18);
        }

        @media (max-width: 992px) {
            .calendar-header {
                padding: 12px;
            }

            .quota-date-card {
                padding: 10px;
            }

            .quota-table thead th,
            .quota-table tbody td {
                padding: 6px 6px;
                font-size: 11px;
            }

            .summary-card {
                padding: 10px 8px;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
        }

        @media (max-width: 768px) {
            .calendar-header {
                padding: 8px;
            }

            .calendar-header h5 {
                font-size: 1rem;
            }

            .date-selector {
                flex-direction: column;
                align-items: stretch;
            }

            .date-selector>div {
                width: 100%;
            }

            .quota-date-card {
                padding: 7px;
                margin-bottom: 10px;
            }

            .quota-date-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .quota-date-title {
                font-size: 14px;
            }

            .quota-table {
                font-size: 10px;
            }

            .quota-table thead th,
            .quota-table tbody td {
                padding: 5px 4px;
                font-size: 10px;
            }

            .quota-action-btn {
                padding: 3px 6px;
                font-size: 10px;
            }

            .summary-card {
                padding: 8px 6px;
            }

            .summary-card h5 {
                font-size: 0.95rem;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .summary-item {
                padding: 7px;
            }

            .summary-value {
                font-size: 14px;
            }

            .summary-label {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .quota-date-card {
                padding: 5px;
                margin-bottom: 7px;
            }

            .quota-date-title {
                font-size: 12px;
            }

            .quota-date-header {
                margin-bottom: 7px;
            }

            .quota-table {
                font-size: 9px;
                overflow-x: auto;
            }

            .quota-table thead th,
            .quota-table tbody td {
                padding: 3px 2px;
                font-size: 9px;
            }

            .quota-action-btn {
                padding: 2px 4px;
                font-size: 9px;
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <!-- Calendar Header -->
    <div class="calendar-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-bold">Kalender Kuota</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuotaModal">
                <i class="bi bi-plus-circle me-2"></i>Set Kuota
            </button>
        </div>

        <form method="GET" id="dateFilterForm">
            <div class="date-selector">
                <div class="flex-grow-1">
                    <label class="form-label small fw-semibold">Destinasi</label>
                    <select name="destination_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Destinasi</option>
                        @foreach ($destinations as $dest)
                            <option value="{{ $dest->id }}"
                                {{ request('destination_id') == $dest->id ? 'selected' : '' }}>
                                {{ $dest->name }} ({{ $dest->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control"
                        value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}"
                        onchange="this.form.submit()">
                </div>
                <div class="flex-grow-1">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control"
                        value="{{ request('end_date', now()->endOfMonth()->toDateString()) }}"
                        onchange="this.form.submit()">
                </div>
                <div style="margin-top: 28px;">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('cms.quotas.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="summary-card">
        <h5 class="fw-bold mb-3">Ringkasan Kuota (Periode yang Dipilih)</h5>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value" style="color: #2196F3;">{{ $quotas->count() }}</div>
                <div class="summary-label">Total Entry Kuota</div>
            </div>
            <div class="summary-item">
                <div class="summary-value" style="color: #4CAF50;">{{ number_format($quotas->sum('quota_daily')) }}</div>
                <div class="summary-label">Total Kuota</div>
            </div>
            <div class="summary-item">
                <div class="summary-value" style="color: #FF9800;">{{ number_format($quotas->sum('used_daily')) }}</div>
                <div class="summary-label">Sudah Terpakai</div>
            </div>
            <div class="summary-item">
                <div class="summary-value" style="color: #9C27B0;">{{ number_format($quotas->sum('remaining_daily')) }}
                </div>
                <div class="summary-label">Sisa Kuota</div>
            </div>
        </div>
    </div>

    <!-- Quota Cards by Date -->
    <div class="quota-list">
        @forelse($quotasByDate as $dateStr => $quotasForDate)
            @php
                $dateObj = \Carbon\Carbon::parse($dateStr);
                $isToday = $dateObj->isToday();
            @endphp
            <div class="quota-date-card {{ $isToday ? 'today' : '' }}">
                <!-- Date Header -->
                <div class="quota-date-header">
                    <div>
                        <div class="quota-date-title">
                            {{ $dateObj->format('d F Y') }}
                            @if ($isToday)
                                <span class="badge bg-success ms-2" style="font-size: 11px;">Hari Ini</span>
                            @endif
                        </div>
                        <div class="quota-date-info">
                            {{ $dateObj->isoFormat('dddd') }}
                        </div>
                    </div>
                    <div class="quota-date-badge">
                        <span style="font-size: 13px; color: #757575;">
                            📊 {{ $quotasForDate->count() }} kota
                        </span>
                    </div>
                </div>

                <!-- Date Quota Table -->
                <div style="overflow-x: auto;">
                    <table class="quota-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Kota</th>
                                <th style="width: 15%; text-align: center;">Daily Quota</th>
                                <th style="width: 15%; text-align: center;">Terpakai</th>
                                <th style="width: 15%; text-align: center;">Sisa</th>
                                <th style="width: 15%; text-align: center;">Progress</th>
                                <th style="width: 15%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quotasForDate as $quota)
                                @php
                                    $percentage =
                                        $quota->quota_daily > 0 ? ($quota->used_daily / $quota->quota_daily) * 100 : 0;
                                    $barClass = '';
                                    if ($percentage >= 80) {
                                        $barClass = 'danger';
                                    } elseif ($percentage >= 60) {
                                        $barClass = 'warning';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="quota-destination-name">{{ $quota->destination->name }}</div>
                                        <span class="quota-destination-code">{{ $quota->destination->code }}</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <strong style="font-size: 16px;">{{ $quota->quota_daily }}</strong>
                                    </td>
                                    <td style="text-align: center;">
                                        <strong style="color: #FF9800;">{{ $quota->used_daily }}</strong>
                                    </td>
                                    <td style="text-align: center;">
                                        <strong style="color: #4CAF50;">{{ $quota->remaining_daily }}</strong>
                                    </td>
                                    <td>
                                        <div class="quota-bar-small">
                                            <div class="quota-bar-fill-small {{ $barClass }}"
                                                style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <div class="quota-percentage">
                                            {{ number_format($percentage, 0) }}%
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <button class="btn btn-sm btn-outline-primary quota-action-btn"
                                            onclick="editQuota({{ $quota->id }}, {{ $quota->destination_id }}, '{{ $quota->date->toDateString() }}', {{ $quota->quota_daily }}, '{{ $quota->destination->name }}')"
                                            title="Edit Kuota">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div style="background: white; border-radius: 16px; padding: 40px; text-align: center;">
                <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
                <p class="text-muted" style="margin-bottom: 20px;">Tidak ada kuota yang diatur untuk periode ini</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuotaModal">
                    <i class="bi bi-plus-circle me-2"></i>Set Kuota Sekarang
                </button>
            </div>
        @endforelse
    </div>
@endsection

<!-- Add/Edit Quota Modal -->
<div class="modal fade" id="addQuotaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Set Kuota Harian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quotaForm">
                    <input type="hidden" id="quota_id" name="id" value="">

                    <div class="form-floating mb-3">
                        <select class="form-select" id="quota_destination" name="destination_id" required>
                            <option value="">Pilih Destinasi</option>
                            @foreach ($destinations as $dest)
                                <option value="{{ $dest->id }}" data-quota="{{ $dest->total_quota }}"
                                    data-remaining="{{ $dest->remaining_quota }}"
                                    data-used="{{ $dest->used_quota }}">
                                    {{ $dest->name }} (Total: {{ $dest->total_quota }}, Remaining:
                                    {{ $dest->remaining_quota }})
                                </option>
                            @endforeach
                        </select>
                        <label for="quota_destination">
                            <i class="bi bi-geo-alt me-2"></i>Destinasi
                        </label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="quota_date" name="date"
                            min="{{ now()->toDateString() }}" required>
                        <label for="quota_date">
                            <i class="bi bi-calendar3 me-2"></i>Tanggal
                        </label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="quota_amount" name="quota_daily"
                            min="0" required>
                        <label for="quota_amount">
                            <i class="bi bi-123 me-2"></i>Jumlah Kuota Harian
                        </label>
                        <div class="form-text" id="quotaHint">Masukkan 0 untuk menonaktifkan kuota di tanggal ini
                        </div>
                    </div>

                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <small>
                            <strong>Catatan:</strong> Jika kuota untuk tanggal ini sudah ada,
                            maka akan di-update dengan nilai baru.
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 12px;">
                            <span class="btn-text">
                                <i class="bi bi-check-circle me-2"></i>Simpan Kuota
                            </span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Menyimpan...
                            </span>
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 12px;">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const quotaModal = new bootstrap.Modal(document.getElementById('addQuotaModal'));

        // Show destination remaining quota when select changes
        document.getElementById('quota_destination')?.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const totalQuota = selectedOption.dataset.quota || 'N/A';
            const remainingQuota = selectedOption.dataset.remaining || 'N/A';
            const hintEl = document.getElementById('quotaHint');

            if (hintEl && this.value) {
                hintEl.innerHTML = `
            <strong>Destinasi terpilih:</strong> Total: ${totalQuota} | Remaining: ${remainingQuota}<br>
            <small style="color: #666;">Masukkan nilai kuota ≤ ${remainingQuota} atau 0 untuk menonaktifkan</small>
        `;
            } else if (hintEl) {
                hintEl.textContent = 'Pilih destinasi untuk melihat informasi kuota';
            }
        });

        // Edit quota function
        function editQuota(id, destinationId, date, currentQuota, destinationName = '') {
            // Set title to edit mode
            const modalTitle = document.getElementById('modalTitle');
            modalTitle.textContent =
                `Edit Kuota: ${destinationName || 'Destinasi'} (${new Date(date).toLocaleDateString('id-ID', {year: 'numeric', month: 'long', day: 'numeric'})})`;

            // Fill form
            document.getElementById('quota_id').value = id;
            const destSelect = document.getElementById('quota_destination');
            destSelect.value = destinationId;
            destSelect.disabled = true; // Disable destination select on edit
            destSelect.dispatchEvent(new Event('change')); // Trigger hint update

            document.getElementById('quota_date').value = date;
            document.getElementById('quota_date').disabled = true; // Disable date on edit
            document.getElementById('quota_amount').value = currentQuota;

            quotaModal.show();
        }

        // Validate form before submission
        function validateQuotaForm() {
            const destinationId = document.getElementById('quota_destination').value;
            const date = document.getElementById('quota_date').value;
            const quotaAmount = parseInt(document.getElementById('quota_amount').value) || 0;
            const today = new Date().toISOString().split('T')[0];

            // Validate date
            if (date < today) {
                alert('❌ Tanggal tidak boleh di masa lalu. Silahkan pilih tanggal hari ini atau yang akan datang.');
                return false;
            }

            // Validate destination
            if (!destinationId) {
                alert('❌ Silahkan pilih destinasi');
                return false;
            }

            // Validate quota
            if (quotaAmount < 0) {
                alert('❌ Kuota tidak boleh negatif');
                return false;
            }

            // Validate quota doesn't exceed remaining
            const selectedOption = document.querySelector(`#quota_destination option[value="${destinationId}"]`);
            if (selectedOption) {
                const remaining = parseInt(selectedOption.dataset.remaining) || 0;
                if (quotaAmount > remaining && quotaAmount !== 0) {
                    alert(`❌ Kuota melebihi sisa kuota destinasi (${remaining}). Silahkan kurangi jumlah kuota.`);
                    return false;
                }
            }

            return true;
        }

        // Form submission
        document.getElementById('quotaForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Client-side validation
            if (!validateQuotaForm()) {
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');

            // Disable button
            submitBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');

            try {
                const formData = new FormData(this);
                const data = Object.fromEntries(formData);

                const response = await axios.post('{{ route('cms.quotas.store') }}', data);

                if (response.data.success) {
                    alert('✅ Kuota berhasil disimpan!');
                    location.reload();
                }
            } catch (error) {
                let errorMessage = 'Gagal menyimpan kuota';
                let errorCode = null;

                if (error.response && error.response.data) {
                    errorMessage = error.response.data.message || errorMessage;
                    errorCode = error.response.data.code;

                    // Handle specific error codes with better messages
                    switch (errorCode) {
                        case 'INVALID_DATE':
                            errorMessage =
                                '❌ Tanggal tidak boleh di masa lalu. Silahkan pilih tanggal hari ini atau yang akan datang.';
                            break;
                        case 'INVALID_DESTINATION':
                            errorMessage = '❌ Destinasi tidak ditemukan atau tidak aktif.';
                            break;
                        case 'EXCEED_REMAINING_QUOTA':
                            errorMessage = errorMessage; // Use message from backend
                            break;
                        case 'QUOTA_ALREADY_EXISTS':
                            errorMessage =
                                '⚠️ Kuota untuk tanggal ini sudah ada.\n\nSilahkan gunakan tombol "Edit" untuk memperbarui kuota.';
                            break;
                    }

                    if (error.response.data.errors) {
                        const errors = Object.values(error.response.data.errors).flat();
                        errorMessage = errors.join('\n');
                    }
                }

                alert(errorMessage);
            } finally {
                // Enable button
                submitBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
            }
        });

        // Reset modal when closed
        document.getElementById('addQuotaModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalTitle').textContent = 'Set Kuota Harian';
            document.getElementById('quotaForm').reset();
            document.getElementById('quota_id').value = '';

            // Re-enable fields
            document.getElementById('quota_destination').disabled = false;
            document.getElementById('quota_date').disabled = false;

            const hintEl = document.getElementById('quotaHint');
            if (hintEl) {
                hintEl.textContent = 'Pilih destinasi untuk melihat informasi kuota';
            }
        });
    </script>
@endpush
