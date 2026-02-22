@extends('layouts.app-v2')

@section('title', 'Homepage - ' . config('mudik.website.name'))
@push('styles')
    <style>
        /* Registration Period Alert */
        .alert-period {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            padding: 12px 20px;
            transform: translateY(-100%);
            animation: slideDown 0.5s ease 0.8s forwards;
        }

        .alert-period--success {
            background: linear-gradient(135deg, rgba(26,122,74,0.55), rgba(40,167,69,0.55));
            backdrop-filter: blur(4px);
        }
        .alert-period--warning {
            background: linear-gradient(135deg, rgba(197,124,0,0.55), rgba(255,193,7,0.55));
            backdrop-filter: blur(4px);
        }
        .alert-period--danger  {
            background: linear-gradient(135deg, rgba(167,29,42,0.55), rgba(220,53,69,0.55));
            backdrop-filter: blur(4px);
        }

        .alert-period__inner {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }

        .alert-period--warning .alert-period__inner { color: #1a1a1a; }

        .alert-period__icon { font-size: 1.3rem; flex-shrink: 0; }

        .alert-period__text {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 0.9rem;
        }

        .alert-period__text strong:first-child { font-size: 1rem; }

        .alert-period__close {
            background: rgba(255,255,255,0.25);
            border: none;
            color: inherit;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: 1.1rem;
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .alert-period__close:hover { background: rgba(255,255,255,0.4); }

        .alert-period.hide {
            animation: slideUp 0.4s ease forwards;
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to   { transform: translateY(0); }
        }

        @keyframes slideUp {
            from { transform: translateY(0); }
            to   { transform: translateY(-100%); }
        }

        @media (max-width: 576px) {
            .alert-period__text { font-size: 0.8rem; }
            .alert-period__text strong:first-child { font-size: 0.9rem; }
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #de5d00 0%, #ff9634 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.3rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
@endpush
@section('header')
    <button class="back-to-top" id="backToTop" title="Kembali ke atas">
        <i class="mdi mdi-arrow-up"></i>
    </button>
    @include('components.header', [
      'menuItems' => [
        ['label' => 'Beranda', 'href' => '#home', 'active' => true],
        ['label' => 'Persyaratan', 'href' => '#terms'],
        ['label' => 'Cara Daftar', 'href' => '#how-to'],
        ['label' => 'Daftar', 'href' => '#registration'],
      ]
    ])
@endsection
@section('content')
    {{-- Registration Period Alert --}}
    @php
        $regStart = \Carbon\Carbon::parse(config('mudik.registration.start_date'));
        $regEnd   = \Carbon\Carbon::parse(config('mudik.registration.end_date'));
        $now      = \Carbon\Carbon::now();
    @endphp

    @if($now->lt($regStart))
        <div class="alert-period alert-period--warning" id="registrationAlert">
            <div class="alert-period__inner">
                <span class="alert-period__icon">🕐</span>
                <div class="alert-period__text">
                    <strong>Pendaftaran Belum Dibuka</strong>
                    <span>Pendaftaran akan dibuka pada <strong>{{ $regStart->translatedFormat('d F Y, H:i') }} WIB</strong></span>
                </div>
                <button class="alert-period__close" onclick="closeAlert()">&times;</button>
            </div>
        </div>
    @elseif($now->gt($regEnd))
        <div class="alert-period alert-period--danger" id="registrationAlert">
            <div class="alert-period__inner">
                <span class="alert-period__icon">🔴</span>
                <div class="alert-period__text">
                    <strong>Pendaftaran Telah Ditutup</strong>
                    <span>Periode pendaftaran telah berakhir pada <strong>{{ $regEnd->translatedFormat('d F Y, H:i') }} WIB</strong></span>
                </div>
                <button class="alert-period__close" onclick="closeAlert()">&times;</button>
            </div>
        </div>
    @else
        <div class="alert-period alert-period--success" id="registrationAlert">
            <div class="alert-period__inner">
                <span class="alert-period__icon">🟢</span>
                <div class="alert-period__text">
                    <strong>Pendaftaran Sedang Dibuka</strong>
                    <span>Daftarkan diri Anda sebelum <strong>{{ $regEnd->translatedFormat('d F Y, H:i') }} WIB</strong></span>
                </div>
                <button class="alert-period__close" onclick="closeAlert()">&times;</button>
            </div>
        </div>
    @endif

    <!-- Hero Section -->
    @include('components.hero', [
        'sectionId' => 'home',
        'slides' => [
            [
                'title' => 'Daftar Sekarang, Tempat Terbatas!',
                'subtitle' => 'Wujudkan Impian Mudik Bersama Keluarga<br>Program Pemerintah Kabupaten Tangerang<br/> untuk Meringankan Beban Masyarakat',
                'buttonText' => 'Daftar Sekarang !',
                'buttonLink' => '#registration',
            ],
            [
                'title' => 'Program Mudik Kabupaten Tangerang 2026',
                'subtitle' => 'Komitmen Pemerintah dalam Memberikan Layanan Transportasi mudik yang Layak<br>Aman, Nyaman, dan Terpercaya',
                'buttonText' => 'Daftar Sekarang !',
                'buttonLink' => '#registration',
            ],
        ]
    ])

    <!-- Terms Section -->
    <section class="section" id="terms">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="title text-center">
                    <h2>
                        Syarat
                        <b>Pendaftaran</b>
                    </h2>
                    <span class="title-border"><i class="mdi mdi-set-none"></i></span>
                    </div>
                </div>
            </div>
            <div class="row mt-4 pt-4 justify-content-center">
                <div class="col-lg-6 text-left">
                    <div class="features-desc px-2 px-lg-0">
                        <h5 class="mb-3">
                            <span class="mdi mdi-check-all"></span>
                            Wajib memiliki
                            <b>Kartu Keluarga (KK)</b>
                            yang
                            <b>masih berlaku</b>
                            .
                        </h5>
                        <h5 class="mb-3">
                            <span class="mdi mdi-check-all"></span>
                            Peserta mudik harus berasal dari
                            <b>satu Kartu Keluarga</b>
                            (tidak diperbolehkan menggabungkan keluarga berbeda).
                        </h5>
                        <h5 class="mb-3">
                            <span class="mdi mdi-check-all"></span>
                            Setiap anggota keluarga wajib memiliki
                            <b>KTP</b>
                            atau
                            <b>Kartu Identitas Anak (KIA)</b>
                            .
                        </h5>
                        <h5 class="mb-3">
                            <span class="mdi mdi-check-all"></span>
                            <b>Anak di bawah usia 4 tahun</b>
                            wajib
                            <b>dipangku</b>
                            selama perjalanan.
                        </h5>
                        <h5 class="mb-3">
                            <span class="mdi mdi-check-all"></span>
                            <b>Satu keluarga</b>
                            hanya diperbolehkan
                            <b>mendaftar 1 (satu) kali</b>
                            .
                        </h5>
                        <h5 class="mb-3">
                            <span class="mdi mdi-check-all"></span>
                            Wajib memiliki
                            <b>email aktif</b>
                            untuk menerima
                            <b>informasi dan notifikasi</b>
                            .
                        </h5>
                        <h5 class="mb-3">
                            <span class="mdi mdi-check-all"></span>
                            Peserta
                            <b>bersedia dan menyetujui</b>
                            untuk mengikuti
                            <b>seluruh ketentuan</b>
                            yang berlaku.
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How-to Section -->
    <section id="how-to" class="section bg-custom pt-5 pb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="title text-center">
                        <h2 class="text-white">
                            Alur
                            <b>Pendaftaran</b>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="row pt-2 mt-2">
                <div class="col-lg-3 mt-3">
                    <div class="service-box bg-white clearfix p-3 p-lg-4">
                        <div class="service-icon service-left">
                            <i class="mdi mdi-numeric-1-box-outline"></i>
                        </div>
                        <div class="service-desc service-left">
                            <h4>Masukkan Email</h4>
                            <p class="text-muted mb-0">Klik tombol "DAFTAR" dan masukkan email aktif Anda.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mt-3">
                    <div class="service-box bg-white clearfix p-3 p-lg-4">
                        <div class="service-icon service-left">
                            <i class="mdi mdi-numeric-2-box-outline"></i>
                        </div>
                        <div class="service-desc service-left">
                            <h4>Cek Inbox</h4>
                            <p class="text-muted mb-0">Cek inbox email Anda dan klik link pendaftaran yang dikirim.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mt-3">
                    <div class="service-box bg-white clearfix p-3 p-lg-4">
                        <div class="service-icon service-left">
                            <i class="mdi mdi-numeric-3-box-outline"></i>
                        </div>
                        <div class="service-desc service-left">
                            <h4>Isi Form</h4>
                            <p class="text-muted mb-0">Isi formulir dengan data yang benar dan lengkap.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mt-3">
                    <div class="service-box bg-white clearfix p-3 p-lg-4">
                        <div class="service-icon service-left">
                            <i class="mdi mdi-numeric-4-box-outline"></i>
                        </div>
                        <div class="service-desc service-left">
                            <h4>Terima QR Code</h4>
                            <p class="text-muted mb-0">Jika disetujui, Anda menerima QR Code untuk ditukar Tiket.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How-to Registration Section -->
    <section class="section" id="how-to-registration">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="title text-center">
                        <h2>
                            Cara
                            <b>Pendaftaran</b>
                        </h2>
                        <span class="title-border"><i class="mdi mdi-set-none"></i></span>
                    </div>
                </div>
            </div>
            <div class="row mt-4 pt-4 justify-content-center">
                <div class="col-lg-6 text-left">
                    <div class="features-desc px-2 px-lg-0">
                        <h5 class="mb-3">
                            1. Klik tombol
                            <b>DAFTAR</b>
                            lalu masukkan
                            <b>alamat email yang aktif</b>
                            dan dapat dihubungi.
                        </h5>
                        <h5 class="mb-3">
                            2. Periksa
                            <b>kotak masuk email</b>
                            Anda, termasuk folder
                            <b>Spam/Junk</b>
                            , lalu klik
                            <b>tautan pendaftaran</b>
                            yang telah dikirimkan.
                        </h5>
                        <h5 class="mb-3">
                            3. Lengkapi formulir pendaftaran dengan data yang
                            <b>benar</b>
                            ,
                            <b>lengkap</b>
                            , dan
                            <b>sesuai dengan dokumen Kartu Keluarga (KK)</b>
                            .
                        </h5>
                        <h5 class="mb-3">
                            4. Tunggu proses
                            <b>verifikasi oleh admin</b>
                            maksimal
                            <b>2 × 24 jam</b>
                            .
                        </h5>
                        <h5 class="mb-3">
                            5. Setelah pendaftaran
                            <b>disetujui</b>
                            ,
                            <b>QR Code</b>
                            akan dikirimkan melalui
                            <b>email</b>
                            .
                        </h5>
                        <h5 class="mb-3">
                            6. Tunjukkan
                            <b>QR Code</b>
                            tersebut saat proses
                            <b>penukaran tiket</b>
                            .
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Registration Section -->
    <section class="section bg-light" id="registration">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="title text-center">
                        <h2><b>Pendaftaran</b></h2>
                        <span class="title-border"><i class="mdi mdi-set-none"></i></span>
                    </div>
                </div>
            </div>
            <div class="row mt-4 pt-4 justify-content-center">
                <div class="col-lg-4 mt-3">
                    <div class="team-box bg-white p-3 p-lg-4">
                        <div class="team-desc text-left">
                            <h5 class="team-name text-uppercase text-custom mb-4 text-center">Informasi Penting</h5>
                            <h5 class="mb-0">
                                <span class="mdi mdi-calendar-range"></span>
                                Tanggal Keberangkatan:
                            </h5>
                            <h5 class="mb-3 ps-4">
                                <b>{{ config('mudik.schedule.departure_date') }} {{ config('mudik.schedule.departure_time') }} WIB</b>
                            </h5>
                            <h5 class="mb-0">
                                <span class="mdi mdi-map-marker"></span>
                                Lokasi Keberangkatan:
                            </h5>
                            <h5 class="mb-3 ps-4">
                                <b>{{ config('mudik.locations.departure_location') }}</b>
                            </h5>
                            <h5 class="mb-0">
                                <span class="mdi mdi-ticket"></span>
                                Lokasi Penukaran Tiket:
                            </h5>
                            <h5 class="mb-3 ps-4">
                                <b>{{ config('mudik.locations.ticket_exchange_location') }}</b>
                            </h5>
                            <h5 class="mb-0">
                                <span class="mdi mdi-phone"></span>
                                Kontak:
                            </h5>
                            <h5 class="mb-3 ps-4">
                                <b>{{ config('mudik.support.phone') }} / {{ config('mudik.support.email') }}</b>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 mt-5 text-center">
                    <button type="button" id="btn-register" class="submitBnt btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#registrationModal">DAFTAR SEKARANG !</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Registration Modal -->
    @include('components.registration-modal')

    @push('scripts')
    <script>

    function closeAlert() {
        const alert = document.getElementById('registrationAlert');
        if (!alert) return;
        alert.classList.add('hide');
        alert.addEventListener('animationend', () => alert.remove(), { once: true });
    }

    @if($now->between($regStart, $regEnd))
        setTimeout(closeAlert, 8000);
    @endif

    (function () {
        const backToTop = document.getElementById('backToTop');

        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('show', window.scrollY > 300);
        });

        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
    </script>
    @endpush
@endsection
