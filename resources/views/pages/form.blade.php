@extends('layouts.app-v2')
@section('title', 'Formulir Pendaftaran Mudik Gratis - Mudik Banten')
@push('styles')
    <style>
     html {
        scroll-behavior: smooth;
      }
    </style>
@endpush
@section('header')
    @include('components.header', [
        'menuItems' => [
            ['label' => 'Formulir Pendaftaran', 'href' => '#form-pendaftaran', 'active' => true],
        ]
    ])
@endsection
@section('content')
    <!-- Hero Section untuk Form -->
    @include('components.hero', [
        'sectionId' => 'form-pendaftaran',
            'slides' => [
            [
                'title' => 'Formulir Pendaftaran',
                    'subtitle' => 'Isi formulir pendaftaran mudik gratis dengan data yang <b>lengkap</b> dan <b>sesuai Kartu Keluarga (KK)</b>, serta gunakan <b>email aktif</b> untuk menerima <b>informasi</b> dan <b>notifikasi</b>.',
                    'buttonText' => '',
                    'buttonLink' => '',
            ],
        ]
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
                        <!-- Data Perwakilan Keluarga -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">DATA PERWAKILAN KELUARGA</h5>
                                <div class="mb-3">
                                    <label for="representative_name" class="form-label">
                                    Nama Lengkap Perwakilan
                                    <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="representative_name"
                                        name="representative_name"
                                        placeholder="nama lengkap sesuai KTP"
                                        required
                                        />
                                </div>
                                <div class="mb-3">
                                    <label for="representative_nik" class="form-label">
                                    Nomor KTP Perwakilan
                                    <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="representative_nik"
                                        name="representative_nik"
                                        placeholder="16 digit nomor KTP"
                                        maxlength="16"
                                        pattern="[0-9]{16}"
                                        required
                                        />
                                </div>
                                <div class="mb-3">
                                    <label for="representative_birth_date" class="form-label">
                                    Tanggal Lahir Perwakilan
                                    <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="representative_birth_date"
                                        name="representative_birth_date"
                                        placeholder="dd/mm/yyyy"
                                        required
                                        />
                                </div>
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="addToParticipants"
                                        name="addToParticipants"
                                        />
                                    <label class="form-check-label" for="addToParticipants">
                                    Tambahkan ke daftar peserta mudik
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Data Keluarga -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">DATA KELUARGA</h5>
                                <div class="mb-3">
                                    <label for="family_count" class="form-label">
                                    Jumlah Anggota Keluarga
                                    <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="family_count"
                                        name="family_count"
                                        min="1"
                                        max="20"
                                        value="1"
                                        required
                                        />
                                </div>
                                <div class="mb-3">
                                    <label for="kk_number" class="form-label">
                                    Nomor Kartu Keluarga
                                    <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="kk_number"
                                        name="kk_number"
                                        placeholder="nomor kk"
                                        maxlength="16"
                                        pattern="[0-9]{16}"
                                        required
                                        />
                                </div>
                                <div class="mb-3">
                                    <label for="kk_document" class="form-label">
                                    Upload Dokumen Kartu Keluarga
                                    <span class="text-danger">*</span>
                                    </label>
                                    <div class="upload-area border rounded p-4 text-center" id="fileUploadArea">
                                        <i class="mdi mdi-cloud-upload" style="font-size: 48px; color: #6c757d"></i>
                                        <p class="mb-2">Klik atau seret file ke sini</p>
                                        <input
                                            type="file"
                                            class="form-control d-none"
                                            id="kk_document"
                                            name="kk_document"
                                            accept="image/*,.pdf"
                                            required
                                            />
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm px-3 py-1"
                                            onclick="document.getElementById('kk_document').click()"
                                            >
                                        Pilih File
                                        </button>
                                        <div class="form-text mt-2">Format: JPG, PNG, PDF (Maks. 2MB)</div>
                                        <div id="fileInfo" class="mt-2 text-success" style="display: none"></div>
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

                                <button
                                type="button"
                                class="btn btn-primary btn-custom w-100 mt-3"
                                id="addParticipantBtn"
                                >
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
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="has_child_under_4"
                                        name="has_child_under_4"
                                        />
                                    <label class="form-check-label" for="has_child_under_4">
                                    Saya menyatakan bahwa anak dibawah 4 tahun akan dipangku selama perjalanan.
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="data_valid"
                                        name="data_valid"
                                        required
                                        />
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
                                Mengirim data...
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

        // Initialize form
        document.addEventListener("DOMContentLoaded", function () {
            initializeForm();
            setupEventListeners();
        });

        function initializeForm() {
            // Initialize datepicker for representative birth date
            initializeDatepicker("#representative_birth_date");

            // Add first participant form by default
            addPesertaForm();
        }

        function initializeDatepicker(selector) {
            $(selector).datepicker({
                uiLibrary: "bootstrap5",
                format: "dd/mm/yyyy",
                showOnFocus: true,
                showRightIcon: false,
                maxDate: function () {
                    return new Date();
                },
                size: "default",
                change: function (e) {
                    // Trigger validation on change
                    if (e.target) {
                        $(e.target).valid();
                    }
                },
            });
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
            document.getElementById("addToParticipants").addEventListener("change", handleTambahkanPerwakilan);

            // Jumlah anggota keluarga
            document.getElementById("family_count").addEventListener("change", validateJumlahAnggota);

            // Form submission
            document.getElementById("registrationForm").addEventListener("submit", handleFormSubmit);

            // KTP and KK number validation
            document.getElementById("representative_nik").addEventListener("input", formatNumber);
            document.getElementById("kk_number").addEventListener("input", formatNumber);
        }

        function formatNumber(e) {
            e.target.value = e.target.value.replace(/\D/g, "").slice(0, 16);
        }

        function handleFileUpload(e) {
            const file = e.target.files[0];
            const fileInfo = document.getElementById("fileInfo");

            if (file) {
                const maxSize = 2 * 1024 * 1024; // 2MB
                const allowedTypes = ["image/jpeg", "image/png", "image/jpg", "application/pdf"];

                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        title: "Error!",
                        text: "Format file tidak didukung. Gunakan JPG, PNG, atau PDF.",
                        icon: "error",
                        confirmButtonText: "OK",
                    });
                    e.target.value = "";
                    fileInfo.style.display = "none";
                    return;
                }

                if (file.size > maxSize) {
                    Swal.fire({
                        title: "Error!",
                        text: "Ukuran file terlalu besar. Maksimal 2MB.",
                        icon: "error",
                        confirmButtonText: "OK",
                    });
                    e.target.value = "";
                    fileInfo.style.display = "none";
                    return;
                }

                fileInfo.innerHTML = `<i class="mdi mdi-check-circle"></i> ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
                fileInfo.style.display = "block";
            }
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
                    <input type="text" class="form-control" id="namaPeserta${pesertaCount}" name="namaPeserta[]" placeholder="nama lengkap" required>
                </div>

                <div class="mb-3">
                    <label for="ktpPeserta${pesertaCount}" class="form-label">KTP / KIA <span class="text-danger">*</span></label>
                    <input type="text" class="form-control ktp-input" id="ktpPeserta${pesertaCount}" name="ktpPeserta[]" placeholder="nomor KTP / KIA" maxlength="16" required>
                    <div class="form-text">Masukkan nomor KTP (16 digit) atau KIA</div>
                </div>

                <div class="mb-3">
                    <label for="tanggalLahirPeserta${pesertaCount}" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="text" class="form-control datepicker-input" id="tanggalLahirPeserta${pesertaCount}" name="tanggalLahirPeserta[]" placeholder="dd/mm/yyyy" required readonly>
                </div>
                `;

            pesertaContainer.appendChild(pesertaDiv);

            // Add event listener for remove button
            const removeBtn = pesertaDiv.querySelector(".remove-peserta");
            if (removeBtn) {
                removeBtn.addEventListener("click", function () {
                    removePesertaForm(this.dataset.id);
                });
            }

            // Add format validation for KTP input
            const ktpInput = pesertaDiv.querySelector(".ktp-input");
            ktpInput.addEventListener("input", formatNumber);

            // Initialize datepicker for this peserta's birth date
            initializeDatepicker(`#tanggalLahirPeserta${pesertaCount}`);

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
                        pesertaForm.remove();
                        pesertaCount--;
                        updatePesertaNumbers();
                        updateTambahPesertaButton();
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

        function handleTambahkanPerwakilan(e) {
            if (e.target.checked) {
                // Auto-fill first participant with representative data
                const nama = document.getElementById("representative_name").value;
                const ktp = document.getElementById("representative_nik").value;
                const tanggalLahir = $("#representative_birth_date").val();

                if (pesertaCount > 0) {
                    document.getElementById("namaPeserta1").value = nama;
                    document.getElementById("ktpPeserta1").value = ktp;
                    $("#tanggalLahirPeserta1").val(tanggalLahir);

                    // Make first participant fields readonly
                    document.getElementById("namaPeserta1").setAttribute("readonly", true);
                    document.getElementById("ktpPeserta1").setAttribute("readonly", true);
                    $("#tanggalLahirPeserta1").prop("readonly", true).addClass("readonly-datepicker");
                }
            } else {
                // Remove readonly and clear values
                if (pesertaCount > 0) {
                    document.getElementById("namaPeserta1").removeAttribute("readonly");
                    document.getElementById("ktpPeserta1").removeAttribute("readonly");
                    $("#tanggalLahirPeserta1").prop("readonly", false).removeClass("readonly-datepicker");
                    document.getElementById("namaPeserta1").value = "";
                    document.getElementById("ktpPeserta1").value = "";
                    $("#tanggalLahirPeserta1").val("");
                }
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

        function handleFormSubmit(e) {
            e.preventDefault();

            // Validate jumlah anggota vs peserta
            const jumlahAnggota = parseInt(document.getElementById("family_count").value);
            const jumlahPeserta = document.querySelectorAll(".peserta-form").length;

            if (jumlahPeserta > jumlahAnggota) {
                Swal.fire({
                    title: "Peringatan!",
                    text: `Jumlah peserta (${jumlahPeserta}) melebihi jumlah anggota keluarga (${jumlahAnggota}). Silakan sesuaikan.`,
                    icon: "warning",
                    confirmButtonText: "OK",
                });
                return;
            }

            // Validate tanggal lahir perwakilan
            if (!$("#representative_birth_date").val()) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Tanggal lahir perwakilan harus diisi.",
                    icon: "warning",
                    confirmButtonText: "OK",
                });
                return;
            }

            // Validate all peserta birth dates
            let missingBirthDate = false;
            $(".datepicker-input").each(function () {
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
                    confirmButtonText: "OK",
                });
                return;
            }

            // Check if pernyataan data is checked
            if (!document.getElementById("data_valid").checked) {
                Swal.fire({
                    title: "Peringatan!",
                    text: "Anda harus menyetujui pernyataan data sebelum submit.",
                    icon: "warning",
                    confirmButtonText: "OK",
                });
                return;
            }

            // Show loading
            Swal.fire({
                title: "Memproses...",
                text: "Mohon tunggu sebentar",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            // Simulate form submission (replace with actual AJAX call)
            setTimeout(() => {
                // Collect form data
                const formData = new FormData(document.getElementById("registrationForm"));

                // Add additional data
                formData.append("representative_name", document.getElementById("representative_name").value);
                formData.append("representative_nik", document.getElementById("representative_nik").value);
                formData.append("representative_birth_date", $("#representative_birth_date").val());
                formData.append("family_count", document.getElementById("family_count").value);
                formData.append("kk_number", document.getElementById("kk_number").value);

                // Here you would send formData to your PHP backend
                // Example: fetch('/php/contact.php', { method: 'POST', body: formData })

                Swal.fire({
                    title: "Berhasil!",
                    html: `
                    <p>Pendaftaran Anda telah berhasil dikirim.</p>
                    <p><strong>Nomor Registrasi:</strong> REG-${Date.now()}</p>
                    <p class="text-muted">Silakan simpan nomor registrasi untuk referensi Anda.</p>
                    `,
                    icon: "success",
                    confirmButtonText: "OK",
                }).then(() => {
                    // Reset form
                    document.getElementById("registrationForm").reset();
                    document.getElementById("fileInfo").style.display = "none";

                    // Reset datepicker
                    $("#representative_birth_date").val("");

                    // Reset peserta forms
                    document.getElementById("pesertaContainer").innerHTML = "";
                    pesertaCount = 0;
                    addPesertaForm();

                    // Scroll to top
                    window.scrollTo({
                        top: 0,
                        behavior: "smooth"
                    });
                });
            }, 2000);
        }
    </script>
@endpush
