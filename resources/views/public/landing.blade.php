@extends('layouts.app')

@section('title', 'Mudik Gratis Lebaran 2026 - Daftar Sekarang')

@push('styles')
<style>
    /* ... keep all existing styles ... */
    .hero-section {
        min-height: 100vh;
        background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 50%, #4CAF50 100%);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255,255,255,0.08) 0%, transparent 50%);
        pointer-events: none;
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
        color: white;
        text-align: center;
        padding: 60px 20px;
    }
    
    .hero-title {
        font-size: clamp(2.5rem, 8vw, 5rem);
        font-weight: 800;
        margin-bottom: 20px;
        line-height: 1.1;
        text-shadow: 2px 4px 8px rgba(0,0,0,0.3);
    }
    
    .hero-subtitle {
        font-size: clamp(1.1rem, 3vw, 1.5rem);
        font-weight: 300;
        margin-bottom: 15px;
        opacity: 0.95;
    }
    
    .hero-tagline {
        font-size: clamp(0.9rem, 2.5vw, 1.2rem);
        font-weight: 400;
        margin-bottom: 40px;
        opacity: 0.9;
    }
    
    .feature-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        padding: 8px 20px;
        border-radius: 50px;
        margin: 5px;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .cta-button {
        background: white;
        color: var(--primary-dark);
        padding: 18px 48px;
        font-size: 1.3rem;
        font-weight: 700;
        border-radius: 50px;
        border: none;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .cta-button:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 12px 32px rgba(0,0,0,0.3);
        color: var(--primary-dark);
    }
    
    .section {
        padding: 80px 20px;
    }
    
    .section-title {
        font-size: clamp(2rem, 5vw, 3rem);
        font-weight: 800;
        margin-bottom: 50px;
        text-align: center;
        color: var(--primary-dark);
    }
    
    .about-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 24px;
        padding: 40px;
        box-shadow: var(--shadow-md);
    }
    
    .about-text {
        font-size: 1.1rem;
        line-height: 1.8;
        color: var(--dark-text);
    }
    
    .facility-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: white;
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }
    
    .facility-item:hover {
        transform: translateX(10px);
        box-shadow: var(--shadow-md);
    }
    
    .facility-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    
    .requirement-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--shadow-md);
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .requirement-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
    }
    
    .requirement-number {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--secondary-dark) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 20px;
    }
    
    .step-card {
        position: relative;
        background: white;
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
    }
    
    .step-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
    }
    
    .step-number {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 20px;
        box-shadow: var(--shadow-md);
    }
    
    .timeline-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: var(--shadow-md);
        position: relative;
        margin-bottom: 30px;
    }
    
    .timeline-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--info-color) 0%, #01579B 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        position: absolute;
        top: 30px;
        left: -30px;
        box-shadow: var(--shadow-md);
    }
    
    .faq-item {
        background: white;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }
    
    .faq-item:hover {
        box-shadow: var(--shadow-md);
    }
    
    .faq-question {
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--primary-dark);
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    
    .faq-answer {
        color: var(--gray-text);
        line-height: 1.7;
        padding-left: 30px;
    }
    
    @media (max-width: 768px) {
        .timeline-icon {
            position: static;
            margin-bottom: 20px;
        }
        
        .hero-content {
            padding: 40px 15px;
        }
        
        .section {
            padding: 60px 15px;
        }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero-section" id="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title heading-font">MUDIK GRATIS LEBARAN 2026</h1>
            <p class="hero-subtitle">Wujudkan Impian Mudik Bersama Keluarga</p>
            <p class="hero-tagline">Program Pemerintah untuk Meringankan Beban Masyarakat</p>
            
            <div class="mb-4">
                <span class="feature-badge">
                    <i class="bi bi-check-circle-fill"></i> Gratis 100%
                </span>
                <span class="feature-badge">
                    <i class="bi bi-shield-fill-check"></i> Aman & Nyaman
                </span>
                <span class="feature-badge">
                    <i class="bi bi-bus-front-fill"></i> Bus Terbaru
                </span>
                <span class="feature-badge">
                    <i class="bi bi-star-fill"></i> Berpengalaman
                </span>
            </div>
            
            <button class="cta-button" data-bs-toggle="modal" data-bs-target="#emailModal">
                Daftar Sekarang
            </button>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section bg-light" id="about">
    <div class="container">
        <h2 class="section-title heading-font">TENTANG PROGRAM MUDIK GRATIS</h2>
        
        <div class="about-card">
            <p class="about-text">
                Program Mudik Gratis Lebaran 2026 adalah inisiatif pemerintah untuk membantu
                masyarakat yang ingin pulang kampung merayakan Hari Raya Idul Fitri bersama
                keluarga. Program ini menyediakan transportasi bus gratis dengan fasilitas
                yang nyaman dan aman.
            </p>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="h4 fw-bold mb-4 text-center">FASILITAS</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="facility-item">
                            <div class="facility-icon">
                                <i class="bi bi-snow"></i>
                            </div>
                            <div>
                                <strong>Bus Pariwisata AC</strong>
                                <p class="mb-0 small text-muted">Kenyamanan maksimal selama perjalanan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="facility-item">
                            <div class="facility-icon">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div>
                                <strong>Sopir Berpengalaman</strong>
                                <p class="mb-0 small text-muted">Driver profesional dan bersertifikat</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="facility-item">
                            <div class="facility-icon">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                            <div>
                                <strong>Asuransi Perjalanan</strong>
                                <p class="mb-0 small text-muted">Perlindungan penuh selama perjalanan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="facility-item">
                            <div class="facility-icon">
                                <i class="bi bi-cup-hot-fill"></i>
                            </div>
                            <div>
                                <strong>Konsumsi Perjalanan</strong>
                                <p class="mb-0 small text-muted">Makanan dan minuman tersedia</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="facility-item">
                            <div class="facility-icon">
                                <i class="bi bi-house-fill"></i>
                            </div>
                            <div>
                                <strong>Rest Area Rutin</strong>
                                <p class="mb-0 small text-muted">Istirahat setiap 4 jam perjalanan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="facility-item">
                            <div class="facility-icon">
                                <i class="bi bi-hospital-fill"></i>
                            </div>
                            <div>
                                <strong>Tim Medis Standby</strong>
                                <p class="mb-0 small text-muted">Petugas kesehatan siaga 24 jam</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Requirements Section -->
<section class="section" id="requirements">
    <div class="container">
        <h2 class="section-title heading-font">PERSYARATAN PENDAFTARAN</h2>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="requirement-card">
                    <div class="requirement-number">1</div>
                    <p class="mb-0">Memiliki Kartu Keluarga (KK) yang masih berlaku</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="requirement-card">
                    <div class="requirement-number">2</div>
                    <p class="mb-0">Setiap anggota keluarga memiliki KTP atau KIA</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="requirement-card">
                    <div class="requirement-number">3</div>
                    <p class="mb-0">Anak dibawah 4 tahun wajib dipangku selama perjalanan</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="requirement-card">
                    <div class="requirement-number">4</div>
                    <p class="mb-0">Satu keluarga hanya dapat mendaftar 1 kali</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="requirement-card">
                    <div class="requirement-number">5</div>
                    <p class="mb-0">Memiliki email aktif untuk menerima notifikasi</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="requirement-card">
                    <div class="requirement-number">6</div>
                    <p class="mb-0">Bersedia mengikuti semua ketentuan yang berlaku</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How to Register Section -->
<section class="section bg-light" id="how-to">
    <div class="container">
        <h2 class="section-title heading-font">CARA PENDAFTARAN</h2>
        <p class="text-center mb-5 fs-5">Ikuti 4 langkah mudah berikut:</p>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4 class="fw-bold mb-3">Masukkan Email</h4>
                    <p class="text-muted">Klik tombol "DAFTAR" dan masukkan email aktif Anda</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4 class="fw-bold mb-3">Terima Link</h4>
                    <p class="text-muted">Cek inbox email Anda dan klik link pendaftaran yang dikirim</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4 class="fw-bold mb-3">Isi Form</h4>
                    <p class="text-muted">Lengkapi formulir pendaftaran dengan data yang benar dan lengkap</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h4 class="fw-bold mb-3">Terima QR Code</h4>
                    <p class="text-muted">Jika disetujui, Anda akan menerima QR Code untuk penukaran tiket</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="section" id="timeline">
    <div class="container">
        <h2 class="section-title heading-font">TIMELINE PENDAFTARAN</h2>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="timeline-card">
                    <div class="timeline-icon d-none d-md-flex">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="timeline-icon d-flex d-md-none mx-auto">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <h4 class="fw-bold mb-2">📅 Pendaftaran Dibuka</h4>
                    <p class="text-muted mb-0">1 - 15 Februari 2026</p>
                </div>
                
                <div class="timeline-card">
                    <div class="timeline-icon d-none d-md-flex">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="timeline-icon d-flex d-md-none mx-auto">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h4 class="fw-bold mb-2">✅ Verifikasi Data</h4>
                    <p class="text-muted mb-0">Maksimal 2x24 jam setelah submit</p>
                </div>
                
                <div class="timeline-card">
                    <div class="timeline-icon d-none d-md-flex">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                    <div class="timeline-icon d-flex d-md-none mx-auto">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                    <h4 class="fw-bold mb-2">🎫 Penukaran Tiket</h4>
                    <p class="text-muted mb-2">10 - 12 Maret 2026</p>
                    <p class="small text-muted mb-0">Lokasi: [Alamat Lengkap]</p>
                </div>
                
                <div class="timeline-card">
                    <div class="timeline-icon d-none d-md-flex">
                        <i class="bi bi-bus-front-fill"></i>
                    </div>
                    <div class="timeline-icon d-flex d-md-none mx-auto">
                        <i class="bi bi-bus-front-fill"></i>
                    </div>
                    <h4 class="fw-bold mb-2">🚌 Keberangkatan</h4>
                    <p class="text-muted mb-0">15 Maret 2026, Pukul 06:00 WIB</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section bg-light" id="faq">
    <div class="container">
        <h2 class="section-title heading-font">PERTANYAAN YANG SERING DIAJUKAN</h2>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="bi bi-question-circle-fill text-primary"></i>
                        <span>Apakah pendaftaran benar-benar gratis?</span>
                    </div>
                    <div class="faq-answer">
                        Ya, seluruh proses pendaftaran dan perjalanan mudik 100% GRATIS.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="bi bi-question-circle-fill text-primary"></i>
                        <span>Berapa kuota yang tersedia?</span>
                    </div>
                    <div class="faq-answer">
                        Kuota terbatas dan dibatasi per hari. Pendaftaran first come first served.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="bi bi-question-circle-fill text-primary"></i>
                        <span>Apa yang harus dibawa saat penukaran tiket?</span>
                    </div>
                    <div class="faq-answer">
                        QR Code (cetak/digital), KTP asli perwakilan, dan KK asli.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="bi bi-question-circle-fill text-primary"></i>
                        <span>Apakah bisa membatalkan pendaftaran?</span>
                    </div>
                    <div class="faq-answer">
                        Setelah disetujui, pembatalan hanya bisa dilakukan minimal H-3 dengan menghubungi customer service kami.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="bi bi-question-circle-fill text-primary"></i>
                        <span>Bagaimana jika link pendaftaran expired?</span>
                    </div>
                    <div class="faq-answer">
                        Anda dapat submit ulang email di website untuk mendapatkan link baru.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="bi bi-question-circle-fill text-primary"></i>
                        <span>Apakah anak harus memiliki tiket sendiri?</span>
                    </div>
                    <div class="faq-answer">
                        Anak dibawah 4 tahun tidak mendapat kursi sendiri (dipangku). Anak 4 tahun ke atas mendapat kursi sendiri.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="bi bi-question-circle-fill text-primary"></i>
                        <span>Bagaimana jika data ditolak?</span>
                    </div>
                    <div class="faq-answer">
                        Anda akan menerima email berisi alasan penolakan dan dapat mendaftar ulang setelah melakukan perbaikan data.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);">
    <div class="container text-center">
        <h2 class="heading-font text-white mb-4" style="font-size: clamp(2rem, 5vw, 3rem);">
            JANGAN LEWATKAN KESEMPATAN INI!
        </h2>
        <p class="text-white fs-5 mb-4">Daftar sekarang sebelum kuota habis</p>
        <button class="cta-button" data-bs-toggle="modal" data-bs-target="#emailModal">
            Daftar Sekarang
        </button>
    </div>
</section>

<!-- Email Submission Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">DAFTAR MUDIK GRATIS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="alert-container"></div>
                
                <p class="text-muted mb-4">Masukkan email aktif Anda untuk menerima link formulir pendaftaran</p>
                
                <form id="emailForm">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email aktif:</label>
                        <input 
                            type="email" 
                            class="form-control form-control-lg" 
                            id="email" 
                            name="email"
                            placeholder="contoh@gmail.com"
                            required
                            style="border-radius: 12px;"
                        >
                        <div class="form-text">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            Pastikan email yang dimasukkan aktif dan dapat menerima email dari sistem
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 12px;">
                            <span class="btn-text">KIRIM</span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Mengirim...
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

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <div class="mx-auto" style="width: 80px; height: 80px; background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-check-lg text-white" style="font-size: 3rem;"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3">✅ EMAIL BERHASIL TERKIRIM</h4>
                <p class="mb-2">Link formulir pendaftaran telah dikirim ke email Anda</p>
                <p class="text-muted mb-2" id="successEmail"></p>
                <p class="small text-muted mb-4">Silahkan cek inbox atau spam folder Anda.<br>Link akan kadaluarsa dalam 3 hari.</p>
                <p class="fw-semibold mb-4">Terima kasih!</p>
                <button type="button" class="btn btn-primary px-5" data-bs-dismiss="modal" style="border-radius: 12px;">
                    OK!
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    const emailForm = document.getElementById('emailForm');
    const emailModalEl = document.getElementById('emailModal');
    const successModalEl = document.getElementById('successModal');
    
    if (!emailForm || !emailModalEl || !successModalEl) {
        console.error('Required elements not found');
        return;
    }
    
    const emailModal = new bootstrap.Modal(emailModalEl);
    const successModal = new bootstrap.Modal(successModalEl);
    
    // Show error function
    const showError = (message) => {
        const alertContainer = document.getElementById('alert-container');
        if (!alertContainer) return;
        
        alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Error!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    };
    
    // Form submit handler
    emailForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        const emailInput = this.querySelector('#email');
        const alertContainer = document.getElementById('alert-container');
        
        if (!submitBtn || !btnText || !btnLoading || !emailInput) {
            console.error('Form elements not found');
            return;
        }
        
        // Clear previous errors
        if (alertContainer) alertContainer.innerHTML = '';
        
        // Disable button
        submitBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
        
        try {
            const response = await axios.post('/public/submit-email', {
                email: emailInput.value
            });
            
            if (response.data && response.data.success) {
                // Close email modal
                emailModal.hide();
                
                // Show success modal
                const successEmailEl = document.getElementById('successEmail');
                if (successEmailEl) {
                    successEmailEl.textContent = emailInput.value;
                }
                successModal.show();
                
                // Reset form
                emailForm.reset();
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
            // Enable button
            submitBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
        }
    });
    
    // Reset form when modal is hidden
    emailModalEl.addEventListener('hidden.bs.modal', function() {
        emailForm.reset();
        const alertContainer = document.getElementById('alert-container');
        if (alertContainer) alertContainer.innerHTML = '';
    });
})();
</script>
@endpush