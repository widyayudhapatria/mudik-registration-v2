@extends('layouts.cms')

@section('title', 'Manajemen Kuota Harian - CMS Admin')
@section('page-title', 'Manajemen Kuota Harian')

@push('styles')
<style>
    .calendar-header {
        background: white;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .date-selector {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }
    .quota-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .quota-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-top: 4px solid transparent;
    }
    .quota-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    .quota-card.today {
        border-top-color: #2196F3;
        background: linear-gradient(to bottom, #e3f2fd 0%, #ffffff 100%);
    }
    .quota-card.full {
        border-top-color: #f44336;
    }
    .quota-card.available {
        border-top-color: #4CAF50;
    }
    .quota-card.empty {
        border-top-color: #9E9E9E;
        opacity: 0.7;
    }
    .quota-date {
        font-size: 14px;
        color: #757575;
        margin-bottom: 8px;
    }
    .quota-day {
        font-size: 24px;
        font-weight: 800;
        color: #212121;
        margin-bottom: 15px;
    }
    .quota-stats {
        margin: 20px 0;
    }
    .quota-bar {
        height: 8px;
        background-color: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
        margin: 10px 0;
    }
    .quota-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #4CAF50 0%, #2E7D32 100%);
        transition: width 0.3s ease;
    }
    .quota-bar-fill.warning {
        background: linear-gradient(90deg, #FF9800 0%, #F57C00 100%);
    }
    .quota-bar-fill.danger {
        background: linear-gradient(90deg, #f44336 0%, #d32f2f 100%);
    }
    .quota-numbers {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #757575;
        margin-top: 8px;
    }
    .quota-total {
        font-size: 18px;
        font-weight: 700;
        color: #212121;
        margin-top: 15px;
    }
    .quota-label {
        font-size: 13px;
        color: #757575;
    }
    .edit-quota-btn {
        width: 100%;
        margin-top: 15px;
        border-radius: 8px;
        padding: 10px;
        font-weight: 600;
    }
    .summary-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .summary-item {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 12px;
    }
    .summary-value {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 8px;
    }
    .summary-label {
        font-size: 14px;
        color: #757575;
    }
    .modal-body {
        padding: 30px;
    }
    .form-floating > .form-control:focus {
        border-color: #4CAF50;
        box-shadow: 0 0 0 0.25rem rgba(76, 175, 80, 0.25);
    }
    @media (max-width: 768px) {
        .quota-grid {
            grid-template-columns: 1fr;
        }
        .date-selector {
            flex-direction: column;
            align-items: stretch;
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
                <label class="form-label small fw-semibold">Dari Tanggal</label>
                <input 
                    type="date" 
                    name="start_date" 
                    class="form-control" 
                    value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}"
                    onchange="this.form.submit()"
                >
            </div>
            <div class="flex-grow-1">
                <label class="form-label small fw-semibold">Sampai Tanggal</label>
                <input 
                    type="date" 
                    name="end_date" 
                    class="form-control" 
                    value="{{ request('end_date', now()->endOfMonth()->toDateString()) }}"
                    onchange="this.form.submit()"
                >
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
            <div class="summary-label">Hari dengan Kuota</div>
        </div>
        <div class="summary-item">
            <div class="summary-value" style="color: #4CAF50;">{{ $quotas->sum('quota') }}</div>
            <div class="summary-label">Total Kuota</div>
        </div>
        <div class="summary-item">
            <div class="summary-value" style="color: #FF9800;">{{ $quotas->sum('used') }}</div>
            <div class="summary-label">Sudah Terpakai</div>
        </div>
        <div class="summary-item">
            <div class="summary-value" style="color: #9C27B0;">{{ $quotas->sum('remaining') }}</div>
            <div class="summary-label">Sisa Kuota</div>
        </div>
    </div>
</div>

<!-- Quota Grid -->
<div class="quota-grid">
    @forelse($quotas as $quota)
    @php
        $isToday = $quota->date->isToday();
        $percentage = $quota->quota > 0 ? ($quota->used / $quota->quota) * 100 : 0;
        $cardClass = 'available';
        
        if ($isToday) {
            $cardClass = 'today';
        } elseif ($percentage >= 100) {
            $cardClass = 'full';
        } elseif ($quota->quota == 0) {
            $cardClass = 'empty';
        }
        
        $barClass = '';
        if ($percentage >= 80) {
            $barClass = 'danger';
        } elseif ($percentage >= 60) {
            $barClass = 'warning';
        }
    @endphp
    
    <div class="quota-card {{ $cardClass }}">
        <div class="quota-date">
            {{ $quota->date->isoFormat('dddd') }}
            @if($isToday)
            <span class="badge bg-primary ms-2">Hari Ini</span>
            @endif
        </div>
        <div class="quota-day">
            {{ $quota->date->format('d') }}
            <span style="font-size: 16px; font-weight: 600; color: #757575;">
                {{ $quota->date->isoFormat('MMMM YYYY') }}
            </span>
        </div>
        
        <div class="quota-stats">
            <div class="quota-bar">
                <div class="quota-bar-fill {{ $barClass }}" style="width: {{ min($percentage, 100) }}%"></div>
            </div>
            <div class="quota-numbers">
                <span>Terpakai: <strong>{{ $quota->used }}</strong></span>
                <span>Sisa: <strong>{{ $quota->remaining }}</strong></span>
            </div>
        </div>
        
        <div class="quota-total">
            {{ number_format($quota->quota) }}
            <span class="quota-label">Total Kuota</span>
        </div>
        
        <button 
            class="btn btn-sm btn-outline-primary edit-quota-btn" 
            onclick="editQuota('{{ $quota->date->toDateString() }}', {{ $quota->quota }})"
        >
            <i class="bi bi-pencil me-1"></i>Edit Kuota
        </button>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5" style="background: white; border-radius: 16px;">
            <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
            <p class="text-muted">Tidak ada kuota yang diatur untuk periode ini</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuotaModal">
                <i class="bi bi-plus-circle me-2"></i>Set Kuota Sekarang
            </button>
        </div>
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
                    <div class="form-floating mb-3">
                        <input 
                            type="date" 
                            class="form-control" 
                            id="quota_date" 
                            name="date"
                            required
                        >
                        <label for="quota_date">
                            <i class="bi bi-calendar3 me-2"></i>Tanggal
                        </label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input 
                            type="number" 
                            class="form-control" 
                            id="quota_amount" 
                            name="quota"
                            min="0"
                            required
                        >
                        <label for="quota_amount">
                            <i class="bi bi-123 me-2"></i>Jumlah Kuota
                        </label>
                        <div class="form-text">Masukkan 0 untuk menonaktifkan kuota di tanggal ini</div>
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
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">
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

// Edit quota function
function editQuota(date, currentQuota) {
    document.getElementById('modalTitle').textContent = 'Edit Kuota Harian';
    document.getElementById('quota_date').value = date;
    document.getElementById('quota_amount').value = currentQuota;
    quotaModal.show();
}

// Form submission
document.getElementById('quotaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
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
        
        const response = await axios.post('{{ route("cms.quotas.store") }}', data);
        
        if (response.data.success) {
            alert('Kuota berhasil disimpan!');
            location.reload();
        }
    } catch (error) {
        let errorMessage = 'Gagal menyimpan kuota';
        
        if (error.response && error.response.data) {
            errorMessage = error.response.data.message || errorMessage;
            
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
});

// Auto-refresh every 30 seconds for today's quota
@if($quotas->contains(fn($q) => $q->date->isToday()))
setInterval(() => {
    // Refresh page to get latest data
    location.reload();
}, 30000);
@endif
</script>
@endpush