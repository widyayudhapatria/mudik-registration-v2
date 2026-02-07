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
    }
    .btn-action {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<!-- Filters -->
<div class="filter-card">
    <form method="GET" id="filterForm">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Tanggal Dari</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Tanggal Sampai</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            
            <div class="col-md-3">
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

<!-- Table -->
<div class="table-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">
            Daftar Pendaftaran ({{ $registrations->total() }})
        </h5>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Perwakilan</th>
                    <th>NIK</th>
                    <th>No. KK</th>
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
                    <td>{{ $registration->kk_number }}</td>
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