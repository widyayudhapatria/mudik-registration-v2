@extends('layouts.cms')

@section('title', 'Manajemen Pendaftaran - CMS Admin')
@section('page-title', 'Manajemen Pendaftaran')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .table-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
                <label class="form-label fw-semibold small">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="col-md-3 col-12">
                <label class="form-label fw-semibold small">Tujuan</label>
                <select name="destination_id" class="form-select">
                    <option value="">Semua Tujuan</option>
                    @foreach($destinations as $destination)
                    <option value="{{ $destination->id }}" {{ request('destination_id') == $destination->id ? 'selected' : '' }}>
                        {{ $destination->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3 col-sm-6 col-12">
                <label class="form-label fw-semibold small">Tanggal Dari</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            
            <div class="col-md-3 col-sm-6 col-12">
                <label class="form-label fw-semibold small">Tanggal Sampai</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            
            <div class="col-md-3 col-12">
                <label class="form-label fw-semibold small">Cari</label>
                <input type="text" name="search" class="form-control" placeholder="Nama/NIK/KK" value="{{ request('search') }}">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i>Filter
                </button>
                <a href="{{ route('cms.registrations.index') }}" class="btn btn-outline-secondary">
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
            Daftar Pendaftaran ({{ $registrations->total() }})
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
                        <th>NIK</th>
                        <th>Tujuan</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $registration)
                    <tr>
                        <td>{{ $registration->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $registration->representative_name }}</div>
                            <div class="small text-muted">{{ $registration->formLink->email }}</div>
                        </td>
                        <td>{{ $registration->representative_nik }}</td>
                        <td>{{ $registration->destination->name }}</td>
                        <td>
                            <span class="badge bg-info">{{ $registration->family_count }} orang</span>
                        </td>
                        <td>{{ $registration->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($registration->isApproved())
                            <span class="status-badge bg-success text-white">
                                <i class="bi bi-check-circle-fill me-1"></i>Disetujui
                            </span>
                            @elseif($registration->isRejected())
                            <span class="status-badge bg-danger text-white">
                                <i class="bi bi-x-circle-fill me-1"></i>Ditolak
                            </span>
                            @else
                            <span class="status-badge bg-warning text-dark">
                                <i class="bi bi-clock-fill me-1"></i>Pending
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('cms.registrations.show', $registration) }}" class="btn btn-sm btn-primary btn-action">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if($registration->isPending())
                                @can('approve', $registration)
                                <button type="button" class="btn btn-sm btn-success btn-action" onclick="quickApprove({{ $registration->id }})">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endcan
                                
                                @can('reject', $registration)
                                <button type="button" class="btn btn-sm btn-danger btn-action" onclick="quickReject({{ $registration->id }})">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            Tidak ada data pendaftaran
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Mobile Cards View -->
    <div class="mobile-cards">
        @forelse($registrations as $registration)
        <div class="registration-card">
            <div class="registration-card-header">
                <div>
                    <h6 class="fw-bold mb-1">{{ $registration->representative_name }}</h6>
                    <small class="text-muted">{{ $registration->formLink->email }}</small>
                </div>
                <div>
                    @if($registration->isApproved())
                    <span class="status-badge bg-success text-white">
                        <i class="bi bi-check-circle-fill"></i> Disetujui
                    </span>
                    @elseif($registration->isRejected())
                    <span class="status-badge bg-danger text-white">
                        <i class="bi bi-x-circle-fill"></i> Ditolak
                    </span>
                    @else
                    <span class="status-badge bg-warning text-dark">
                        <i class="bi bi-clock-fill"></i> Pending
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="registration-card-body">
                <div class="info-row">
                    <span class="info-label">ID Registrasi</span>
                    <span class="info-value">#{{ $registration->id }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">NIK Perwakilan</span>
                    <span class="info-value">{{ $registration->representative_nik }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">No. Kartu Keluarga</span>
                    <span class="info-value">{{ $registration->kk_number }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Jumlah Peserta</span>
                    <span class="info-value">
                        <span class="badge bg-info">{{ $registration->family_count }} orang</span>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Tanggal Daftar</span>
                    <span class="info-value">{{ $registration->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="{{ route('cms.registrations.show', $registration) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye me-1"></i>Detail
                </a>
                
                @if($registration->isPending())
                @can('approve', $registration)
                <button type="button" class="btn btn-sm btn-success" onclick="quickApprove({{ $registration->id }})">
                    <i class="bi bi-check-lg me-1"></i>Setujui
                </button>
                @endcan
                
                @can('reject', $registration)
                <button type="button" class="btn btn-sm btn-danger" onclick="quickReject({{ $registration->id }})">
                    <i class="bi bi-x-lg me-1"></i>Tolak
                </button>
                @endcan
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p>Tidak ada data pendaftaran</p>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($registrations->hasPages())
    <div class="mt-4">
        {{ $registrations->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
async function quickApprove(id) {
    if (!confirm('Setujui pendaftaran ini?')) return;
    
    try {
        const response = await axios.post(`/cms/registrations/${id}/approve`, {
            notes: null
        });
        
        if (response.data.success) {
            alert('Pendaftaran berhasil disetujui');
            location.reload();
        }
    } catch (error) {
        alert('Gagal menyetujui pendaftaran: ' + (error.response?.data?.message || error.message));
    }
}

async function quickReject(id) {
    const reason = prompt('Alasan penolakan:');
    if (!reason) return;
    
    try {
        const response = await axios.post(`/cms/registrations/${id}/reject`, {
            rejection_reason: reason,
            notes: null
        });
        
        if (response.data.success) {
            alert('Pendaftaran berhasil ditolak');
            location.reload();
        }
    } catch (error) {
        alert('Gagal menolak pendaftaran: ' + (error.response?.data?.message || error.message));
    }
}
</script>
@endpush