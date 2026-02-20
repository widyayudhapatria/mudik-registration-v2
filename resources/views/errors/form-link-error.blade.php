@extends('layouts.app-v2')
@section('title', 'Link Tidak Valid - Mudik Gratis Banten')

@push('styles')
<style>
    .error-icon {
        font-size: 120px;
        color: #dc3545;
        margin-bottom: 30px;
    }

    .error-code {
        font-size: 80px;
        font-weight: 700;
        color: #dc3545;
        margin-bottom: 20px;
        line-height: 1;
    }

    .error-title {
        font-size: 32px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
    }

    .error-description {
        font-size: 18px;
        color: #666;
        line-height: 1.8;
        margin-bottom: 30px;
    }

    .info-box {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 20px;
        border-radius: 8px;
        margin: 30px 0;
        text-align: left;
    }

    .info-box h5 {
        color: #856404;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .info-box ul {
        margin-bottom: 0;
        padding-left: 20px;
    }

    .info-box li {
        color: #856404;
        margin-bottom: 8px;
        font-size: 16px;
    }

    .btn-home {
        padding: 15px 40px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .btn-home:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .contact-box {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 25px;
        margin-top: 30px;
    }

    .contact-box h5 {
        color: #333;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .contact-box p {
        color: #666;
        margin-bottom: 5px;
        font-size: 16px;
    }

    .section-error {
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        overflow: hidden;
    }

    body {
        overflow: hidden;
    }

    .error-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }

    .error-code {
        font-size: 60px;
        margin-bottom: 15px;
    }

    .error-title {
        font-size: 24px;
        margin-bottom: 15px;
    }

    .error-description {
        font-size: 16px;
        margin-bottom: 20px;
    }

    .info-box {
        padding: 15px;
        margin: 20px 0;
    }

    .info-box h5 {
        font-size: 16px;
        margin-bottom: 10px;
    }

    .info-box li {
        font-size: 14px;
        margin-bottom: 5px;
    }

    .btn-home {
        padding: 12px 30px;
        font-size: 16px;
        margin: 20px 0;
    }

    .contact-box {
        padding: 15px;
        margin-top: 20px;
    }

    .contact-box h5 {
        font-size: 16px;
        margin-bottom: 10px;
    }

    .contact-box p {
        font-size: 14px;
        margin-bottom: 1px;
    }

    @media (max-width: 768px) {
        .error-icon {
            font-size: 60px;
        }

        .error-code {
            font-size: 48px;
        }

        .error-title {
            font-size: 20px;
        }

        .error-description {
            font-size: 14px;
        }

        .info-box h5 {
            font-size: 14px;
        }

        .info-box li {
            font-size: 12px;
        }

        .btn-home {
            padding: 10px 25px;
            font-size: 14px;
        }

        .contact-box h5 {
            font-size: 14px;
        }

        .contact-box p {
            font-size: 12px;
        }
    }

    .error-code-subtitle {
        font-size: 12px;
        color: #adb5bd;
        font-family: monospace;
        margin-top: 0px;
        margin-bottom: 20px;
    }
    header, footer, .footer-alt { display: none !important; }
</style>
@endpush

@section('header')
    @include('components.header', [
        'menuItems' => [
            ['label' => 'Beranda', 'href' => route('public.landing'), 'active' => false],
        ]
    ])
@endsection

@section('content')
    <section class="section section-error bg-light" id="error">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <!-- Error Icon -->
                    <div class="error-icon">
                        <i class="mdi mdi-alert-circle-outline"></i>
                    </div>
                    <!-- Error Code -->
                    <h1 class="error-code">403</h1>
                    @if(isset($errorCode))
                        <div class="error-code-subtitle">
                            Error Code: {{ $errorCode }}
                        </div>
                    @endif
                    <!-- Error Title -->
                    <h2 class="error-title">
                        @if($errorCode === 'LINK_INVALID')
                            Link Pendaftaran Tidak Valid
                        @elseif($errorCode === 'LINK_EXPIRED')
                            Link Pendaftaran Sudah Kedaluwarsa
                        @elseif($errorCode === 'QUOTA_FULL')
                            Kuota Pendaftaran Penuh
                        @else
                            Link Pendaftaran Tidak Valid
                        @endif
                    </h2>
                    <!-- Error Description -->
                    <p class="error-description">
                         {{ $errorMessage }}
                    </p>
                    <!-- Info Box -->
                    <div class="info-box">
                        <h5>
                            <i class="mdi mdi-information-outline"></i>
                            Kemungkinan Penyebab:
                        </h5>
                        <ul>
                            @if($errorCode === 'QUOTA_FULL')
                                <li>Kuota pendaftaran untuk hari ini sudah penuh</li>
                                <li>Silakan coba lagi besok menggunakan link yang sama jika masih tersedia dan link tidak kadaluarsa</li>
                                <li>Kuota pendaftaran dibatasi untuk memastikan proses pendaftaran berjalan lancar dan adil bagi semua peserta</li>
                                <li>Atau pendaftaran sudah di tutup (kuota total sudah terpenuhi)</li>
                            @else
                                <li>Link sudah kadaluarsa (3 hari) atau sudah digunakan</li>
                                <li>Link hanya berlaku untuk 1 pendaftaran, jika sudah digunakan maka tidak bisa digunakan lagi</li>
                                <li>
                                    Gunakan email lain jika email sudah pernah valid dilakukan pendaftaran
                                    <b>(1 EMAIL = 1 LINK PENDAFTARAN)</b>
                                </li>
                                <li>Link rusak atau tidak lengkap saat di-copy</li>
                                <li>Terjadi kesalahan teknis saat mengakses link</li>
                            @endif
                        </ul>
                    </div>
                    <!-- Call to Action -->
                    <div class="mt-4">
                        <a href="{{ route('public.landing') }}" class="btn btn-custom btn-home">
                        <i class="mdi mdi-home-outline me-2"></i>
                        Kembali ke Halaman Utama
                        </a>
                    </div>
                    <!-- Contact Box -->
                    <div class="contact-box">
                        <h5>
                            <i class="mdi mdi-help-circle-outline"></i>
                            Butuh Bantuan?
                        </h5>
                        <p>
                            <i class="mdi mdi-email-outline"></i>
                            Email:
                            <a href="mailto:{{ config('mudik.support.email') }}">{{ config('mudik.support.email') }}</a>
                        </p>
                        <p>
                            <i class="mdi mdi-phone-outline"></i>
                            Telepon:
                            <a href="tel:{{ config('mudik.support.phone') }}">{{ config('mudik.support.phone') }}</a>
                        </p>
                        <p>
                            <i class="mdi mdi-clock-outline"></i>
                            {{ config('mudik.support.schedule') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
