@extends('layouts.app-v2')

@section('title', 'Homepage - ' . config('mudik.website.name'))
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
        ['label' => 'Beranda', 'href' => '#home', 'active' => true],
        ['label' => 'Persyaratan', 'href' => '#terms'],
        ['label' => 'Cara Daftar', 'href' => '#how-to'],
        ['label' => 'Daftar', 'href' => '#registration'],
      ]
    ])
@endsection
@section('content')
    <!-- Hero Section -->
    @include('components.hero', [
        'sectionId' => 'home',
        'slides' => [
        [
            'title' => 'Daftar Sekarang, Tempat Terbatas!',
            'subtitle' => 'Wujudkan Impian Mudik Bersama Keluarga<br>Program Pemerintah untuk Meringankan Beban Masyarakat',
            'buttonText' => 'Daftar Sekarang !',
            'buttonLink' => '#registration',
        ],
        [
            'title' => 'Program Mudik Gratis Lebaran 2026',
            'subtitle' => 'Komitmen Pemerintah dalam Memberikan Layanan Transportasi yang Layak<br>Aman, Nyaman, dan Terpercaya',
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
                                <b>30 Mei 2026 08:00 WIB</b>
                            </h5>
                            <h5 class="mb-0">
                                <span class="mdi mdi-map-marker"></span>
                                Lokasi Keberangkatan:
                            </h5>
                            <h5 class="mb-3 ps-4">
                                <b>Kantor Pemerintah Provinsi Banten</b>
                            </h5>
                            <h5 class="mb-0">
                                <span class="mdi mdi-ticket"></span>
                                Lokasi Penukaran Tiket:
                            </h5>
                            <h5 class="mb-3 ps-4">
                                <b>Lapangan Parkir Kantor Pemerintah Provinsi Banten</b>
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
@endsection
