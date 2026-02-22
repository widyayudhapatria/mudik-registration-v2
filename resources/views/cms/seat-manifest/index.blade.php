@extends('layouts.cms')

@section('title', 'Manifest Kursi')
@section('page-title', 'Manifest Kursi Peserta')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <p class="text-muted mb-0">Daftar alokasi kursi peserta mudik per tujuan dan bus</p>
</div>

{{-- Filter Card --}}
<div class="card mb-4" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06);">
    <div class="card-body">
        <form method="GET" action="{{ route('cms.seat-manifest.index') }}" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Kota Tujuan</label>
                    <select name="destination_id" class="form-select" id="destinationSelect">
                        <option value="">-- Semua Tujuan --</option>
                        @foreach($destinations as $dest)
                            <option value="{{ $dest->id }}"
                                {{ $selectedDestinationId == $dest->id ? 'selected' : '' }}>
                                {{ $dest->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nomor Bus</label>
                    <select name="bus_number" class="form-select" id="busSelect"
                        {{ !$selectedDestinationId ? 'disabled' : '' }}>
                        <option value="">-- Semua Bus --</option>
                        @foreach($availableBuses as $bus)
                            <option value="{{ $bus }}"
                                {{ $selectedBusNumber == $bus ? 'selected' : '' }}>
                                @if($selectedDestinationId)
                                    {{ $destinations->firstWhere('id', $selectedDestinationId)?->code }}-{{ $bus }}
                                @else
                                    Bus {{ $bus }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
@if($summary)
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-radius: 12px; border-left: 4px solid #2E7D32;">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-success">{{ $summary['total_seats'] }}</div>
                <div class="small text-muted">Total Kursi Terisi</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-radius: 12px; border-left: 4px solid #1565C0;">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-primary">{{ $summary['total_buses'] }}</div>
                <div class="small text-muted">Jumlah Bus</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-radius: 12px; border-left: 4px solid #E65100;">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold text-warning">{{ $summary['no_seat_count'] }}</div>
                <div class="small text-muted">Dipangku (Balita)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-radius: 12px; border-left: 4px solid #6A1B9A;">
            <div class="card-body py-3">
                <div class="fs-2 fw-bold" style="color: #6A1B9A;">
                    {{ $summary['total_seats'] + $summary['no_seat_count'] }}
                </div>
                <div class="small text-muted">Total Peserta Scan</div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Table --}}
<div class="card" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06);">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"
        style="border-radius: 12px 12px 0 0;">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-grid-3x3-gap me-2"></i>
            Daftar Kursi
            @if($seatAllocations->count() > 0)
                <span class="badge bg-secondary ms-1">{{ $seatAllocations->count() }}</span>
            @endif
        </h6>
        @if($seatAllocations->count() > 0)
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print
        </button>
        @endif
    </div>
    <div class="card-body p-0">
        @if($seatAllocations->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                @if(!$selectedDestinationId)
                    Pilih kota tujuan untuk melihat manifest kursi.
                @else
                    Belum ada data kursi untuk filter ini.
                @endif
            </div>
        @else
            {{-- Group by bus --}}
            @php
                $grouped = $seatAllocations->groupBy('bus_number');
            @endphp

            @foreach($grouped as $busNumber => $seats)
            @php
                $destCode = $seats->first()->destination->code;
                $busName = $destCode . '-' . $busNumber;
            @endphp
            <div class="border-bottom">
                {{-- Bus Header --}}
                <div class="px-4 py-2 d-flex justify-content-between align-items-center"
                    style="background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);">
                    <span class="text-white fw-bold">
                        <i class="bi bi-truck-front me-2"></i>Bus {{ $busName }}
                    </span>
                    <span class="badge bg-light text-dark">{{ $seats->count() }} / 50 kursi</span>
                </div>

                {{-- Seat Table --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;" class="ps-4">Kursi</th>
                                <th>Nama Peserta</th>
                                <th>Kode Kursi</th>
                                <th>Perwakilan KK</th>
                                <th>No. KK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seats->sortBy('seat_number') as $seat)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-success">{{ $seat->seat_number }}</span>
                                </td>
                                <td class="fw-semibold">{{ $seat->participant->full_name }}</td>
                                <td>
                                    <span class="badge bg-dark">{{ $seat->seat_code }}</span>
                                </td>
                                <td class="text-muted small">
                                    {{ $seat->registration->representative_name }}
                                </td>
                                <td class="text-muted small font-monospace">
                                    {{ $seat->registration->kk_number }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .sidebar, .topbar, form, .btn { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-reset bus filter when destination changes
    document.getElementById('destinationSelect').addEventListener('change', function() {
        const busSelect = document.getElementById('busSelect');
        busSelect.value = '';
        busSelect.disabled = !this.value;
        document.getElementById('filterForm').submit();
    });
</script>
@endpush