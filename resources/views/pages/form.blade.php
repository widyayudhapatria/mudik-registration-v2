@extends('layouts.app-v2')
@section('title', 'Formulir Pendaftaran - ' . config('mudik.website.name'))
@push('styles')
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
@endpush
@section('header')
    @include('components.header', [
        'menuItems' => [['label' => 'Formulir Pendaftaran', 'href' => '#form-pendaftaran', 'active' => true]],
    ])
@endsection
@section('content')
    <!-- Hero Section untuk Form -->
    @include('components.hero', [
        'sectionId' => 'form-pendaftaran',
        'slides' => [
            [
                'title' => 'Formulir Pendaftaran',
                'subtitle' =>
                    'Isi formulir pendaftaran mudik gratis dengan data yang <b>lengkap</b> dan <b>sesuai Kartu Keluarga (KK)</b>, serta gunakan <b>email aktif</b> untuk menerima <b>informasi</b> dan <b>notifikasi</b>.',
                'buttonText' => '',
                'buttonLink' => '',
            ],
        ],
    ])
    <!-- Formulir Pendaftaran -->
    <section class="section" id="form--pendaftaran">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="title text-center">
                        <h2>
                            Formulir
                            <b>Pendaftaran</b>
                        </h2>
                        <span class="title-border"><i class="mdi mdi-set-none"></i></span>
                    </div>
                </div>
            </div>
            <div class="row mt-4 pt-4 justify-content-center">
                <div class="col-lg-8 text-left">
                    <form id="registrationForm" enctype="multipart/form-data">
                        @csrf

                        <!-- Pilihan Kota Tujuan -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">PILIH KOTA TUJUAN</h5>

                                <!-- Loading state -->
                                <div id="destinationLoading" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Memuat kota tujuan...</p>
                                </div>

                                <!-- Destinations container -->
                                <div class="row" id="destinationOptions" style="display: none;">
                                    <!-- Will be populated by JavaScript -->
                                </div>

                                <!-- Error state - quota not set -->
                                <div id="destinationError" class="alert alert-warning text-center" style="display: none;">
                                    <i class="mdi mdi-alert-circle-outline fs-2"></i>
                                    <h6 class="mt-2">Kuota Harian Belum Diatur</h6>
                                    <p class="mb-0">
                                        Kuota untuk hari ini belum tersedia.
                                        <br />
                                        <strong>Silakan kembali lagi dalam 1 jam ke depan</strong> atau kunjungi secara
                                        berkala.
                                    </p>
                                </div>

                                <!-- Hidden input for destination_id -->
                                <input type="hidden" name="destination_id" id="destination_id" required>
                            </div>
                        </div>

                        <!-- Data Perwakilan Keluarga -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">DATA PERWAKILAN KELUARGA</h5>
                                <div class="mb-3">
                                    <label for="representative_name" class="form-label">
                                        Nama Lengkap Perwakilan
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="representative_name"
                                        name="representative_name" placeholder="Nama Lengkap Perwakilan"
                                        pattern="[A-Za-z ]+" oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '')"
                                        minlength="3" required />
                                </div>
                                <div class="mb-3">
                                    <label for="representative_nik" class="form-label">
                                        Nomor KTP Perwakilan
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control mb-0" id="representative_nik"
                                        name="representative_nik" placeholder="Nomor KTP Perwakilan (16 digit)"
                                        minlength="16" maxlength="16" pattern="[0-9]{16}" required />
                                    <div class="form-text pb-2">Nomor KTP harus tepat 16 digit</div>
                                </div>
                                <div class="mb-3">
                                    <label for="representative_birth_date" class="form-label">
                                        Tanggal Lahir Perwakilan
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="representative_birth_date"
                                        name="representative_birth_date" placeholder="Tanggal Lahir Perwakilan (dd/mm/yyyy)"
                                        required />
                                </div>
                                <div class="alert alert-info mb-0 mt-3 d-flex align-items-center gap-2">
                                    <i class="mdi mdi-information-outline fs-5"></i>
                                    <span>Data perwakilan keluarga akan otomatis tercantum sebagai <strong>Peserta 1</strong> dalam daftar peserta mudik.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Data Keluarga -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">DATA KELUARGA</h5>
                                <div class="mb-3">
                                    <label for="family_count" class="form-label">
                                        Jumlah Peserta Mudik (termasuk perwakilan keluarga)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="family_count" name="family_count"
                                        min="1" max="10" value="1" required />
                                </div>
                                <div class="mb-3">
                                    <label for="kk_number" class="form-label">
                                        Nomor Kartu Keluarga
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control mb-0" id="kk_number" name="kk_number"
                                        placeholder="Nomor Kartu Keluarga (16 digit)" minlength="16" maxlength="16"
                                        pattern="[0-9]{16}" required />
                                    <div class="form-text pb-2">Nomor Kartu Keluarga harus tepat 16 digit</div>
                                </div>
                                <div class="mb-3">
                                    <label for="kk_document" class="form-label">
                                        Upload Dokumen Kartu Keluarga
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="upload-area border rounded p-4 text-center" id="fileUploadArea"
                                        style="transition: all 0.3s ease;">
                                        <i class="mdi mdi-cloud-upload" style="font-size: 48px; color: #6c757d"></i>
                                        <p class="mb-2">Klik atau seret file ke sini</p>
                                        <input type="file" class="form-control d-none" id="kk_document"
                                            name="kk_document" accept=".jpg,.jpeg,.png" required />
                                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1"
                                            onclick="document.getElementById('kk_document').click()">
                                            Pilih File
                                        </button>
                                        <div class="form-text mt-2">
                                            <strong>Format:</strong> JPG, JPEG, PNG<br />
                                            <strong>Ukuran Max:</strong> 5 MB
                                        </div>
                                        <div id="fileInfo" class="mt-3" style="display: none; font-weight: 600;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Peserta Mudik -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">DATA PESERTA MUDIK</h5>

                                <div id="pesertaContainer">
                                    <!-- Peserta forms will be added here dynamically -->
                                </div>

                                <button type="button" class="btn btn-primary btn-custom w-100 mt-3"
                                    id="addParticipantBtn">
                                    <i class="mdi mdi-plus-circle"></i>
                                    Tambah Peserta
                                </button>
                            </div>
                        </div>

                        <!-- Keterangan dan Pernyataan -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">KETERANGAN</h5>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="has_child_under_4"
                                        name="has_child_under_4" />
                                    <label class="form-check-label" for="has_child_under_4">
                                        <strong>Memiliki anak dibawah 4 tahun sebagai peserta mudik ?</strong> Saya
                                        menyatakan bahwa anak dibawah 4 tahun akan dipangku selama perjalanan.
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="data_valid" name="data_valid"
                                        required />
                                    <label class="form-check-label" for="data_valid">
                                        Saya menyatakan data yang diisi adalah benar dan dapat dipertanggungjawabkan.
                                        <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mb-4">
                            <button type="submit" class="btn btn-primary btn-custom btn-lg px-5" id="submitFormBtn">
                                <span class="btn-text">
                                    <i class="mdi mdi-email"></i> Submit Pendaftaran
                                </span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Proses mengirim data...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
<!-- Include Form Utilities - Organized in sub-components -->
@include('components.util-datepicker')

@push('scripts')
    <script>
        // State management
        let pesertaCount = 0;
        const maxPeserta = 10;

        // Submit URL from controller
        const submitUrl = @json($submitUrl ?? '');

        // Initialize form
        document.addEventListener("DOMContentLoaded", function() {
            loadDestinations();
            initializeForm();
            setupEventListeners();
        });

        // Load available destinations
        async function loadDestinations() {
            const loadingEl = document.getElementById('destinationLoading');
            const optionsEl = document.getElementById('destinationOptions');
            const errorEl = document.getElementById('destinationError');
            const submitBtn = document.getElementById('submitFormBtn');

            try {
                const response = await fetch('/api/destinations/available');
                const data = await response.json();

                // Hide loading
                if (loadingEl) loadingEl.style.display = 'none';

                if (!data.success) {
                    // Handle error - quota not set
                    if (data.error_code === 'DAILY_QUOTA_NOT_SET') {
                        if (errorEl) errorEl.style.display = 'block';
                        if (submitBtn) submitBtn.disabled = true;
                    }
                    return;
                }

                // Check if there are available destinations
                if (!data.data || data.data.length === 0) {
                    if (errorEl) {
                        errorEl.innerHTML = `
                            <i class="mdi mdi-alert-circle-outline fs-2"></i>
                            <h6 class="mt-2">Kuota Tidak Tersedia</h6>
                            <p class="mb-0">
                                Maaf, saat ini tidak ada kuota tersedia untuk semua tujuan.
                                <br />
                                <strong>Silakan coba lagi besok atau hubungi admin</strong>.
                            </p>
                        `;
                        errorEl.style.display = 'block';
                    }
                    if (submitBtn) submitBtn.disabled = true;
                    return;
                }

                // Render destinations
                const html = data.data.map(dest => {
                    const badgeClass = dest.remaining > 5 ? 'bg-success' : dest.remaining > 2 ? 'bg-warning' :
                        'bg-danger';

                    if (!dest.is_available) {
                        return `
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="btn btn-outline-secondary w-100 py-3 disabled" style="cursor: not-allowed; opacity: 0.6;">
                                    <strong>${dest.name}</strong>
                                    <br>
                                    <small>Tersisa: <span class="badge bg-danger">0 orang</span></small>
                                </div>
                            </div>
                        `;
                    }

                    return `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <input type="radio" class="btn-check" name="destination_radio"
                                id="dest${dest.id}" value="${dest.id}" required>
                            <label class="btn btn-outline-dark w-100 py-3" for="dest${dest.id}">
                                <strong>${dest.name}</strong>
                                <br>
                                <small>Tersisa: <span class="badge ${badgeClass}">${dest.remaining} orang</span></small>
                            </label>
                        </div>
                    `;
                }).join('');

                if (optionsEl) {
                    optionsEl.innerHTML = html;
                    optionsEl.style.display = 'flex';
                }

                // Add event listeners for radio buttons
                document.querySelectorAll('input[name="destination_radio"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        document.getElementById('destination_id').value = this.value;
                    });
                });

            } catch (error) {
                console.error('Error loading destinations:', error);

                // Hide loading
                if (loadingEl) loadingEl.style.display = 'none';

                // Show error
                if (errorEl) {
                    errorEl.innerHTML = `
                        <i class="mdi mdi-alert-circle-outline fs-2"></i>
                        <h6 class="mt-2">Gagal Memuat Data</h6>
                        <p class="mb-0">
                            Terjadi kesalahan saat memuat daftar kota tujuan.
                            <br />
                            <strong>Silakan refresh halaman atau coba lagi nanti</strong>.
                        </p>
                    `;
                    errorEl.style.display = 'block';
                }

                if (submitBtn) submitBtn.disabled = true;
            }
        }

        function initializeForm() {
            // Initialize datepicker for representative birth date
            initializeDatepicker("#representative_birth_date");

            // Add first participant form by default
            addPesertaForm();
            syncRepresentativeToFirstParticipant();
        }

        function syncRepresentativeToFirstParticipant() {
            const firstForm = document.querySelector('.peserta-form[data-peserta-id="1"]');
            if (!firstForm) return;

            firstForm.querySelector('input[name="namaPeserta[]"]').value =
                document.getElementById('representative_name').value.trim();

            firstForm.querySelector('input[name="ktpPeserta[]"]').value =
                document.getElementById('representative_nik').value.trim();

            const repBirthDate = $('#representative_birth_date').val();
            $(`#tanggalLahirPeserta1`).val(repBirthDate);
            $('#hiddenTanggalLahirPeserta1').val(repBirthDate);
        }

        function setupRepresentativeSyncListeners() {
            document.getElementById('representative_name').addEventListener('input', syncRepresentativeToFirstParticipant);
            document.getElementById('representative_nik').addEventListener('input', syncRepresentativeToFirstParticipant);
            $('#representative_birth_date').on('change', syncRepresentativeToFirstParticipant);
        }

        function initializeDatepicker(selector) {
            // Wait for jQuery and datepicker to be available
            if (typeof jQuery === 'undefined') {
                console.error('jQuery not loaded');
                return;
            }

            if (typeof jQuery.fn.datepicker === 'undefined') {
                console.error('Datepicker plugin not loaded, retrying in 500ms');
                setTimeout(() => initializeDatepicker(selector), 500);
                return;
            }

            const $element = $(selector);

            // Check if already initialized
            if ($element.data('datepicker')) {
                return;
            }

            try {
                $element.datepicker({
                    uiLibrary: "bootstrap5",
                    format: "dd/mm/yyyy",
                    showOnFocus: true,
                    showRightIcon: false,
                    maxDate: function() {
                        return new Date();
                    },
                    size: "default",
                });
            } catch (error) {
                console.error('Error initializing datepicker:', error);
            }
        }

        function setupEventListeners() {
            // File upload handling
            const uploadInput = document.getElementById("kk_document");
            const uploadArea = document.getElementById("fileUploadArea");

            uploadInput.addEventListener("change", handleFileUpload);

            // Drag and drop for file upload
            uploadArea.addEventListener("dragover", (e) => {
                e.preventDefault();
                uploadArea.classList.add("border-primary");
            });

            uploadArea.addEventListener("dragleave", () => {
                uploadArea.classList.remove("border-primary");
            });

            uploadArea.addEventListener("drop", (e) => {
                e.preventDefault();
                uploadArea.classList.remove("border-primary");
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    uploadInput.files = files;
                    handleFileUpload({
                        target: uploadInput
                    });
                }
            });

            // Click area to trigger file input
            uploadArea.addEventListener("click", (e) => {
                if (!e.target.closest("button")) {
                    uploadInput.click();
                }
            });

            // Tambah peserta button
            document.getElementById("addParticipantBtn").addEventListener("click", addPesertaForm);

            // Checkbox untuk tambahkan perwakilan ke peserta

            // Jumlah anggota keluarga
            document.getElementById("family_count").addEventListener("change", validateJumlahAnggota);

            // Form submission
            document.getElementById("registrationForm").addEventListener("submit", handleFormSubmit);

            // KTP and KK number validation
            document.getElementById("representative_nik").addEventListener("input", formatNumber);
            document.getElementById("kk_number").addEventListener("input", formatNumber);

            // Add focus event to representative birth date to ensure datepicker works
            const repBirthDateInput = document.getElementById("representative_birth_date");
            if (repBirthDateInput) {
                repBirthDateInput.addEventListener("focus", function() {
                    if (!$(this).data('datepicker')) {
                        initializeDatepicker("#representative_birth_date");
                    }
                });
            }

            // Event listener data_valid checkbox to validate umur peserta
            document.getElementById("data_valid").addEventListener("change", function(e) {
                if (this.checked) {
                    if (!validateChildUnder4OnDataValid()) {
                        // Fallback jika validasi gagal, tetap uncheck checkbox
                        this.checked = false;
                    }
                }
            });

            setupRepresentativeSyncListeners();
        }

        function formatNumber(e) {
            e.target.value = e.target.value.replace(/\D/g, "").slice(0, 16);
        }

        // Track valid file upload state
        let isFileValid = false;

        function handleFileUpload(e) {
            const file = e.target.files[0];
            const fileInfo = document.getElementById("fileInfo");
            const uploadArea = document.getElementById("fileUploadArea");
            isFileValid = false; // Reset validity

            if (!file) {
                fileInfo.style.display = "none";
                uploadArea.classList.remove("border-danger", "border-success");
                return;
            }

            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            const allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            const fileSizeKB = (file.size / 1024).toFixed(2);

            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: "Format Tidak Valid!",
                    html: `<div class="text-start">
                        <p class="mb-0"><strong>File:</strong> ${file.name}</p>
                        <p class="text-danger mb-0">Format file tidak didukung.</p>
                        <p class="mb-0">Silakan gunakan file dengan format: <strong>JPG, JPEG, atau PNG</strong></p>
                    </div>`,
                    icon: "error",
                    confirmButtonText: "Baik, saya mengerti.",
                });
                e.target.value = "";
                fileInfo.style.display = "none";
                uploadArea.classList.add("border-danger");
                uploadArea.classList.remove("border-success");
                return;
            }

            // Validate file size (5MB = 5242880 bytes)
            if (file.size > maxSize) {
                Swal.fire({
                    title: "Ukuran File Terlalu Besar!",
                    html: `<div class="text-start">
                        <p class="mb-0"><strong>File:</strong> ${file.name}</p>
                        <p class="mb-0"><strong>Ukuran:</strong> ${fileSizeMB} MB</p>
                        <p class="text-danger mb-0"><strong>Maksimal: 5 MB</strong></p>
                        <p class="mt-3">Silakan pilih file yang lebih kecil.</p>
                    </div>`,
                    icon: "error",
                    confirmButtonText: "Baik, saya mengerti.",
                });
                e.target.value = "";
                fileInfo.style.display = "none";
                uploadArea.classList.add("border-danger");
                uploadArea.classList.remove("border-success");
                return;
            }

            // All validations passed
            isFileValid = true;
            uploadArea.classList.add("border-success");
            uploadArea.classList.remove("border-danger");

            const fileSizeDisplay = fileSizeKB > 1024 ? `${fileSizeMB} MB` : `${fileSizeKB} KB`;
            fileInfo.innerHTML = `<span class="text-success"><i class="mdi mdi-check-circle"></i> File diterima: ${file.name}</span><br/>
                <small class="text-muted">Ukuran: ${fileSizeDisplay} (dari maksimal 5 MB)</small>`;
            fileInfo.style.display = "block";

            console.log(`File valid: ${file.name} - ${fileSizeDisplay}`);
        }

        function addPesertaForm() {
            if (pesertaCount >= maxPeserta) {
                Swal.fire({
                    title: "Peringatan!",
                    text: `Maksimal ${maxPeserta} peserta yang dapat didaftarkan.`,
                    icon: "warning",
                    confirmButtonText: "OK",
                });
                return;
            }

            pesertaCount++;
            const pesertaContainer = document.getElementById("pesertaContainer");

            const pesertaDiv = document.createElement("div");
            pesertaDiv.className = "peserta-form border rounded p-3 mb-3 position-relative";
            pesertaDiv.dataset.pesertaId = pesertaCount;

            pesertaDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">Peserta ${pesertaCount}</h5>
                    ${
                    pesertaCount > 1
                        ? `<button type="button" class="btn btn-danger btn-sm remove-peserta px-2 py-0" data-id="${pesertaCount}">
                                <i class="mdi mdi-close-circle-outline fs-5"></i>
                            </button>`
                        : ""
                    }
                </div>

                <div class="mb-3">
                    <label for="namaPeserta${pesertaCount}" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="namaPeserta${pesertaCount}" name="namaPeserta[]" pattern="[A-Za-z ]+" oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '')" minlength="3" placeholder="Nama Lengkap" required>
                </div>

                <div class="mb-3">
                    <label for="ktpPeserta${pesertaCount}" class="form-label">KTP / KIA <span class="text-danger">*</span></label>
                    <input type="text" class="form-control mb-0 ktp-input" id="ktpPeserta${pesertaCount}" name="ktpPeserta[]" placeholder="Nomor KTP / KIA" minlength="16" maxlength="16" pattern="[0-9]{16}" required>
                    <div class="form-text p-2">Nomor KTP/KIA harus tepat 16 digit</div>
                </div>

                <div class="mb-3">
                    <label for="tanggalLahirPeserta${pesertaCount}" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="text" class="form-control datepicker-input" id="tanggalLahirPeserta${pesertaCount}" name="tanggalLahirPeserta[]" placeholder="dd/mm/yyyy" required readonly>
                </div>
                `;

            pesertaContainer.appendChild(pesertaDiv);

            if (pesertaCount === 1) {
                const nameInput = pesertaDiv.querySelector('input[name="namaPeserta[]"]');
                const ktpInput = pesertaDiv.querySelector('input[name="ktpPeserta[]"]');
                const birthInput = pesertaDiv.querySelector('input[name="tanggalLahirPeserta[]"]');

                nameInput.setAttribute('readonly', true);
                ktpInput.setAttribute('readonly', true);

                birthInput.setAttribute('disabled', true);

                const hiddenBirth = document.createElement('input');
                hiddenBirth.type = 'hidden';
                hiddenBirth.name = 'tanggalLahirPeserta[]';
                hiddenBirth.id = 'hiddenTanggalLahirPeserta1';

                pesertaDiv.appendChild(hiddenBirth);
            }

            // Add event listener for remove button
            const removeBtn = pesertaDiv.querySelector(".remove-peserta");
            if (removeBtn) {
                removeBtn.addEventListener("click", function() {
                    removePesertaForm(this.dataset.id);
                });
            }

            // Add format validation for KTP input
            const ktpInput = pesertaDiv.querySelector(".ktp-input");
            ktpInput.addEventListener("input", formatNumber);

            // Initialize datepicker for this peserta's birth date
            const datepickerSelector = `#tanggalLahirPeserta${pesertaCount}`;
            initializeDatepicker(datepickerSelector);

            if (pesertaCount === 1) {
                setTimeout(() => $(`#tanggalLahirPeserta1`).prop('readonly', true), 600);
            }

            // Add focus event to ensure datepicker works on focus
            const datepickerInput = pesertaDiv.querySelector(".datepicker-input");
            if (datepickerInput) {
                datepickerInput.addEventListener("focus", function() {
                    if (!$(this).data('datepicker')) {
                        initializeDatepicker(datepickerSelector);
                    }
                });
            }

            // Update button visibility
            updateTambahPesertaButton();
        }

        function removePesertaForm(id) {
            const pesertaForm = document.querySelector(`[data-peserta-id="${id}"]`);
            if (pesertaForm) {
                Swal.fire({
                    title: "Konfirmasi",
                    text: "Apakah Anda yakin ingin menghapus peserta ini?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Destroy datepicker for this peserta before removing
                        $(`#tanggalLahirPeserta${id}`).datepicker('destroy');

                        pesertaForm.remove();
                        pesertaCount--;

                        // CRITICAL: Update family_count to match actual peserta count IMMEDIATELY
                        // Use setTimeout to ensure DOM is updated before querying
                        setTimeout(() => {
                            const actualPesertaCount = document.querySelectorAll(".peserta-form").length;
                            const familyCountInput = document.getElementById("family_count");
                            if (familyCountInput) {
                                familyCountInput.value = actualPesertaCount;
                                // Trigger change event for any listeners
                                familyCountInput.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                            updatePesertaNumbers();
                            updateTambahPesertaButton();
                        }, 0);
                    }
                });
            }
        }

        function updatePesertaNumbers() {
            const pesertaForms = document.querySelectorAll(".peserta-form");
            pesertaForms.forEach((form, index) => {
                const header = form.querySelector("h6");
                if (header) {
                    header.textContent = `Peserta ${index + 1}`;
                }
            });
        }

        function updateTambahPesertaButton() {
            const btn = document.getElementById("addParticipantBtn");
            if (pesertaCount >= maxPeserta) {
                btn.disabled = true;
                btn.innerHTML = `<i class="mdi mdi-information-outline"></i> Maksimal ${maxPeserta} peserta`;
            } else {
                btn.disabled = false;
                btn.innerHTML = `<i class="mdi mdi-plus-circle"></i> Tambah Peserta`;
            }
        }

        function validateJumlahAnggota(e) {
            const jumlah = parseInt(e.target.value);
            if (jumlah < 1) {
                e.target.value = 1;
            } else if (jumlah > maxPeserta) {
                e.target.value = maxPeserta;
                Swal.fire({
                    title: "Peringatan!",
                    text: `Maksimal ${maxPeserta} anggota keluarga yang dapat didaftarkan.`,
                    icon: "warning",
                    confirmButtonText: "OK",
                });
            }
        }

        // Convert date format from dd/mm/yyyy to YYYY-MM-DD
        function convertDateFormat(dateString) {
            if (!dateString) return '';
            const parts = dateString.split('/');
            if (parts.length !== 3) return dateString;
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }

        function calculateAge(birthDateString) {
            if (!birthDateString) return null;
            const parts = birthDateString.split('/');
            if (parts.length !== 3) return null;

            const day = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            const year = parseInt(parts[2]);

            const birthDate = new Date(year, month - 1, day);
            const today = new Date();

            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            return age;
        }

        function validateChildUnder4OnDataValid() {
            const hasChildCheckbox = document.getElementById("has_child_under_4");
            const dataValidCheckbox = document.getElementById("data_valid");
            const pesertaForms = document.querySelectorAll(".peserta-form");

            let hasAnyChildUnder4 = false;

            // Check if any participant is under 4 years old
            pesertaForms.forEach((form) => {
                const birthDateInput = form.querySelector('input[name="tanggalLahirPeserta[]"]');
                if (birthDateInput && birthDateInput.value) {
                    const age = calculateAge(birthDateInput.value);
                    if (age !== null && age < 4) {
                        hasAnyChildUnder4 = true;
                    }
                }
            });

            // Case 1: has_child_under_4 is checked but no children under 4 found
            if (hasChildCheckbox.checked && !hasAnyChildUnder4) {
                Swal.fire({
                    title: "Peringatan!",
                    html: `<div class="text-start">
                        <p class="mb-3"><strong>Data tidak konsisten :</strong></p>
                        <p>Anda mencentang <strong>"Memiliki anak dibawah 4 tahun"</strong>, tetapi data peserta yang Anda inputkan tidak memiliki anak berusia dibawah 4 tahun.</p>
                        <p class="mt-2">Silakan periksa kembali data peserta Anda.</p>
                    </div>`,
                    icon: "error",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                }).then(() => {
                    hasChildCheckbox.checked = false;
                    dataValidCheckbox.checked = false;
                });
                return false;
            }

            // Case 2: has_child_under_4 is NOT checked but children under 4 found
            if (!hasChildCheckbox.checked && hasAnyChildUnder4) {
                Swal.fire({
                    title: "Peringatan!",
                    html: `<div class="text-start">
                        <p class="mb-3"><strong>Data tidak konsisten :</strong></p>
                        <p>Data peserta memiliki anak berusia dibawah 4 tahun, tetapi checkbox <strong>"Memiliki anak dibawah 4 tahun"</strong> belum dicentang.</p>
                        <p class="mt-2">Silakan centang checkbox <strong>"Memiliki anak dibawah 4 tahun"</strong> terlebih dahulu.</p>
                    </div>`,
                    icon: "error",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                }).then(() => {
                    dataValidCheckbox.checked = false;
                    hasChildCheckbox.checked = true;
                    hasChildCheckbox.focus();
                });
                return false;
            }

            // All validations passed
            return true;
        }

        function handleFormSubmit(e) {
            e.preventDefault();

            // VALIDATION #1: DESTINATION (PALING AWAL)
            const destinationId = document.getElementById("destination_id").value;
            if (!destinationId) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Anda harus memilih kota tujuan terlebih dahulu.",
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan memilih.",
                });
                return;
            }

            // VALIDATION #2: FILE UPLOAD
            const kkDocument = document.getElementById("kk_document");
            if (!kkDocument) {
                Swal.fire({
                    title: "Error!",
                    text: "Input file dokumen KK tidak ditemukan.",
                    icon: "error",
                    confirmButtonText: "Baik, saya mengerti.",
                });
                return;
            }

            // Check file selected first
            if (!kkDocument.files || kkDocument.files.length === 0) {
                Swal.fire({
                    title: "Dokumen KK Belum Dipilih!",
                    html: `<div class="text-start">
                        <p class="mb-2">Anda belum mengupload dokumen Kartu Keluarga (KK).</p>
                        <p class="mb-0"><strong>Silakan upload dokumen KK terlebih dahulu.</strong></p>
                    </div>`,
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan upload.",
                }).then(() => {
                    // Scroll to upload area and focus
                    const uploadArea = document.getElementById("fileUploadArea");
                    if (uploadArea) {
                        uploadArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        uploadArea.style.border = '2px solid #dc3545';
                        setTimeout(() => {
                            uploadArea.style.border = '';
                        }, 3000);
                    }
                });
                return;
            }

            // Then check if file is valid (after upload)
            if (!isFileValid) {
                Swal.fire({
                    title: "File Tidak Valid!",
                    html: `<div class="text-start">
                        <p class="mb-2">File yang dipilih tidak melewati validasi:</p>
                        <ul class="mt-2 mb-2">
                            <li>Pastikan format file: <strong>JPG, JPEG, atau PNG</strong></li>
                            <li>Pastikan ukuran file tidak melebihi <strong>5 MB</strong></li>
                        </ul>
                        <p class="mb-0"><strong>Silakan pilih file yang sesuai.</strong></p>
                    </div>`,
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                }).then(() => {
                    // Clear invalid file
                    kkDocument.value = '';
                    document.getElementById("fileInfo").style.display = "none";
                    const uploadArea = document.getElementById("fileUploadArea");
                    if (uploadArea) {
                        uploadArea.classList.remove("border-success");
                    }
                });
                return;
            }

            // VALIDATION #3: JUMLAH PESERTA
            const jumlahPeserta = document.querySelectorAll(".peserta-form").length;
            const jumlahAnggota = parseInt(document.getElementById("family_count").value);
            console.log(`Validating form submission: jumlahPeserta=${jumlahPeserta}, jumlahAnggota=${jumlahAnggota}`);

            if (jumlahPeserta === 0) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Anda harus menambahkan minimal 1 peserta.",
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan tambahkan peserta.",
                });
                return;
            }

            if (jumlahPeserta !== jumlahAnggota) {
                console.log(`Mismatch detected: jumlahPeserta (${jumlahPeserta}) != jumlahAnggota (${jumlahAnggota}).`);
                Swal.fire({
                    title: "Peringatan!",
                    text: `Jumlah peserta mudik yang diinput tidak sesuai. Harap samakan dengan jumlah peserta mudik.`,
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;

                // CRITICAL: Auto-sync family_count with actual peserta count if mismatch
                //document.getElementById("family_count").value = jumlahPeserta;
            }

            // Validate representative name (min 3 characters)
            const repName = document.getElementById("representative_name").value.trim();
            if (repName.length < 3) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Nama perwakilan harus terdiri dari minimal 3 karakter.",
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;
            }

            // Validate representative NIK (16 digits)
            const repNik = document.getElementById("representative_nik").value.replace(/\D/g, '');
            if (repNik.length !== 16) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "NIK perwakilan harus tepat 16 angka.",
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;
            }

            // Validate KK number (16 digits)
            const kkNumber = document.getElementById("kk_number").value.replace(/\D/g, '');
            if (kkNumber.length !== 16) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Nomor KK harus tepat 16 angka.",
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;
            }

            // Validate tanggal lahir perwakilan
            if (!$("#representative_birth_date").val()) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Tanggal lahir perwakilan harus diisi.",
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;
            }

            // Validate all peserta birth dates
            let missingBirthDate = false;
            $(".datepicker-input").each(function() {
                if ($(this).prop("required") && !$(this).val()) {
                    missingBirthDate = true;
                    return false;
                }
            });

            if (missingBirthDate) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Semua tanggal lahir peserta harus diisi.",
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;
            }

            // Check if pernyataan data is checked
            if (!document.getElementById("data_valid").checked) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Anda harus menyetujui pernyataan data sebelum submit.",
                    icon: "warning",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;
            }

            // Validate participants
            let pesertaValid = true;
            let hasAnyChildUnder4 = false;
            let allPesertaForms = document.querySelectorAll(".peserta-form");
            let ktpCollection = [];

            allPesertaForms.forEach((form, index) => {
                const name = form.querySelector('input[name="namaPeserta[]"]').value.trim();
                const nikKia = form.querySelector('input[name="ktpPeserta[]"]').value.replace(/\D/g, '');
                const birthDateInput = form.querySelector('input[name="tanggalLahirPeserta[]"]');

                if (name.length < 3) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: `Nama peserta ${index + 1} harus terdiri dari minimal 3 karakter.`,
                        icon: "warning",
                        confirmButtonText: "Baik, saya akan periksa kembali.",
                    });
                    pesertaValid = false;
                    return false;
                }

                if (nikKia.length !== 16) {
                    Swal.fire({
                        title: "Peringatan!",
                        text: `NIK/KIA peserta ${index + 1} harus tepat 16 angka.`,
                        icon: "warning",
                        confirmButtonText: "Baik, saya akan periksa kembali.",
                    });
                    pesertaValid = false;
                    return false;
                }

                // Collect KTP for duplicate check
                ktpCollection.push({
                    ktp: nikKia,
                    index: index + 1
                });

                if (birthDateInput && birthDateInput.value) {
                    const age = calculateAge(birthDateInput.value);
                    if (age !== null && age < 4) {
                        hasAnyChildUnder4 = true;
                    }
                }
            });

            if (!pesertaValid) return;

            // VALIDATION: CHECK DUPLICATE KTP AMONG PARTICIPANTS
            const ktpSet = new Set();
            let duplicateFound = false;
            let duplicateKtp = '';
            let duplicateIndexes = [];

            for (let item of ktpCollection) {
                if (ktpSet.has(item.ktp)) {
                    duplicateFound = true;
                    duplicateKtp = item.ktp;
                    // Find all indexes with this KTP
                    duplicateIndexes = ktpCollection
                        .filter(k => k.ktp === item.ktp)
                        .map(k => k.index);
                    break;
                }
                ktpSet.add(item.ktp);
            }

            if (duplicateFound) {
                Swal.fire({
                    title: "Data Tidak Valid!",
                    html: `<div class="text-start">
                        <p class="mb-2"><strong>Nomor KTP Peserta tidak boleh sama diantara yang lain.</strong></p>
                        <p class="mb-0">Nomor KTP: <strong>${duplicateKtp}</strong></p>
                        <p class="mb-0">Ditemukan pada Peserta: <strong>${duplicateIndexes.join(', ')}</strong></p>
                        <p class="mt-3">Silakan periksa dan perbaiki nomor KTP yang duplikat.</p>
                    </div>`,
                    icon: "error",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;
            }

            // If any child under 4 but checkbox not checked, reject submission
            const hasChildCheckbox = document.getElementById("has_child_under_4");
            if (hasAnyChildUnder4 && !hasChildCheckbox.checked) {
                Swal.fire({
                    title: "Peringatan!",
                    html: `<div class="text-start">
                        <p class="mb-3"><strong>Data tidak valid:</strong></p>
                        <p>Data peserta memiliki anak berusia dibawah 4 tahun, tetapi checkbox <strong>"Memiliki anak dibawah 4 tahun"</strong> belum dicentang.</p>
                        <p class="mt-2">Silakan centang checkbox <strong>"Memiliki anak dibawah 4 tahun"</strong> terlebih dahulu.</p>
                    </div>`,
                    icon: "error",
                    confirmButtonText: "Baik, saya akan periksa kembali.",
                });
                return;
            }

            // ALL VALIDATIONS PASSED - PROCEED WITH SUBMISSION
            // Show loading on button submit
            const submitBtn = document.getElementById("submitFormBtn");
            const btnText = submitBtn.querySelector(".btn-text");
            const btnLoading = submitBtn.querySelector(".btn-loading");
            submitBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');

            // Collect form data
            const formData = new FormData();

            // Add CSRF token
            const csrfToken = document.querySelector('input[name="_token"]').value;
            formData.append('_token', csrfToken);
            formData.append("destination_id", destinationId);

            // Add representative data with converted date
            formData.append("representative_name", document.getElementById("representative_name").value);
            formData.append("representative_nik", document.getElementById("representative_nik").value);
            formData.append("representative_birth_date", convertDateFormat($("#representative_birth_date").val()));
            formData.append("family_count", document.getElementById("family_count").value);
            formData.append("kk_number", document.getElementById("kk_number").value);

            // Add KK document
            const kkDocumentFile = document.getElementById("kk_document").files[0];
            if (kkDocumentFile) {
                formData.append("kk_document", kkDocumentFile);
            }

            // Add has_child_under_4
            formData.append("has_child_under_4", document.getElementById("has_child_under_4").checked ? '1' : '0');

            // Collect participants data
            const pesertaFormsData = document.querySelectorAll(".peserta-form");
            pesertaFormsData.forEach((form, index) => {
                const name = form.querySelector('input[name="namaPeserta[]"]').value;
                const nikKia = form.querySelector('input[name="ktpPeserta[]"]').value;
                const birthDate = form.querySelector('input[name="tanggalLahirPeserta[]"]').value;

                formData.append(`participants[${index}][full_name]`, name);
                formData.append(`participants[${index}][nik_kia]`, nikKia);
                formData.append(`participants[${index}][birth_date]`, convertDateFormat(birthDate));
            });

            // Submit to backend
            fetch(submitUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();

                    if (data.success) {
                        //reset submit button
                        resetSubmitButton();

                        Swal.fire({
                            title: "Pendaftaran Berhasil!",
                            html: `
                        <div class="text-start">
                            <p class="mb-3">${data.message}</p>
                            ${data.data && data.data.email ? `<p><strong>Email:</strong> ${data.data.email}</p>` : ''}
                            <p class="text-muted mt-3">Silakan cek email secara berkala.</p>
                        </div>
                        `,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            icon: "success",
                            confirmButtonText: "Baik, saya mengerti.",
                        }).then(() => {
                            // Reset form
                            clearAllFormData();
                            // Show redirect timer
                            showRedirectTimer();
                        });
                    } else {
                        //reset submit button
                        resetSubmitButton();

                        // Handle error from backend
                        let errorMessage = 'Terjadi kesalahan saat memproses pendaftaran :<br/><br/>';
                        errorMessage += '<div class="text-start mt-2">';

                        if (data.errors) {
                            // Handle validation errors
                            for (let field in data.errors) {
                                data.errors[field].forEach(err => {
                                    errorMessage += `<p class="mb-0 fw-bolder">- ${err}</p>`;
                                });
                            }
                        } else if (data.message) {
                            // Handle custom error messages (may contain HTML)
                            // Check if message contains HTML tags
                            if (/<[^>]*>/.test(data.message)) {
                                // Message contains HTML, display as is
                                errorMessage += `<p class="mb-2 fw-bolder">${data.message}</p>`;
                            } else {
                                // Plain text message
                                errorMessage += `<p class="mb-0 fw-bolder">- ${data.message}</p>`;
                            }
                        }
                        errorMessage +=
                            '</div><br/>Mohon periksa kembali data dan pastikan file yang diunggah sesuai ketentuan.';

                        Swal.fire({
                            title: "Pendaftaran Gagal!",
                            html: errorMessage,
                            icon: "error",
                            confirmButtonText: "Baik, saya mengerti.",
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    //reset submit button
                    resetSubmitButton();
                    Swal.fire({
                        title: "Error!",
                        text: "Terjadi kesalahan pada koneksi. Silakan coba lagi.",
                        icon: "error",
                        confirmButtonText: "Baik, saya mengerti.",
                    });
                });
        }

        // Clear all from inputs and reset form -- success
        function clearAllFormData() {
            // Destroy existing datepickers first
            $("#representative_birth_date").datepicker('destroy');
            $(".datepicker-input").datepicker('destroy');

            document.getElementById("registrationForm").reset();
            document.getElementById("fileInfo").style.display = "none";

            // Reset datepicker
            $("#representative_birth_date").val("");

            // Reset peserta forms
            document.getElementById("pesertaContainer").innerHTML = "";
            pesertaCount = 0;

            // Re-initialize representative datepicker
            setTimeout(() => {
                initializeDatepicker("#representative_birth_date");
                addPesertaForm();
            }, 100);

            // Scroll to top
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }

        // Show redirect timer to home page after success
        function showRedirectTimer() {
            let timeLeft = 3;
            const redirectUrl = window.location.origin;

            Swal.fire({
                title: "Pengalihan Halaman",
                html: `
                <div class="text-start">
                    <p class="mb-3">Halaman akan otomatis kembali ke beranda dalam <strong id="timerCount">${timeLeft} detik.</strong></p>
                    <div class="progress" style="height: 25px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                icon: "info",
                confirmButtonText: "Kembali ke Beranda",
                didOpen: () => {
                    // Start countdown timer
                    const timerInterval = setInterval(() => {
                        timeLeft--;
                        const timerCountElement = document.getElementById("timerCount");
                        const progressBar = document.getElementById("progressBar");

                        if (timerCountElement) {
                            timerCountElement.textContent = timeLeft + " detik.";
                        }

                        // Update progress bar width
                        if (progressBar) {
                            const percentage = (timeLeft / 3) * 100;
                            progressBar.style.width = percentage + "%";
                        }

                        // Redirect when timer reaches 0
                        if (timeLeft <= 0) {
                            clearInterval(timerInterval);
                            Swal.close();
                            window.location.href = redirectUrl;
                        }
                    }, 1000); // Update setiap 1 detik
                }
            }).then((result) => {
                // Jika user klik button confirm sebelum timer habis
                if (result.isConfirmed) {
                    window.location.href = redirectUrl;
                }
            });
        }

        // reset button submit
        function resetSubmitButton() {
            const submitBtn = document.getElementById("submitFormBtn");
            if (!submitBtn) return;
            const btnText = submitBtn.querySelector(".btn-text");
            const btnLoading = submitBtn.querySelector(".btn-loading");
            if (btnText) btnText.classList.remove('d-none');
            if (btnLoading) btnLoading.classList.add('d-none');
            submitBtn.disabled = false;
        }
    </script>
@endpush
