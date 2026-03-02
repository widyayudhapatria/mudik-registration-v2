@extends('layouts.app-v2')

@section('title', 'Syarat & Ketentuan - ' . config('mudik.website.name'))

@section('content')
<section class="section">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="mb-5">
                    <h2 class="mb-3"><b>Syarat & Ketentuan</b></h2>
                    <p><i>Pembaruan Terakhir: Februari 2026</i></p>

                    <p>
                        Selamat datang di portal pendaftaran mudik resmi
                        {{ config('mudik.website.name') }}.
                        Dengan mengakses dan menggunakan layanan ini,
                        Anda dianggap telah membaca, memahami,
                        dan menyetujui seluruh ketentuan berikut.
                    </p>

                    <ul class="mt-4">
                        <li>
                            <b>Persyaratan Peserta:</b>
                            Calon pemudik wajib memiliki identitas resmi yang berlaku
                            (KTP/KK). Data yang diinput harus sesuai dengan dokumen asli.
                        </li>

                        <li>
                            <b>Ketentuan Kuota:</b>
                            Pendaftaran menggunakan sistem
                            <i>first-come, first-served</i>.
                            Kuota terbatas dan akan ditutup otomatis jika penuh.
                        </li>

                        <li>
                            <b>Verifikasi Data:</b>
                            Penyelenggara berhak membatalkan pendaftaran
                            apabila ditemukan manipulasi data,
                            penggunaan identitas palsu,
                            atau pendaftaran ganda pada rute yang sama.
                        </li>

                        <li>
                            <b>Tiket & Keberangkatan:</b>
                            Bukti pendaftaran bersifat non-transferable
                            dan tidak dapat diuangkan.
                            Peserta wajib hadir minimal 60 menit sebelum keberangkatan.
                        </li>

                        <li>
                            <b>Pembatalan:</b>
                            Pembatalan dilakukan melalui fitur
                            <b>Batal Daftar</b>
                            selambat-lambatnya H-3 sebelum keberangkatan.
                        </li>
                    </ul>
                </div>

                <hr class="my-5">

                <div>
                    <h2 class="mb-3"><b>Kebijakan Privasi</b></h2>

                    <p>
                        Kami berkomitmen melindungi data pribadi peserta
                        sesuai dengan peraturan perundang-undangan yang berlaku di Indonesia.
                    </p>

                    <ul class="mt-4">
                        <li>
                            <b>Data yang Dikumpulkan:</b>
                            Nama lengkap, NIK, Nomor KK,
                            nomor telepon aktif, dan email.
                        </li>

                        <li>
                            <b>Tujuan Penggunaan:</b>
                            Untuk verifikasi identitas,
                            kepentingan asuransi perjalanan,
                            dan koordinasi teknis keberangkatan.
                        </li>

                        <li>
                            <b>Keamanan Data:</b>
                            Data disimpan dalam sistem yang terlindungi
                            dan tidak dibagikan ke pihak ketiga
                            di luar kebutuhan penyelenggaraan program.
                        </li>

                        <li>
                            <b>Integrasi Sistem:</b>
                            Data dapat diverifikasi dengan sistem
                            kependudukan atau instansi terkait
                            guna mencegah penyalahgunaan.
                        </li>

                        <li>
                            <b>Penyimpanan Data:</b>
                            Data hanya disimpan selama periode program
                            dan untuk kebutuhan audit setelahnya.
                        </li>
                    </ul>

                </div>

                <div class="mt-5 text-center">
                    <a href="{{ route('public.landing') }}" class="btn btn-primary">
                        Kembali ke Halaman Utama
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection