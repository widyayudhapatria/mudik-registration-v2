@extends('layouts.cms')

@section('title', 'Fix Email Log Status')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Fix Email Log Status (Sent → Failed)
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="fas fa-exclamation-circle"></i> Error!</strong> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong><i class="fas fa-check-circle"></i> Success!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Guidance Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-warning" role="alert">
                                <h6 class="font-weight-bold mb-3">
                                    <i class="fas fa-info-circle"></i> Panduan Penggunaan
                                </h6>
                                <ul class="mb-0">
                                    <li><strong>Format File:</strong> .xlsx atau .xls (max 5MB)</li>
                                    <li><strong>Kolom Wajib:</strong> "email" (satu kolom saja)</li>
                                    <li><strong>Fungsi:</strong>
                                        <ul>
                                            <li>Mengubah status email log dari "sent" → "failed"</li>
                                            <li>Memindahkan waktu sent_at ke failed_at</li>
                                            <li>Menambahkan error message: "SMTP limit reached"</li>
                                        </ul>
                                    </li>
                                    <li><strong>Syarat Email yang Bisa Diubah:</strong>
                                        <ul>
                                            <li>Status email log terakhir = <span class="badge bg-success">SENT</span></li>
                                            <li>Status form_link = <span class="badge bg-success">PENDING</span></li>
                                            <li>Form link tidak boleh <span class="badge bg-info">SUBMITTED</span>, <span class="badge bg-primary">APPROVED</span>, atau <span class="badge bg-danger">REJECTED</span></li>
                                        </ul>
                                    </li>
                                    <li><strong>Proses:</strong>
                                        <ul>
                                            <li>Sistem akan mencari email log terakhir untuk setiap email</li>
                                            <li>Hanya email dengan status SENT dan form_link PENDING yang akan diupdate</li>
                                            <li>Email dengan status/kondisi lain akan dilewati</li>
                                        </ul>
                                    </li>
                                </ul>
                                 <!-- Template Download -->
                                <div class="mt-4 text-center">
                                    <a href="{{ route('cms.email-log-fix.download-template') }}" class="btn btn-light btn-sm">
                                        <i class="bi bi-download"></i> Download Template Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Import Form -->
                        <div class="col-md-6">
                            <div class="alert alert-danger" role="alert">
                                <h6 class="font-weight-bold">
                                    <i class="fas fa-exclamation-triangle"></i> PERINGATAN
                                </h6>
                                <p class="mb-0">
                                    Fitur ini mengubah data email log secara permanen.
                                    Pastikan file Excel berisi email yang benar-benar
                                    gagal terkirim karena SMTP limit.
                                </p>
                            </div>

                            <form id="importForm" method="POST" action="{{ route('cms.email-log-fix.validate') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="file" class="font-weight-bold">
                                        File Excel <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('file') is-invalid @enderror"
                                            id="file" name="file" accept=".xlsx,.xls,.csv" required>
                                        <label class="custom-file-label" for="file" id="fileName">
                                            Choose file...
                                        </label>
                                        @error('file')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted mt-2">
                                        Format: .xlsx, .xls, atau .csv | Ukuran max: 5MB
                                    </small>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-danger" id="submitBtn">
                                        <i class="bi bi-upload"></i> Upload & Validate
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                                    </button>
                                </div>
                            </form>

                            <!-- Loading State -->
                            <div id="loadingState" style="display: none;" class="text-center mt-3">
                                <div class="spinner-border text-danger" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="mt-2">Memvalidasi data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Validasi Gagal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="errorMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Validasi Berhasil - Preview Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body bg-light">
                                <h6 class="card-title">Summary</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Total Emails:</strong> <span id="previewTotalEmails" class="badge bg-primary">0</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Can Fix (Sent):</strong> <span id="previewCanFix" class="badge bg-success">0</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Already Failed:</strong> <span id="previewAlreadyFailed" class="badge bg-secondary">0</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>No Log:</strong> <span id="previewNoLog" class="badge bg-warning">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info" role="alert">
                    <h6 class="font-weight-bold"><i class="fas fa-info-circle"></i> Preview Data (10 pertama)</h6>
                    <p class="mb-0">Berikut adalah contoh email dan status email log terakhir mereka:</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="previewTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Email</th>
                                <th width="12%">Email Status</th>
                                <th width="12%">FormLink Status</th>
                                <th width="15%">Sent At</th>
                                <th width="10%">Log ID</th>
                                <th width="21%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody"></tbody>
                    </table>
                </div>

                <p class="text-muted" id="hasMoreMessage" style="display: none;">
                    <i class="fas fa-info-circle"></i> Menampilkan 10 data pertama. Akan ada lebih banyak data yang diproses.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="proceedBtn">
                    <i class="bi bi-arrow-right"></i> Lanjut ke Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file');
    const fileNameLabel = document.getElementById('fileName');
    const importForm = document.getElementById('importForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingState = document.getElementById('loadingState');
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    const errorMessage = document.getElementById('errorMessage');

    // Update file name label
    fileInput.addEventListener('change', function() {
        const fileName = this.value.split('\\').pop();
        fileNameLabel.textContent = fileName || 'Choose file...';
    });

    // Form submission
    importForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        submitBtn.disabled = true;
        loadingState.style.display = 'block';

        axios.post('{{ route("cms.email-log-fix.validate") }}', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            if (response.data.success) {
                showPreviewModal(response.data);
            }
        })
        .catch(error => {
            const errMsg = error.response?.data?.error || 'Terjadi kesalahan saat validasi';
            errorMessage.textContent = errMsg;
            errorModal.show();
        })
        .finally(() => {
            submitBtn.disabled = false;
            loadingState.style.display = 'none';
        });
    });

    // Show preview modal
    function showPreviewModal(response) {
        document.getElementById('previewTotalEmails').textContent = response.preview.total_emails;
        document.getElementById('previewCanFix').textContent = response.preview.status_counts.can_fix;
        document.getElementById('previewAlreadyFailed').textContent = response.preview.status_counts.already_failed;
        document.getElementById('previewNoLog').textContent = response.preview.status_counts.no_log;

        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        response.email_statuses.forEach((item, index) => {
            const statusBadge = getStatusBadge(item.latest_status);
            const formLinkBadge = getFormLinkBadge(item.form_link_status);
            const actionContent = item.can_fix
                ? '<span class="badge bg-success">Will Fix</span>'
                : `<span class="badge bg-secondary" title="${item.skip_reason || 'Cannot fix'}">Skip</span>`;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td><small>${item.email}</small></td>
                    <td>${statusBadge}</td>
                    <td>${formLinkBadge}</td>
                    <td><small>${item.sent_at || '-'}</small></td>
                    <td><small>${item.email_log_id || '-'}</small></td>
                    <td>${actionContent}</td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        if (response.has_more) {
            document.getElementById('hasMoreMessage').style.display = 'block';
        }

        previewModal.show();
    }

    function getStatusBadge(status) {
        const badges = {
            'sent': '<span class="badge bg-success">SENT</span>',
            'failed': '<span class="badge bg-danger">FAILED</span>',
            'pending': '<span class="badge bg-warning">PENDING</span>',
            'no_log': '<span class="badge bg-secondary">NO LOG</span>'
        };
        return badges[status] || `<span class="badge bg-secondary">${status.toUpperCase()}</span>`;
    }

    function getFormLinkBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-success">PENDING</span>',
            'submitted': '<span class="badge bg-info">SUBMITTED</span>',
            'approved': '<span class="badge bg-primary">APPROVED</span>',
            'rejected': '<span class="badge bg-danger">REJECTED</span>',
            'no_form_link': '<span class="badge bg-secondary">NO LINK</span>'
        };
        return badges[status] || `<span class="badge bg-secondary">${status ? status.toUpperCase() : 'N/A'}</span>`;
    }

    // Proceed to preview page
    document.getElementById('proceedBtn').addEventListener('click', function() {
        window.location.href = '{{ route("cms.email-log-fix.preview") }}';
    });

    // Reset form
    const resetBtn = importForm.querySelector('button[type="reset"]');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            fileNameLabel.textContent = 'Choose file...';
        });
    }
});
</script>
@endpush
