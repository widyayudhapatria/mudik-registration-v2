<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pendaftaran Disetujui - QR Code</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container { background-color: #ffffff; border-radius: 8px; overflow: hidden; }
        .header {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            padding: 40px;
            text-align: center;
            color: white;
        }
        .header-icon { font-size: 60px; margin-bottom: 10px; }
        .header-title { margin: 0; font-size: 28px; font-weight: bold; }
        .header-subtitle { margin: 10px 0 0 0; font-size: 16px; }
        .content { padding: 40px 30px; }
        .congratulations {
            font-size: 22px;
            font-weight: bold;
            color: #10b981;
            text-align: center;
            margin-bottom: 10px;
        }
        .qr-container {
            background-color: #e8f5e9;
            border: 3px dashed #10b981;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .qr-container h2 { color: #1B5E20; margin: 0 0 20px 0; }
        .qr-code {
            max-width: 300px;
            height: auto;
            display: block;
            margin: 0 auto;
            background: white;
            padding: 15px;
            border-radius: 8px;
        }
        .qr-notice { color: #d32f2f; font-weight: bold; margin: 15px 0 5px 0; }
        .qr-subnotice { font-size: 14px; color: #6b7280; font-weight: 700; margin: 5px 0 15px 0; }
        .cta-button {
            display: inline-block;
            background: #2196F3;
            color: white !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
        }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 10px 0; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { color: #757575; width: 45%; }
        .info-table tr:last-child td { border-bottom: none; }
        .participant-list { background-color: #f9fafb; padding: 15px 20px; border-radius: 4px; margin: 15px 0; }
        .section-title { color: #f58514; margin: 20px 0 8px 0; }
        .badge-child {
            background: #fff3e0;
            color: #e65100;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }
        .warning-box {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .divider { height: 1px; background-color: #dee2e6; margin: 25px 0; }
    </style>
</head>
<body>
<div class="email-container">

    {{-- Header --}}
    <div class="header">
        <div class="header-icon">✅</div>
        <h1 class="header-title">SELAMAT!</h1>
        <p class="header-subtitle">Pendaftaran Anda Disetujui</p>
    </div>

    {{-- Content --}}
    <div class="content">
        <p>
            Assalamu'alaikum Warahmatullahi Wabarakatuh,<br><br>
            Yth. <strong>{{ $registration->representative_name }}</strong>,
        </p>

        <p class="congratulations">Selamat {{ $registration->representative_name }}! 🎉</p>

        <p style="text-align: center; font-size: 16px; color: #555;">
            Pendaftaran mudik gratis Anda telah
            <strong style="color: #10b981; font-size: 18px;">DISETUJUI</strong> oleh tim kami.<br>
            Jumlah peserta mudik: <strong>{{ $registration->participants->count() }} orang</strong>
        </p>

        {{-- QR Code --}}
        <div class="qr-container">
            <h2>QR CODE PENUKARAN TIKET</h2>
            <img src="{{ $qrCodeImage }}" alt="QR Code Tiket Mudik" class="qr-code">
            <p class="qr-notice">⚠️ Simpan QR Code ini dengan baik!</p>
            <p class="qr-subnotice">QR Code berlaku untuk 1x scan</p>
            <a href="{{ route('scan.view', ['token' => $qrCode->token_qr]) }}" class="cta-button">
                📱 Lihat QR Code di Browser
            </a>
        </div>

        {{-- Info Pendaftaran --}}
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px 0; color: #1B5E20;">📋 Informasi Pendaftaran</h3>
            <table class="info-table">
                <tr>
                    <td>Nama Perwakilan:</td>
                    <td><strong>{{ $registration->representative_name }}</strong></td>
                </tr>
                <tr>
                    <td>NIK:</td>
                    <td><strong>{{ $registration->representative_nik }}</strong></td>
                </tr>
                <tr>
                    <td>Jumlah Peserta:</td>
                    <td><strong>{{ $registration->participants->count() }} orang</strong></td>
                </tr>
            </table>
        </div>

        {{-- Daftar Peserta --}}
        <div class="participant-list">
            <h4 class="section-title">DAFTAR PESERTA MUDIK:</h4>
            <ol style="margin: 5px 0; padding-left: 20px;">
                @foreach($registration->participants as $participant)
                    <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <strong>{{ $participant->full_name }}</strong><br>
                        <span style="font-size: 13px; color: #757575;">
                            NIK/KIA: {{ $participant->nik_kia }}
                            @if($participant->is_child_under_4)
                                <span class="badge-child">👶 &lt; 4 tahun</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="divider"></div>

        {{-- Wajib Dibawa --}}
        <h4 class="section-title">WAJIB DIBAWA SAAT PENUKARAN :</h4>
        <ul>
            <li>QR Code ini (cetak atau tunjukkan di smartphone)</li>
            <li>KTP Asli perwakilan <strong>{{ strtoupper($registration->representative_name) }}</strong></li>
            <li>Kartu Keluarga (KK) Asli</li>
            <li>Dokumen pendukung lainnya jika diminta</li>
        </ul>

        {{-- Info Penukaran --}}
        <h4 class="section-title">INFORMASI PENUKARAN TIKET :</h4>
        <ul>
            <li><strong>Tanggal:</strong> 29 - 30 Mei 2026</li>
            <li><strong>Waktu:</strong> 11:00 - 17:00 WIB</li>
            <li><strong>Lokasi:</strong> Lapangan Parkir Kantor Pemerintah Provinsi Banten</li>
            <li><strong>Alamat:</strong> Sukajaya, Curug, Serang City, Banten 42171</li>
        </ul>

        {{-- Catatan Penting --}}
        <div class="warning-box">
            <h4 style="margin: 0 0 8px 0; color: #991b1b;">⚠️ CATATAN PENTING:</h4>
            <ul style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
                <li><strong>Datang Tepat Waktu:</strong> Keterlambatan dapat menyebabkan pembatalan</li>
                <li><strong>QR Code One-Time:</strong> Hanya berlaku untuk 1x scan, tidak dapat digunakan ulang</li>
                @if($registration->participants->where('is_child_under_4', true)->isNotEmpty())
                    <li><strong>Anak Dibawah 4 Tahun:</strong> Wajib dipangku selama perjalanan</li>
                @endif
                <li><strong>Tidak Dapat Dipindahtangankan:</strong> Tiket tidak dapat dialihkan ke orang lain</li>
                <li><strong>Patuhi Protokol:</strong> Ikuti semua instruksi petugas di lokasi</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <p style="font-size: 18px; color: #000; font-weight: bold;">
                Selamat Mudik! Semoga perjalanan Anda lancar dan selamat sampai tujuan 🙏
            </p>
        </div>

        <div class="divider"></div>

        {{-- Kontak --}}
        <p><strong>Butuh bantuan?</strong></p>
        <p>
            📧 Email: <a href="mailto:support@mudikgratis.com">support@mudikgratis.com</a><br>
            📞 Telepon: 021-1234567<br>
            🕐 Senin - Jumat, 08:00 - 17:00 WIB
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p style="margin: 0;"><strong>Mudik Gratis Lebaran 2026</strong></p>
        <p style="margin: 5px 0;">Program Pemerintah Provinsi Banten</p>
        <p style="margin: 10px 0; font-size: 12px; color: #999;">
            Email ini dikirim otomatis, mohon tidak membalas email ini.<br>
            Jika Anda memerlukan bantuan, silahkan hubungi kontak di atas.
        </p>
        <p style="margin: 10px 0; font-size: 12px; color: #999;">© 2026 Mudik Gratis Lebaran. All rights reserved.</p>
    </div>

</div>
</body>
</html>