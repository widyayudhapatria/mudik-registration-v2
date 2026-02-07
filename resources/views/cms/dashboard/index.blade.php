@extends('layouts.cms')

@section('title', 'Dashboard - CMS Admin')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
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
    .recent-scans {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .scan-item {
        padding: 15px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .scan-item:last-child {
        border-bottom: none;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); color: white;">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="stat-value">{{ $statistics['email_submissions']['total'] }}</div>
            <div class="stat-label">Total Email Submit</div>
            <div class="small text-success mt-2">
                <i class="bi bi-arrow-up-circle-fill me-1"></i>
                +{{ $statistics['email_submissions']['today'] }} hari ini
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); color: white;">
                <i class="bi bi-file-text-fill"></i>
            </div>
            <div class="stat-value">{{ $statistics['registrations']['total'] }}</div>
            <div class="stat-label">Total Pendaftaran</div>
            <div class="small text-success mt-2">
                <i class="bi bi-arrow-up-circle-fill me-1"></i>
                +{{ $statistics['registrations']['today'] }} hari ini
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%); color: white;">
                <i class="bi bi-clock-fill"></i>
            </div>
            <div class="stat-value">{{ $statistics['registrations']['pending'] }}</div>
            <div class="stat-label">Menunggu Approval</div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white;">
                <i class="bi bi-qr-code-scan"></i>
            </div>
            <div class="stat-value">{{ $statistics['qr_codes']['total_scanned'] }}</div>
            <div class="stat-label">QR Code Di-scan</div>
        </div>
    </div>
</div>

<div class="recent-scans">
    <h5 class="fw-bold mb-4">
        <i class="bi bi-activity me-2"></i>Scan Terbaru
    </h5>
    
    <div id="recentScans">
        @forelse($statistics['recent_scans'] as $scan)
        <div class="scan-item">
            <div>
                <div class="fw-semibold">{{ $scan['representative_name'] }}</div>
                <div class="small text-muted">
                    <i class="bi bi-person me-1"></i>{{ $scan['admin_name'] }}
                    <span class="mx-2">•</span>
                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($scan['scanned_at'])->diffForHumans() }}
                </div>
            </div>
            <div>
                @if($scan['scan_result'] === 'success')
                <span class="badge bg-success">Success</span>
                @else
                <span class="badge bg-danger">Failed</span>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <p>Belum ada scan terbaru</p>
        </div>
        @endforelse
    </div>
</div>
@endsection