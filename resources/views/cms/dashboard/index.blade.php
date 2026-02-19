@extends('layouts.cms')

@section('title', 'Dashboard - CMS Admin')
@section('page-title', 'Dashboard Pendaftaran Mudik')

@push('styles')
    <style>
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #757575;
            font-size: 0.9rem;
        }

        .table-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #333;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .stat-card {
                padding: 20px;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                margin-bottom: 12px;
            }

            .stat-value {
                font-size: 1.75rem;
            }

            .stat-label {
                font-size: 0.85rem;
            }

            .table-card {
                padding: 20px;
                overflow-x: auto;
            }

            .section-title {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .stat-value {
                font-size: 1.5rem;
            }

            .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
        }
    </style>
@endpush

@section('content')
    {{-- Section 1: Aktivitas Hari Ini --}}
    <div class="mb-3">
        <h5 class="section-title mb-3">
            <i class="bi bi-calendar-event me-2"></i>Aktivitas Hari Ini
            <small class="text-muted fw-normal">({{ \Carbon\Carbon::today()->isoFormat('dddd, D MMMM Y') }})</small>
        </h5>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); color: white;">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div class="stat-value">{{ $statistics['today']['email_submissions'] }}</div>
                <div class="stat-label">Email Submit</div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #9E9E9E 0%, #757575 100%); color: white;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-value">{{ $statistics['today']['pending'] }}</div>
                <div class="stat-label">Pending (Belum Submit)</div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%); color: white;">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div class="stat-value">{{ $statistics['today']['submitted'] }}</div>
                <div class="stat-label">Submitted (Menunggu Approval)</div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); color: white;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-value">{{ $statistics['today']['approved'] }}</div>
                <div class="stat-label">Approved</div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 col-xl">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #F44336 0%, #C62828 100%); color: white;">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="stat-value">{{ $statistics['today']['rejected'] }}</div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>

    {{-- Section 2: Ringkasan Keseluruhan --}}
    <div class="mb-3">
        <h5 class="section-title mb-3">
            <i class="bi bi-bar-chart-fill me-2"></i>Ringkasan Keseluruhan
        </h5>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #9E9E9E 0%, #616161 100%); color: white;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-value">{{ $statistics['overview']['total_pending'] }}</div>
                <div class="stat-label">Total Pending</div>
                <p class="text-muted small mb-0 mt-2">Link terkirim, belum submit form</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%); color: white;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-value">{{ $statistics['overview']['total_submitted'] }}</div>
                <div class="stat-label">Total Submitted</div>
                <p class="text-muted small mb-0 mt-2">Sudah submit, menunggu approval</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #00BCD4 0%, #0097A7 100%); color: white;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-value">{{ $statistics['overview']['total_approved'] }}</div>
                <div class="stat-label">Total Approved</div>
                <p class="text-muted small mb-0 mt-2">Pendaftaran disetujui</p>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #F44336 0%, #C62828 100%); color: white;">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="stat-value">{{ $statistics['overview']['total_rejected'] }}</div>
                <div class="stat-label">Total Rejected</div>
                <p class="text-muted small mb-0 mt-2">Pendaftaran ditolak</p>
            </div>
        </div>
    </div>

    {{-- Section 3: Participants vs Registrations --}}
    <div class="mb-3">
        <h5 class="section-title mb-3">
            <i class="bi bi-people-fill me-2"></i>Pendaftaran vs Peserta
        </h5>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon me-3"
                        style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $statistics['summary']['total_registrations'] }}</div>
                        <div class="stat-label">Total Pendaftaran</div>
                    </div>
                </div>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Jumlah formulir pendaftaran yang masuk (semua status)
                </p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon me-3"
                        style="background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%); color: white;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $statistics['summary']['total_participants'] }}</div>
                        <div class="stat-label">Total Peserta Mudik</div>
                    </div>
                </div>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Jumlah peserta yang disetujui (1 pendaftaran = 1-6 peserta)
                </p>
            </div>
        </div>
    </div>

    {{-- Section 4: Quota per Destination --}}
    <div class="table-card mb-4">
        <h5 class="section-title">
            <i class="bi bi-diagram-3-fill me-2"></i>Kuota Per Tujuan
        </h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tujuan</th>
                        <th class="text-center">Total Kuota</th>
                        <th class="text-center">Terpakai</th>
                        <th class="text-center">Sisa</th>
                        <th style="width: 200px;">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statistics['quotas'] as $quota)
                        <tr>
                            <td>
                                <strong>{{ $quota['name'] }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ number_format($quota['total_quota']) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ number_format($quota['used_quota']) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $quota['remaining_quota'] > 0 ? 'success' : 'danger' }}">
                                    {{ number_format($quota['remaining_quota']) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar bg-{{ $quota['percentage_used'] >= 90 ? 'danger' : ($quota['percentage_used'] >= 70 ? 'warning' : 'success') }}"
                                            role="progressbar" style="width: {{ min($quota['percentage_used'], 100) }}%"
                                            aria-valuenow="{{ $quota['percentage_used'] }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted"
                                        style="min-width: 45px;">{{ number_format($quota['percentage_used'], 1) }}%</small>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada data tujuan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 5: Daily Statistics (Hari Ini) --}}
    <div class="table-card">
        <h5 class="section-title">
            <i class="bi bi-calendar-check-fill me-2"></i>Statistik Hari Ini
            <small class="text-muted fw-normal">({{ \Carbon\Carbon::today()->isoFormat('dddd, D MMMM Y') }})</small>
        </h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tujuan</th>
                        <th class="text-center">Pendaftaran Masuk</th>
                        <th class="text-center">Peserta Disetujui</th>
                        <th class="text-center">Approval Hari Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statistics['daily_stats'] as $stat)
                        <tr>
                            <td><strong>{{ $stat['destination_name'] }}</strong></td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $stat['registrations_today'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $stat['participants_today'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $stat['approved_today'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada aktivitas hari ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
