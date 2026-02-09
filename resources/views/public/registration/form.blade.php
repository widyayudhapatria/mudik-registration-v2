@extends('layouts.app')

@section('title', 'Formulir Pendaftaran - Mudik Gratis Lebaran 2026')

@push('styles')
<style>
    .registration-header {
        background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
        padding: 40px 20px;
        color: white;
        text-align: center;
    }
    
    .quota-alert {
        background: linear-gradient(135deg, #FF6F00 0%, #E65100 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: var(--shadow-md);
    }
    
    .form-section {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: var(--shadow-md);
    }
    
    .form-section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--primary-color);
    }
    
    .participant-card {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 20px;
        position: relative;
    }
    
    .participant-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .participant-number {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .remove-participant {
        background: #dc3545;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .remove-participant:hover {
        background: #c82333;
        transform: scale(1.1);
    }
    
    .add-participant-btn {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--secondary-dark) 100%);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
        width: 100%;
    }
    
    .add-participant-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .file-upload-area {
        border: 2px dashed var(--border-color);
        border-radius: 16px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .file-upload-area:hover {
        border-color: var(--primary-color);
        background: #e8f5e9;
    }
    
    .file-upload-area.dragover {
        border-color: var(--primary-color);
        background: #c8e6c9;
    }
    
    .file-preview {
        margin-top: 15px;
        padding: 15px;
        background: white;
        border-radius: 12px;
        border: 2px solid var(--primary-color);
    }
    
    .checkbox-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .checkbox-card:hover {
        border-color: var(--primary-color);
        background: #f8f9fa;
    }
    
    .checkbox-card input:checked ~ label {
        color: var(--primary-dark);
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .form-section {
            padding: 20px;
        }
        
        .participant-card {
            padding: 20px 15px;
        }
    }
</style>
@endpush

@section('content')
<div class="registration-header">
    <div class="container">
        <h1 class="heading-font mb-2" style="font-size: clamp(2rem, 5vw, 3rem);">
            MUDIK GRATIS LEBARAN 2026
        </h1>
        <p class="mb-0">Formulir Pendaftaran</p>
    </div>
</div>

<div class="container py-5">
    <div id="alert-container"></div>
    
    @if(isset($dailyQuota))
    <div class="quota-alert">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
            <strong>Kuota Hari Ini:</strong> {{ $dailyQuota->remaining }} dari {{ $dailyQuota->quota }} tersisa
        </div>
    </div>
    @endif
    
    <form id="registrationForm" enctype="multipart/form-data">
        <!-- Data Perwakilan Keluarga -->
        <div class="form-section">
            <h2 class="form-section-title">
                <i class="bi bi-person-fill me-2"></i>DATA PERWAKILAN KELUARGA
            </h2>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="representative_name" class="form-label fw-semibold">
                        Nama Lengkap Perwakilan <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="representative_name" 
                        name="representative_name"
                        placeholder="Nama sesuai KTP"
                        required
                    >
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="representative_nik" class="form-label fw-semibold">
                        Nomor KTP Perwakilan <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="representative_nik" 
                        name="representative_nik"
                        placeholder="16 digit NIK"
                        maxlength="16"
                        pattern="[0-9]{16}"
                        required
                    >
                    <div class="form-text">NIK harus 16 digit angka</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="representative_birth_date" class="form-label fw-semibold">
                        Tanggal Lahir Perwakilan <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="representative_birth_date" 
                        name="representative_birth_date"
                        required
                    >
                </div>
            </div>
            
            <div class="form-check mt-3">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="addToParticipants"
                    checked
                >
                <label class="form-check-label" for="addToParticipants">
                    Tambahkan ke daftar peserta mudik
                </label>
            </div>
        </div>
        
        <!-- Data Keluarga -->
        <div class="form-section">
            <h2 class="form-section-title">
                <i class="bi bi-people-fill me-2"></i>DATA KELUARGA
            </h2>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="family_count" class="form-label fw-semibold">
                        Jumlah Anggota Keluarga (termasuk perwakilan) <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="number" 
                        class="form-control" 
                        id="family_count" 
                        name="family_count"
                        min="1"
                        value="1"
                        readonly
                    >
                    <div class="form-text">
                        <i class="bi bi-info-circle"></i>
                        Jumlah otomatis dihitung dari peserta yang ditambahkan
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="kk_number" class="form-label fw-semibold">
                        Nomor Kartu Keluarga <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="kk_number" 
                        name="kk_number"
                        placeholder="16 digit nomor KK"
                        maxlength="16"
                        pattern="[0-9]{16}"
                        required
                    >
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label fw-semibold">
                        Upload Dokumen Kartu Keluarga <span class="text-danger">*</span>
                    </label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <input 
                            type="file" 
                            class="d-none" 
                            id="kk_document" 
                            name="kk_document"
                            accept=".jpg,.jpeg,.png,.pdf"
                            required
                        >
                        <i class="bi bi-cloud-upload fs-1 text-primary mb-3 d-block"></i>
                        <p class="mb-2 fw-semibold">Klik untuk upload atau drag & drop</p>
                        <p class="small text-muted mb-0">Format: JPG, PNG, PDF (Max. 2MB)</p>
                    </div>
                    <div id="filePreview" class="file-preview d-none">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-check-fill text-success fs-4"></i>
                                <div>
                                    <div class="fw-semibold" id="fileName"></div>
                                    <div class="small text-muted" id="fileSize"></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" id="removeFile">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Peserta Mudik -->
        <div class="form-section">
            <h2 class="form-section-title">
                <i class="bi bi-person-badge-fill me-2"></i>DATA PESERTA MUDIK
            </h2>
            
            <div id="participantsContainer"></div>
            
            <button type="button" class="add-participant-btn" id="addParticipantBtn">
                <i class="bi bi-plus-circle me-2"></i>Tambah Peserta
            </button>
        </div>
        
        <!-- Keterangan -->
        <div class="form-section">
            <h2 class="form-section-title">
                <i class="bi bi-info-circle-fill me-2"></i>KETERANGAN
            </h2>
            
            <div class="checkbox-card">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="has_child_under_4"
                        name="has_child_under_4"
                    >
                    <label class="form-check-label" for="has_child_under_4">
                        Saya menyatakan bahwa anak dibawah 4 tahun akan dipangku selama perjalanan.
                    </label>
                </div>
            </div>
            
            <div class="checkbox-card">
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="data_valid"
                        name="data_valid"
                        required
                    >
                    <label class="form-check-label" for="data_valid">
                        Saya menyatakan bahwa data yang diisi adalah benar dan dapat dipertanggungjawabkan. <span class="text-danger">*</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 12px; padding: 18px;">
                <span class="btn-text">
                    <i class="bi bi-send-fill me-2"></i>Submit Pendaftaran
                </span>
                <span class="btn-loading d-none">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Mengirim data...
                </span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let participantCount = 0;

document.addEventListener('DOMContentLoaded', function() {
    // File upload handling
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('kk_document');
    const filePreview = document.getElementById('filePreview');
    
    fileUploadArea.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', handleFileSelect);
    
    // Drag and drop
    fileUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUploadArea.classList.add('dragover');
    });
    
    fileUploadArea.addEventListener('dragleave', () => {
        fileUploadArea.classList.remove('dragover');
    });
    
    fileUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect();
        }
    });
    
    document.getElementById('removeFile').addEventListener('click', () => {
        fileInput.value = '';
        filePreview.classList.add('d-none');
    });
    
    // Add participant
    document.getElementById('addParticipantBtn').addEventListener('click', addParticipant);
    
    // Check if representative should be added as participant
    document.getElementById('addToParticipants').addEventListener('change', function() {
        if (this.checked && participantCount === 0) {
            addParticipantFromRepresentative();
        }
    });
    
    // Initialize with representative
    if (document.getElementById('addToParticipants').checked) {
        addParticipantFromRepresentative();
    }
    
    // Form submission
    document.getElementById('registrationForm').addEventListener('submit', handleSubmit);
});

function handleFileSelect() {
    const file = document.getElementById('kk_document').files[0];
    const filePreview = document.getElementById('filePreview');
    
    if (file) {
        if (file.size > 2048 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            document.getElementById('kk_document').value = '';
            return;
        }
        
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG, PNG, atau PDF.');
            document.getElementById('kk_document').value = '';
            return;
        }
        
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        filePreview.classList.remove('d-none');
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function addParticipantFromRepresentative() {
    const name = document.getElementById('representative_name').value;
    const nik = document.getElementById('representative_nik').value;
    const birthDate = document.getElementById('representative_birth_date').value;
    
    if (name && nik && birthDate) {
        addParticipant(name, nik, birthDate);
    }
}

function addParticipant(name = '', nikKia = '', birthDate = '') {
    participantCount++;
    updateFamilyCount();
    
    const participantHtml = `
        <div class="participant-card" data-participant="${participantCount}">
            <div class="participant-header">
                <div class="participant-number">Peserta ${participantCount}</div>
                ${participantCount > 1 ? `
                    <button type="button" class="remove-participant" onclick="removeParticipant(${participantCount})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                ` : ''}
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">
                        Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control participant-name" 
                        name="participants[${participantCount - 1}][full_name]"
                        placeholder="Nama lengkap"
                        value="${name}"
                        required
                    >
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">
                        KTP / KIA <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control participant-nik" 
                        name="participants[${participantCount - 1}][nik_kia]"
                        placeholder="Nomor KTP/KIA"
                        maxlength="16"
                        value="${nikKia}"
                        required
                    >
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">
                        Tanggal Lahir <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="date" 
                        class="form-control participant-birth-date" 
                        name="participants[${participantCount - 1}][birth_date]"
                        value="${birthDate}"
                        required
                    >
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('participantsContainer').insertAdjacentHTML('beforeend', participantHtml);
}

function removeParticipant(id) {
    const card = document.querySelector(`[data-participant="${id}"]`);
    if (card) {
        card.remove();
        participantCount--;
        updateFamilyCount();
        renumberParticipants();
    }
}

function renumberParticipants() {
    const cards = document.querySelectorAll('.participant-card');
    cards.forEach((card, index) => {
        const number = index + 1;
        card.querySelector('.participant-number').textContent = `Peserta ${number}`;
        card.setAttribute('data-participant', number);
        
        card.querySelectorAll('input').forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('participants[')) {
                const fieldName = name.match(/\[([^\]]+)\]$/)[1];
                input.setAttribute('name', `participants[${index}][${fieldName}]`);
            }
        });
    });
}

function updateFamilyCount() {
    document.getElementById('family_count').value = participantCount;
}

async function handleSubmit(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    // Validate participants count
    if (participantCount === 0) {
        alert('Minimal harus ada 1 peserta mudik');
        return;
    }
    
    // Disable button
    submitBtn.disabled = true;
    btnText.classList.add('d-none');
    btnLoading.classList.remove('d-none');
    
    try {
        const formData = new FormData(this);

        const hasChildUnder4 = document.getElementById('has_child_under_4').checked;
        formData.set('has_child_under_4', hasChildUnder4 ? '1' : '0');
        
        const response = await axios.post(
            '{{ $submitUrl }}',
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        );
        
        if (response.data.success) {
            const successHtml = `
                <div class="modal fade" id="successModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 20px; border: none;">
                            <div class="modal-body p-5 text-center">
                                <div class="mb-4">
                                    <div class="mx-auto" style="width: 80px; height: 80px; background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-check-lg text-white" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                                <h4 class="fw-bold mb-3">✅ PENDAFTARAN BERHASIL</h4>
                                <p class="mb-4">${response.data.message}</p>
                                <p class="small text-muted mb-4">Notifikasi akan dikirim ke email<br><strong>${response.data.data.email}</strong></p>
                                <a href="{{ route('public.landing') }}" class="btn btn-primary px-5" style="border-radius: 12px;">
                                    Ke Beranda
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', successHtml);
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        }
    } catch (error) {
        let errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
        
        if (error.response && error.response.data) {
            errorMessage = error.response.data.message || errorMessage;
            
            if (error.response.data.errors) {
                const errors = error.response.data.errors;
                errorMessage = Object.values(errors).flat().join('<br>');
            }
        }
        
        alert(errorMessage);
    } finally {
        submitBtn.disabled = false;
        btnText.classList.remove('d-none');
        btnLoading.classList.add('d-none');
    }
}
</script>
@endpush