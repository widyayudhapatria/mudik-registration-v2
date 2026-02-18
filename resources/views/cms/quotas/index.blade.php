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

        /* Past quota styling (disabled) */
        .past-quota {
            opacity: 0.7;
            background-color: #fafafa;
        }

        .quota-action-btn[disabled] {
            pointer-events: none;
            opacity: 0.6;
        }

        .modal-body {
            padding: 16px;
        }

        /* Past date card (disabled) */
        .quota-date-card.past-date {
            background: #f5f5f5;
            border-left-color: #bdbdbd;
            color: #9e9e9e;
            opacity: 0.9;
        }

        .quota-date-card.past-date .quota-date-title,
        .quota-date-card.past-date .quota-date-info {
            color: #757575;
        }

        .form-floating>.form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.18rem rgba(76, 175, 80, 0.18);
        }

        /* Destination Summary Table */
        .destination-grid-table {
            width: 100%;
            border-collapse: collapse;
        }

        .destination-grid-table thead th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px 12px;
            font-weight: 700;
            color: #212121;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .destination-grid-table tbody td {
            border: 1px solid #dee2e6;
            padding: 10px 12px;
            vertical-align: middle;
            color: #424242;
            font-size: 12px;
        }

        .destination-grid-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .dest-name {
            font-weight: 600;
            color: #212121;
        }

        .dest-code {
            font-size: 11px;
            color: #999;
        }

        .dest-quota-value {
            font-weight: 600;
            text-align: center;
        }

        .detail-btn {
            padding: 5px 12px;
            font-size: 11px;
            border-radius: 6px;
            white-space: nowrap;
        }

        /* Detail Modal Styles */
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }

        .detail-dest-name {
            font-size: 18px;
            font-weight: 700;
            color: #212121;
        }

        .detail-quota-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .detail-quota-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        .detail-quota-label {
            font-size: 11px;
            color: #757575;
            margin-bottom: 4px;
        }

        .detail-quota-value {
            font-size: 20px;
            font-weight: 700;
            color: #2196F3;
        }

        .detail-suggestion {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 12px;
        }

        .suggestion-label {
            color: #1565c0;
            font-weight: 600;
        }

        .daily-quota-table {
            width: 100%;
            margin-top: 12px;
        }

        .daily-quota-table thead th {
            background-color: #f0f0f0;
            border: 1px solid #e0e0e0;
            padding: 8px 10px;
            font-weight: 600;
            color: #424242;
            text-align: center;
            font-size: 11px;
        }

        .daily-quota-table tbody td {
            border: 1px solid #e0e0e0;
            padding: 8px 10px;
            text-align: center;
            font-size: 11px;
        }

        .daily-quota-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .daily-quota-table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .date-cell {
            text-align: left !important;
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
        <h5 class="fw-bold mb-3">Ringkasan Kuota Destinasi</h5>
        <div style="overflow-x: auto;">
            <table class="destination-grid-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Kota</th>
                        <th style="width: 18%; text-align: center;">Total Quota</th>
                        <th style="width: 18%; text-align: center;">Terpakai</th>
                        <th style="width: 18%; text-align: center;">Sisa</th>
                        <th style="width: 21%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($destinations as $dest)
                        <tr>
                            <td>
                                <div class="dest-name">{{ $dest->name }}</div>
                                <span class="dest-code">{{ $dest->code }}</span>
                            </td>
                            <td class="dest-quota-value">{{ number_format($dest->total_quota) }}</td>
                            <td class="dest-quota-value" style="color: #FF9800;">{{ number_format($dest->used_quota) }}
                            </td>
                            <td class="dest-quota-value" style="color: #4CAF50;">
                                {{ number_format($dest->remaining_quota) }}</td>
                            <td style="text-align: center;">
                                <button class="btn btn-sm btn-outline-primary detail-btn"
                                    onclick="showDestinationDetail({{ $dest->id }}, '{{ $dest->name }}')">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quota Cards by Date -->
    <div class="quota-list">
        @forelse($quotasByDate as $dateStr => $quotasForDate)
            @php
                $dateObj = \Carbon\Carbon::parse($dateStr);
                $isToday = $dateObj->isToday();
            @endphp
            @php $isPastDate = $dateObj->isBefore(\Carbon\Carbon::today()); @endphp
            <div class="quota-date-card {{ $isToday ? 'today' : '' }} {{ $isPastDate ? 'past-date' : '' }}">
                <!-- Date Header -->
                <div class="quota-date-header">
                    <div>
                        <div class="quota-date-title">
                            {{ $dateObj->format('d F Y') }}
                            @if ($isToday)
                                <span class="badge bg-success ms-2" style="font-size: 11px;">Hari Ini</span>
                            @endif
                            @if ($isPastDate)
                                <span class="badge bg-secondary ms-2" style="font-size: 11px;">Lewat</span>
                            @endif
                        </div>
                        <div class="quota-date-info">
                            {{ $dateObj->isoFormat('dddd') }}
                        </div>
                    </div>
                    <div class="quota-date-badge">
                        <span style="font-size: 13px; color: #757575;">
                            {{ $quotasForDate->count() }} kota
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

                                    $isPast = $quota->date->isBefore(\Carbon\Carbon::today());
                                @endphp
                                <tr class="{{ $isPast ? 'past-quota' : '' }}">
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
                                        @if ($isPast)
                                            <button class="btn btn-sm btn-outline-secondary quota-action-btn" disabled
                                                title="Tanggal sudah lewat - tidak bisa diedit">
                                                <i class="bi bi-x-circle"></i> Tidak bisa edit
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-primary quota-action-btn"
                                                onclick="editQuota({{ $quota->id }}, {{ $quota->destination_id }}, '{{ $quota->date->toDateString() }}', {{ $quota->quota_daily }}, '{{ $quota->destination->name }}')"
                                                title="Edit Kuota">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                        @endif
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

<!-- Destination Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="detailModalTitle">Detail Kuota Destinasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Header Info -->
                <div class="detail-header">
                    <div class="detail-dest-name" id="detailDestName">-</div>
                </div>

                <!-- Quota Info -->
                <div class="detail-quota-info">
                    <div class="detail-quota-item">
                        <div class="detail-quota-label">Total Quota (destination)</div>
                        <div class="detail-quota-value" id="detailTotalQuota">0</div>
                    </div>
                    <div class="detail-quota-item">
                        <div class="detail-quota-label">Sudah Terpakai (approved)</div>
                        <div class="detail-quota-value" style="color: #FF9800;" id="detailUsedQuota">0</div>
                    </div>
                    <div class="detail-quota-item">
                        <div class="detail-quota-label">Sisa Quota (remaining)</div>
                        <div class="detail-quota-value" style="color: #4CAF50;" id="detailRemainingQuota">0</div>
                    </div>
                </div>
                <!-- Daily Quotas Table -->
                <h6 class="fw-bold mb-0">
                    Set Kuota (Semua Tanggal)
                </h6>
                <div style="overflow-x: auto;">
                    <table class="daily-quota-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Tanggal</th>
                                <th style="width: 18%; text-align: center;">Daily Quota</th>
                                <th style="width: 18%; text-align: center;">Terpakai</th>
                                <th style="width: 18%; text-align: center;">Sisa</th>
                            </tr>
                        </thead>
                        <tbody id="detailQuotasBody">
                            <tr>
                                <td colspan="4" style="text-align: center; color: #999; padding: 20px;">
                                    Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Suggestion -->
                <div class="detail-suggestion" style="margin-top: 20px; margin-bottom: 10px;">
                    <div class="suggestion-label">Info Summary:</div>
                    <div style="margin-top: 6px;">
                        <strong>Total plot (daily) quota:</strong> <span id="detailTotalDailyQuota">0</span> kuota
                        <br>
                        <strong>Remaining keseluruhan:</strong> <span id="detailRemainingSummary"
                            style="color: #1565c0; font-weight: 600;">0</span> kuota
                        <br>
                        <strong>Dibutuhkan alokasi:</strong> <span id="detailQuotaNeedSummary"
                            style="color: #FF9800; font-weight: 600;">0</span> kuota
                        <br><br>
                        <div id="allocationAlert"
                            style="background: rgba(21, 101, 192, 0.1); padding: 10px; border-radius: 6px; margin-top: 8px; display: none;">
                            <span style="color: #1565c0; font-size: 13px; font-weight: 700;">
                                ✓ Anda perlu mengalokasikan +
                            </span>
                            <span id="detailAllocatableQuota"
                                style="color: #1565c0; font-weight: 700; font-size: 16px; display: inline-block;">
                                0
                            </span>
                            <span style="color: #1565c0; font-size: 13px;">kuota lagi supaya kuota terpenuhi</span>
                        </div>
                    </div>
                </div>
                <div id="emptyQuotasMessage" style="display: none; text-align: center; padding: 30px; color: #999;">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    <p>Belum ada kuota yang diset untuk destinasi ini</p>
                </div>
            </div>
        </div>
    </div>
</div>

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
                            silahkan gunakan tombol <strong>Edit</strong> pada listing untuk memperbarui kuota.
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

        // Show destination detail modal
        async function showDestinationDetail(destinationId, destinationName) {
            try {
                // Update modal title
                document.getElementById('detailModalTitle').textContent = `Detail Kuota: ${destinationName}`;
                document.getElementById('detailDestName').textContent = destinationName;

                // Fetch destination detail
                const response = await axios.get(`/cms/quotas/destination/${destinationId}/detail`);
                const data = response.data;

                if (!data.success) {
                    alert('Gagal memuat detail kuota');
                    return;
                }

                // Populate info
                const dest = data.destination;
                document.getElementById('detailTotalQuota').textContent = number_format(dest.total_quota);
                document.getElementById('detailUsedQuota').textContent = number_format(dest.used_quota);
                document.getElementById('detailRemainingQuota').textContent = number_format(dest.remaining_quota);

                // Populate suggestion
                // Calculate quota allocation recommendation
                const today = data.current_date; // Use server-provided current date to handle timezone correctly

                // Total daily quota dari tanggal aktif (today onwards) saja
                const totalDailyQuotaFromToday = data.daily_quotas
                    .filter(q => q.date >= today)
                    .reduce((sum, q) => sum + q.quota_daily, 0);

                // Remaining quota dari destination (total - used)
                const remainingQuota = dest.remaining_quota;

                // Quota yang perlu ditambah = remaining - total daily quota aktif
                const quotaNeeded = Math.max(0, remainingQuota - totalDailyQuotaFromToday);

                // Populate Info Summary
                document.getElementById('detailTotalDailyQuota').textContent = number_format(totalDailyQuotaFromToday);
                document.getElementById('detailRemainingSummary').textContent = number_format(remainingQuota);
                document.getElementById('detailQuotaNeedSummary').textContent = number_format(quotaNeeded);
                document.getElementById('detailAllocatableQuota').textContent = number_format(quotaNeeded);

                // Show/hide allocation alert based on quotaNeeded
                const allocationAlert = document.getElementById('allocationAlert');
                if (quotaNeeded > 0) {
                    allocationAlert.style.display = 'block';
                } else {
                    allocationAlert.style.display = 'none';
                }

                // Populate daily quotas table
                const quotasBody = document.getElementById('detailQuotasBody');
                const emptyMsg = document.getElementById('emptyQuotasMessage');

                if (data.daily_quotas.length === 0) {
                    quotasBody.closest('table').style.display = 'none';
                    emptyMsg.style.display = 'block';
                } else {
                    quotasBody.closest('table').style.display = 'table';
                    emptyMsg.style.display = 'none';

                    // Calculate totals for today onwards only
                    const dailyQuotasFromToday = data.daily_quotas.filter(q => q.date >= today);
                    const totalQuotaDaily = dailyQuotasFromToday.reduce((sum, q) => sum + q.quota_daily, 0);
                    const totalUsedDaily = dailyQuotasFromToday.reduce((sum, q) => sum + q.used_daily, 0);
                    const totalRemainingDaily = dailyQuotasFromToday.reduce((sum, q) => sum + q.remaining_daily, 0);

                    quotasBody.innerHTML = data.daily_quotas.map(quota => {
                        const today = data.current_date; // Use server-provided current date
                        const isPassedDate = quota.date < today;
                        const rowClass = isPassedDate ? 'style="opacity: 0.6; background-color: #f5f5f5;text-decoration: line-through;"' : '';

                        return `
                            <tr ${rowClass}>
                                <td class="date-cell">
                                    ${quota.date_formatted}
                                    ${isPassedDate ? '<span style="font-size: 10px; color: #999; margin-left: 8px;">(Lewat)</span>' : ''}
                                </td>
                                <td style="text-align: center;"><strong>${quota.quota_daily}</strong></td>
                                <td style="text-align: center; color: #FF9800;"><strong>${quota.used_daily}</strong></td>
                                <td style="text-align: center; color: #4CAF50;"><strong>${quota.remaining_daily}</strong></td>
                            </tr>
                        `;
                    }).join('') + `
                        <tr style="background-color: #f0f0f0; font-weight: 700; border-top: 2px solid #dee2e6;">
                            <td class="date-cell" style="font-weight: 700;">Total</td>
                            <td style="text-align: center;"><strong>${totalQuotaDaily}</strong></td>
                            <td style="text-align: center; color: #FF9800;"><strong>${totalUsedDaily}</strong></td>
                            <td style="text-align: center; color: #4CAF50;"><strong>${totalRemainingDaily}</strong></td>
                        </tr>
                    `;
                }

                // Show detail modal
                const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                detailModal.show();
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal memuat detail kuota: ' + (error.response?.data?.message || error.message));
            }
        }

        // Helper function untuk format number
        function number_format(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    </script>
@endpush
