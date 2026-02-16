<!-- Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="registrationForm" class="mb-2">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="registrationModalLabel">Pendaftaran Mudik Gratis</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-text mb-2 fs-6 fw-semibold">Masukkan email aktif Anda untuk menerima link formulir pendaftaran.</div>
                    <div class="mb-2">
                        <input type="email" class="form-control mb-0" id="email" name="email" placeholder="Masukkan Email Aktif Anda" required />
                    </div>
                    <div class="form-text fs-6 text-danger fw-semibold">
                        <span class="mdi mdi-alert-outline"></span>
                        Pastikan email yang dimasukkan aktif dan dapat menerima email dari sistem.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-custom">
                        <span class="btn-text">Daftar</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Proses Mengirim...
                        </span>
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    (function() {
        'use strict';

        const registrationForm = document.getElementById('registrationForm');
        const emailModalEl = document.getElementById('registrationModal');
        const emailModal = new bootstrap.Modal(emailModalEl);

        // Check if elements exist
        if (!registrationForm || !emailModalEl) {
            console.error('Required elements not found');
            return;
        }

        // Show error function
        const showError = (message) => {
            Swal.fire({
                title: "Error!",
                text: message,
                icon: "error",
                confirmButtonText: "OK",
            });
        };

        // Handle form submission
        registrationForm.addEventListener('submit', async function(event) {
            console.log('Form submitted'); // Debug log
            event.preventDefault();


            // Get form elements
            const submitBtn = this.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            const emailInput = this.querySelector('#email');

            // Check if elements exist
            if (!submitBtn || !btnText || !btnLoading || !emailInput) {
                console.error('Required form elements not found');
                return;
            }

            // Disable submit button and show loading state
            submitBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');

            // Validate email
            const email = emailInput.value.trim();
            if (!email) {
                showError("Silakan masukkan alamat email yang valid.");
                // Re-enable submit button and hide loading state
                submitBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
                return;
            }

            try {
                // Ajax request to send email
                const response = await axios.post('/public/submit-email', {
                    email: emailInput.value
                });

                // Check response status
                if (response.data && response.data.success) {
                    // Close email modal
                    emailModal.hide();

                    // Show success message
                    Swal.fire({
                        title: "Berhasil!",
                        html: response.data.message,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        icon: "success",
                        confirmButtonText: "Baik, saya mengerti.",
                    }).then(() => {
                        // Close modal
                        emailModal.hide();
                        // Reset form
                        registrationForm.reset();
                    });

                    // Reset form
                    registrationForm.reset();
                } else {
                    showError(response.data.message || "Gagal mengirim email. Silakan coba lagi.");
                }

            } catch (error) {
                console.error('Submit error:', error);

                let errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';

                if (error.response) {
                    if (error.response.status === 419) {
                        errorMessage = 'Session telah kadaluarsa. Silakan refresh halaman dan coba lagi.';
                    } else if (error.response.data && error.response.data.message) {
                        errorMessage = error.response.data.message;
                    }
                } else if (error.request) {
                    errorMessage = 'Tidak dapat menghubungi server. Periksa koneksi internet Anda.';
                }

                showError(errorMessage);
            } finally {
                // Re-enable submit button and hide loading state
                submitBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
            }
        });
    })();
</script>
@endpush
