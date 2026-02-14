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
                                    <div class="upload-area border rounded p-4 text-center" id="uploadArea">
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
                                id="tambahPesertaBtn"
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
        /**
         * State management untuk registration form
         */
        window.registrationState = {
            pesertaCount: 0,
            maxPeserta: 10,
        };

        // Function to initialize form utilities on page load
        function initializeForm() {
            // Initialize datepicker for representative birth date
            initializeDatepicker("#representative_birth_date");

            // Add first participant form
            addPesertaForm();
        }

        // Initialize form when DOM is ready
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize datepicker for representative birth date
            initializeForm();
        });

         /**
         * Template generator untuk single peserta form
         */
        function pesertaFormTemplate(pesertaCount) {
            const removeButton = pesertaCount > 1
            ? `<button type="button" class="btn btn-danger btn-sm remove-peserta px-2 py-0" data-id="${pesertaCount}">
                <i class="mdi mdi-close-circle-outline fs-5"></i>
                </button>`
            : '';

            return `
            <div class="peserta-form border rounded p-3 mb-3 position-relative" data-peserta-id="${pesertaCount}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-semibold">Peserta ${pesertaCount}</h5>
                ${removeButton}
                </div>

                <div class="mb-3">
                <label for="namaPeserta${pesertaCount}" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control"
                    id="namaPeserta${pesertaCount}"
                    name="namaPeserta[]"
                    placeholder="nama lengkap"
                    required
                >
                </div>

                <div class="mb-3">
                <label for="ktpPeserta${pesertaCount}" class="form-label">KTP / KIA <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control ktp-input"
                    id="ktpPeserta${pesertaCount}"
                    name="ktpPeserta[]"
                    placeholder="nomor KTP / KIA"
                    maxlength="16"
                    required
                >
                <div class="form-text">Masukkan nomor KTP (16 digit) atau KIA</div>
                </div>

                <div class="mb-3">
                <label for="tanggalLahirPeserta${pesertaCount}" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control datepicker-input"
                    id="tanggalLahirPeserta${pesertaCount}"
                    name="tanggalLahirPeserta[]"
                    placeholder="dd/mm/yyyy"
                    required
                    readonly
                >
                </div>
            </div>
            `;
        }

         /**
         * Tambah peserta form baru
         */
        function addPesertaForm() {
            const state = window.registrationState;

            if (state.pesertaCount >= state.maxPeserta) {
                Swal.fire({
                    title: "Peringatan!",
                    text: `Maksimal ${state.maxPeserta} peserta yang dapat didaftarkan.`,
                    icon: "warning",
                    confirmButtonText: "OK",
                });
                return;
            }

            state.pesertaCount++;
            const pesertaContainer = document.getElementById("pesertaContainer");

            const pesertaDiv = document.createElement("div");
            pesertaDiv.innerHTML = pesertaFormTemplate(state.pesertaCount);

            pesertaContainer.appendChild(pesertaDiv.firstElementChild);

            // Attach event listeners
            const removeBtn = pesertaContainer.querySelector(`[data-peserta-id="${state.pesertaCount}"] .remove-peserta`);
            if (removeBtn) {
            removeBtn.addEventListener("click", function () {
                removePesertaForm(this.dataset.id);
            });
            }

            const ktpInput = pesertaContainer.querySelector(`#ktpPeserta${state.pesertaCount}`);
            if (ktpInput) {
            ktpInput.addEventListener("input", formatNumber);
            }

            initializeDatepicker(`#tanggalLahirPeserta${state.pesertaCount}`);
            updateTambahPesertaButton();
        }

        /**
         * Hapus peserta form dengan konfirmasi
         */
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
                    window.registrationState.pesertaCount--;
                    updatePesertaNumbers();
                    updateTambahPesertaButton();
                    }
                });
            }
        }

        /**
         * Update nomor peserta setelah ada yang dihapus
         */
        function updatePesertaNumbers() {
            const pesertaForms = document.querySelectorAll(".peserta-form");
            pesertaForms.forEach((form, index) => {
                const header = form.querySelector("h5.fw-semibold");
                if (header) {
                    header.textContent = `Peserta ${index + 1}`;
                }
            });
        }

        /**
         * Update status tombol tambah peserta
         */
        function updateTambahPesertaButton() {
            const btn = document.getElementById("tambahPesertaBtn");
            const { pesertaCount, maxPeserta } = window.registrationState;

            if (pesertaCount >= maxPeserta) {
                btn.disabled = true;
                btn.innerHTML = `<i class="mdi mdi-information-outline"></i> Maksimal ${maxPeserta} peserta`;
            } else {
                btn.disabled = false;
                btn.innerHTML = `<i class="mdi mdi-plus-circle"></i> Tambah Peserta`;
            }
        }

        /**
         * Handle checkbox tambahkan perwakilan ke peserta
         */
        function handleTambahkanPerwakilan(e) {
            if (e.target.checked) {
                const nama = document.getElementById("namaLengkapPerwakilan").value;
                const ktp = document.getElementById("nomorKTPPerwakilan").value;
                const tanggalLahir = $("#tanggalLahirPerwakilan").val();

                if (window.registrationState.pesertaCount > 0) {
                    document.getElementById("namaPeserta1").value = nama;
                    document.getElementById("ktpPeserta1").value = ktp;
                    $("#tanggalLahirPeserta1").val(tanggalLahir);

                    document.getElementById("namaPeserta1").setAttribute("readonly", true);
                    document.getElementById("ktpPeserta1").setAttribute("readonly", true);
                    $("#tanggalLahirPeserta1").prop("readonly", true).addClass("readonly-datepicker");
                }
            } else {
                if (window.registrationState.pesertaCount > 0) {
                    document.getElementById("namaPeserta1").removeAttribute("readonly");
                    document.getElementById("ktpPeserta1").removeAttribute("readonly");
                    $("#tanggalLahirPeserta1").prop("readonly", false).removeClass("readonly-datepicker");
                    document.getElementById("namaPeserta1").value = "";
                    document.getElementById("ktpPeserta1").value = "";
                    $("#tanggalLahirPeserta1").val("");
                }
            }
        }

        /**
         * Format nomor input (hanya angka, maksimal 16 digit)
         */
        function formatNumber(e) {
            e.target.value = e.target.value.replace(/\D/g, "").slice(0, 16);
        }
    </script>
@endpush

{{--
@include('components.form-utilities-file-upload')
@include('components.form-utilities-peserta')
@include('components.form-utilities-validation')
@include('components.form-utilities-init') --}}
