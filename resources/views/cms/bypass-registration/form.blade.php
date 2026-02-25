@extends('layouts.cms')

@section('title', 'Import Bypass Registrations')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-upload"></i> Import Bypass Registrations
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Guidance Section -->
                    <div class="row">
                        <div class="col-6">
                            <div class="alert alert-info" role="alert">
                                <h6 class="font-weight-bold mb-3">
                                    <i class="fas fa-info-circle"></i> Panduan Import Data Bypass
                                </h6>
                                <ul class="mb-0">
                                    <li><strong>Format File:</strong> .xlsx atau .xls (max 5MB)</li>
                                    <li><strong>Aturan Data:</strong>
                                        <ul>
                                            <li>Email harus UNIK (tidak sama dengan email terdaftar)</li>
                                            <li>Email antara baris HARUS BERBEDA (1 rep = 1 email)</li>
                                            <li>NIK representative & peserta harus 16 digit</li>
                                            <li>Jumlah peserta = family_count</li>
                                            <li>Tanggal format: YYYY-MM-DD</li>
                                        </ul>
                                    </li>
                                    <li><strong>Validasi:</strong>
                                        <ul>
                                            <li>✓ Email belum terdaftar (global check)</li>
                                            <li>✓ NIK peserta belum ada di destination ini</li>
                                            <li>✓ NIK representative belum ada (per destination)</li>
                                            <li>✓ Data type & format sesuai requirement</li>
                                        </ul>
                                    </li>
                                </ul>
                                 <!-- Template Download -->
                                <div class="mt-4 text-center">
                                    <a href="{{ route('cms.bypass-registrations.download-template') }}" class="btn btn-light btn-sm">
                                        <i class="bi bi-download"></i> Download Template Excel
                                    </a>
                                    <a href="{{ route('cms.bypass-registrations.history') }}" class="btn btn-light btn-sm">
                                        <i class="bi bi-clock-history"></i> Lihat History Import
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Import Form -->
                        <div class="col-6">
                            <form id="importForm" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="destination_id" class="font-weight-bold">
                                        Destination <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('destination_id') is-invalid @enderror"
                                            id="destination_id" name="destination_id" required>
                                        <option value="">-- Pilih Destination --</option>
                                        @foreach($destinations as $destination)
                                            <option value="{{ $destination->id }}">
                                                {{ $destination->name }} (quota: {{ $destination->total_quota }} orang - remaining: {{ $destination->remaining_quota }} orang)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('destination_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Destinasi tidak bisa diubah setelah upload file
                                    </small>
                                </div>

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
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="bi bi-upload"></i> Upload for Validation
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                                    </button>
                                </div>
                            </form>

                            <!-- Loading State -->
                            <div id="loadingState" style="display: none;" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">...</span>
                                </div>
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
            <div class="modal-header">
                <h5 class="modal-title" id="errorModal">Validasi Gagal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="errorMessage"></p>
                <div id="errorDetails" class="mt-3" style="display: none;">
                    <h6 class="font-weight-bold">Error Details:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped" id="errorTable">
                            <thead>
                                <tr>
                                    <th>Row</th>
                                    <th>Type</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody id="errorTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Validasi Berhasil - Preview Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body bg-warning text-dark">
                                <h6 class="card-title">Summary Peserta</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Registrations:</strong> <span id="previewRegCount">0</span></li>
                                    <li><strong>Total Peserta:</strong> <span id="previewPartCount">0</span></li>
                                    <li class="mt-2 border-top pt-2">
                                        <strong>Breakdown:</strong>
                                        <ul class="list-unstyled mb-0 pl-3">
                                            <li><strong>Dewasa (≥4 tahun):</strong> <span id="previewAdultCount">0</span></li>
                                            <li><strong>Anak (<4 tahun):</strong> <span id="previewUnder4Count">0</span></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body bg-info text-white">
                                <h6 class="card-title">Dampak Quota (Dewasa Saja)</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><strong>Total Quota:</strong> <span id="quotaTotal">-</span></li>
                                    <li><strong>Terpakai:</strong> <span id="quotaUsed">-</span></li>
                                    <li><strong>Tersisa:</strong> <span id="quotaRemaining">-</span></li>
                                    <li class="mt-2 border-top pt-2">
                                        <strong>Akan Digunakan:</strong> <span id="quotaWouldUse">-</span>
                                    </li>
                                    <li><strong>Sisa Setelah Import:</strong> <span id="quotaAfter">-</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <h6 class="font-weight-bold">Data Preview (First 5 Registrations)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped" id="previewTable">
                            <thead>
                                <tr>
                                    <th>Rep Name</th>
                                    <th>Email</th>
                                    <th>Total Peserta</th>
                                    <th>Dewasa (≥4th)</th>
                                    <th>Anak (<4th)</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div id="warningsSection" style="display: none;" class="alert alert-danger mt-3">
                    <h6 class="alert-heading">⚠️ Data Warnings - Import Tidak Dapat Dilanjutkan</h6>
                    <p class="mb-2"><strong>Data memiliki warnings yang harus diperbaiki sebelum import:</strong></p>
                    <ul id="warningsList" class="mb-2"></ul>
                    <p class="mb-0"><em>Silakan perbaiki file Excel dan upload ulang.</em></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="proceedBtn" disabled>
                    <i class="bi bi-check-circle"></i> Proceed to Confirmation
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Ensure Bootstrap is available
if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap is required for this page');
}

// Enable/disable submit button based on form state
function updateSubmitButtonState() {
    const submitBtn = document.getElementById('submitBtn');
    const destinationId = document.getElementById('destination_id').value;
    const fileInput = document.getElementById('file');

    // Enable button only if both destination and file are selected
    submitBtn.disabled = !destinationId || !fileInput.files.length;
}

// File input change handler
document.getElementById('file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Choose file...';
    document.getElementById('fileName').textContent = fileName;
    updateSubmitButtonState();

    // Reset proceed button to disabled when new file selected
    const proceedBtn = document.getElementById('proceedBtn');
    proceedBtn.disabled = true;
});

// Destination change handler
document.getElementById('destination_id').addEventListener('change', function(e) {
    updateSubmitButtonState();

    // Reset proceed button to disabled when destination changed
    const proceedBtn = document.getElementById('proceedBtn');
    proceedBtn.disabled = true;
});

// Form submission
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    const loadingState = document.getElementById('loadingState');
    const proceedBtn = document.getElementById('proceedBtn');
    const form = this;

    submitBtn.disabled = true;
    loadingState.style.display = 'block';

    // Reset proceed button at start of validation
    proceedBtn.disabled = true;

    fetch('{{ route("cms.bypass-registrations.validate") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw {response: data};
            });
        }
        return response.json();
    })
    .then(data => {
        loadingState.style.display = 'none';
        showPreviewModal(data);
    })
    .catch(error => {
        loadingState.style.display = 'none';
        // Keep button DISABLED if validation fails - user must select new file

        if (error.response) {
            showErrorModal(error.response);
        } else {
            alert('Error: ' + (error.message || 'Unknown error'));
        }
    });
});

function showErrorModal(data) {
    const modal = document.getElementById('errorModal');
    const errorMessage = document.getElementById('errorMessage');
    const errorDetails = document.getElementById('errorDetails');
    const errorTableBody = document.getElementById('errorTableBody');
    const proceedBtn = document.getElementById('proceedBtn');

    errorMessage.textContent = data.error || 'Validasi gagal';

    if (data.validation_errors && data.validation_errors.length > 0) {
        errorTableBody.innerHTML = '';
        data.validation_errors.forEach(error => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${error.row || '-'}</td>
                <td><small>${error.type || error.column || '-'}</small></td>
                <td>${error.message || ''}</td>
            `;
            errorTableBody.appendChild(row);
        });
        errorDetails.style.display = 'block';
    }

    // Ensure proceed button is DISABLED when validation fails
    proceedBtn.disabled = true;

    // Use vanilla Bootstrap modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}

function showPreviewModal(data) {
    console.log('Preview data received:', data); // Debug log for preview data
    const previewTable = document.getElementById('previewTableBody');
    const previewRegCount = document.getElementById('previewRegCount');
    const previewPartCount = document.getElementById('previewPartCount');
    const previewAdultCount = document.getElementById('previewAdultCount');
    const previewUnder4Count = document.getElementById('previewUnder4Count');
    const quotaInfo = data.quota || {};
    const proceedBtn = document.getElementById('proceedBtn');

    // Check if data is valid before showing preview
    if (!data.preview || !data.registrations || data.registrations.length === 0) {
        alert('Error: Invalid preview data received');
        proceedBtn.disabled = true;
        return;
    }

    previewRegCount.textContent = data.preview.total_registrations;
    previewPartCount.textContent = data.preview.total_participants;
    previewAdultCount.textContent = data.preview.total_participants_for_quota;
    previewUnder4Count.textContent = data.preview.total_under_4;

    // Calculate quota after import as (remaining - would_use)
    const total = quotaInfo.total ?? '-';
    const used = quotaInfo.used ?? '-';
    const remainingRaw = quotaInfo.remaining;
    const wouldUseRaw = quotaInfo.would_use;
    const afterImportRaw = quotaInfo.after_import;

    const remainingVal = (remainingRaw === undefined || remainingRaw === null) ? null : Number(remainingRaw);
    const wouldUseVal = (wouldUseRaw === undefined || wouldUseRaw === null) ? null : Number(wouldUseRaw);
    const afterImportVal = (afterImportRaw === undefined || afterImportRaw === null) ? null : Number(afterImportRaw);

    const canCalc = remainingVal !== null && !isNaN(remainingVal) && wouldUseVal !== null && !isNaN(wouldUseVal);
    const quotaAfterCalc = canCalc ? (remainingVal - wouldUseVal) : null;

    // Display quota info with subtraction expression when possible
    document.getElementById('quotaTotal').textContent = total || '-';
    document.getElementById('quotaUsed').textContent = used || '-';
    document.getElementById('quotaRemaining').textContent = (remainingRaw ?? '-');
    document.getElementById('quotaWouldUse').textContent = wouldUseRaw !== undefined ? wouldUseRaw : '-';
    document.getElementById('quotaAfter').textContent = canCalc ? `${quotaAfterCalc} (${remainingVal} - ${wouldUseVal})` : '-';

    previewTable.innerHTML = '';
    (data.registrations || []).forEach(reg => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${reg.representative_name}</td>
            <td>${reg.representative_email}</td>
            <td>${reg.family_count} orang</td>
            <td><strong>${reg.adult_count}</strong></td>
            <td><span class="badge badge-info text-bg-secondary">${reg.under_4_count}</span></td>
        `;
        previewTable.appendChild(row);
    });

    // Handle warnings display and button state
    const existingWarnings = (data.preview.warnings && data.preview.warnings.length) ? data.preview.warnings.slice() : [];
    let hasWarnings = existingWarnings.length > 0;

    if (canCalc && quotaAfterCalc < 0) {
        hasWarnings = true;
        existingWarnings.push({
            message: `Quota insufficient: ${quotaAfterCalc} = (${remainingVal}-${wouldUseVal})`
        });
    }

    if (hasWarnings) {
        const warningsList = document.getElementById('warningsList');
        warningsList.innerHTML = '';
        existingWarnings.forEach(warning => {
            const li = document.createElement('li');
            li.textContent = warning.message || JSON.stringify(warning);
            warningsList.appendChild(li);
        });
        document.getElementById('warningsSection').style.display = 'block';

        // Warnings BLOCK import - button stays disabled permanently
        proceedBtn.disabled = true;
    } else {
        document.getElementById('warningsSection').style.display = 'none';

        // No warnings: enable button immediately
        proceedBtn.disabled = false;
    }

    // Use vanilla Bootstrap modal
    const previewModal = document.getElementById('previewModal');
    const bootstrapModal = new bootstrap.Modal(previewModal);
    bootstrapModal.show();
}

document.getElementById('proceedBtn').addEventListener('click', function() {
    window.location.href = '{{ route("cms.bypass-registrations.preview") }}';
});

// Initialize button state on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSubmitButtonState();
});
</script>
@endpush
@endsection
