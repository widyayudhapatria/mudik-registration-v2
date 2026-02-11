<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pemberitahuan Pendaftaran Mudik Gratis</title>
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
            background: linear-gradient(135deg, #757575 0%, #424242 100%);
            padding: 40px 20px;
            text-align: center;
        }
        .header-icon { font-size: 50px; margin-bottom: 10px; }
        .header-title { color: #ffffff; font-size: 24px; font-weight: bold; margin: 0 0 10px 0; }
        .header-subtitle { color: #ffffff; font-size: 16px; margin: 0; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .message { font-size: 15px; line-height: 1.7; color: #424242; margin-bottom: 25px; }
        .status-box {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
            padding: 25px;
            margin: 30px 0;
            border-radius: 4px;
            text-align: center;
        }
        .status-title { font-size: 20px; font-weight: bold; color: #c62828; margin-bottom: 10px; }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #d32f2f;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f44336;
        }
        .reason-box {
            background-color: #fff3e0;
            border: 2px solid #ff9800;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .reason-title { font-weight: bold; color: #e65100; margin-bottom: 10px; font-size: 16px; }
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-title { font-weight: bold; color: #1565c0; margin-bottom: 10px; font-size: 16px; }
        .steps-list { margin: 15px 0; padding-left: 0; list-style: none; }
        .steps-list li {
            padding: 12px 15px;
            margin-bottom: 10px;
            background-color: #ffffff;
            border-left: 3px solid #2196F3;
            border-radius: 4px;
        }
        .step-number {
            display: inline-block;
            background-color: #2196F3;
            color: #ffffff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-weight: bold;
            font-size: 13px;
            margin-right: 10px;
        }
        .requirements-box { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .requirement-item { padding: 10px 0; border-bottom: 1px solid #e0e0e0; }
        .requirement-item:last-child { border-bottom: none; }
        .checkmark { color: #4CAF50; font-weight: bold; margin-right: 8px; }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
        }
        .contact-section { background-color: #e8f5e9; padding: 25px; border-radius: 8px; margin: 30px 0; }
        .contact-title { font-weight: bold; color: #1B5E20; margin-bottom: 15px; font-size: 16px; }
        .footer {
            background-color: #f5f5f5;
            padding: 30px;
            text-align: center;
            color: #757575;
            font-size: 13px;
            line-height: 1.6;
        }
        .divider { height: 1px; background-color: #e0e0e0; margin: 30px 0; }
    </style>
</head>
<body>
<div class="email-container">

    {{-- Header --}}
    <div class="header">
        <div class="header-icon">📋</div>
        <h1 class="header-title">Pemberitahuan Pendaftaran</h1>
        <p class="header-subtitle">Mudik Gratis Lebaran 2026</p>
    </div>

    {{-- Content --}}
    <div class="content">
        <p class="message">
            Kepada Yth.<br>
            <strong>{{ $registration->representative_name }}</strong>
        </p>

        <p class="message">
            Terima kasih telah mendaftar program <strong>Mudik Gratis Lebaran 2026</strong>.
            Kami menghargai minat dan kepercayaan Anda terhadap program ini.
        </p>

        {{-- Status --}}
        <div class="status-box">
            <div class="status-title">❌ PENDAFTARAN DITOLAK</div>
            <p style="color: #424242; margin: 0;">
                Mohon maaf, setelah melakukan verifikasi data,<br>
                pendaftaran Anda <strong>tidak dapat kami setujui</strong>.
            </p>
        </div>

        <div class="divider"></div>

        {{-- Alasan Penolakan --}}
        <h2 class="section-title">📝 ALASAN PENOLAKAN</h2>
        <div class="reason-box">
            <div class="reason-title">Berikut alasan pendaftaran Anda ditolak:</div>
            <p style="margin: 0; color: #424242; line-height: 1.7;">{{ $rejectionReason }}</p>
        </div>

        <p class="message">
            Kami mohon maaf atas ketidaknyamanan ini. Keputusan ini diambil setelah
            tim kami melakukan verifikasi menyeluruh terhadap data dan dokumen yang Anda kirimkan.
        </p>

        <div class="divider"></div>

        {{-- Info Pendaftaran Ulang --}}
        <div class="info-box">
            <div class="info-title">💡 INFORMASI PENDAFTARAN ULANG</div>
            <p style="margin: 0; color: #424242;">
                Jika Anda merasa data yang Anda berikan sudah benar dan memenuhi persyaratan,
                Anda dapat <strong>mendaftar kembali</strong> dengan melakukan perbaikan data
                sesuai alasan penolakan di atas.
            </p>
        </div>

        {{-- Cara Daftar Ulang --}}
        <h2 class="section-title">🔄 CARA MENDAFTAR ULANG</h2>
        <ul class="steps-list">
            <li>
                <span class="step-number">1</span>
                <strong>Periksa & Perbaiki Data:</strong> Pastikan data dan dokumen sudah sesuai persyaratan
            </li>
            <li>
                <span class="step-number">2</span>
                <strong>Submit Ulang Email:</strong> Kunjungi website pendaftaran dan submit email Anda
            </li>
            <li>
                <span class="step-number">3</span>
                <strong>Terima Link Baru:</strong> Link form baru akan dikirimkan ke email Anda
            </li>
            <li>
                <span class="step-number">4</span>
                <strong>Lengkapi Form:</strong> Isi formulir dengan data yang benar dan lengkap
            </li>
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $websiteUrl }}" class="cta-button">🔄 DAFTAR ULANG SEKARANG</a>
        </div>

        <div class="divider"></div>

        {{-- Persyaratan --}}
        <h2 class="section-title">📋 PERSYARATAN PENDAFTARAN</h2>
        <p class="message">Pastikan Anda memenuhi semua persyaratan berikut sebelum mendaftar ulang:</p>

        <div class="requirements-box">
            <div class="requirement-item">
                <span class="checkmark">✓</span>
                <strong>Kartu Keluarga (KK) yang masih berlaku dan jelas</strong><br>
                <span style="font-size: 13px; color: #757575;">Dokumen harus asli dan dapat terbaca dengan jelas</span>
            </div>
            <div class="requirement-item">
                <span class="checkmark">✓</span>
                <strong>Data NIK sesuai dengan yang tertera di KTP/KIA</strong><br>
                <span style="font-size: 13px; color: #757575;">Pastikan NIK yang diinput sama persis dengan dokumen</span>
            </div>
            <div class="requirement-item">
                <span class="checkmark">✓</span>
                <strong>Foto/scan dokumen harus jelas dan dapat dibaca</strong><br>
                <span style="font-size: 13px; color: #757575;">Format JPG, PNG, atau PDF dengan ukuran max 2MB</span>
            </div>
            <div class="requirement-item">
                <span class="checkmark">✓</span>
                <strong>Tidak ada duplikasi data dengan pendaftar lain</strong><br>
                <span style="font-size: 13px; color: #757575;">Setiap keluarga hanya boleh mendaftar 1 kali</span>
            </div>
            <div class="requirement-item">
                <span class="checkmark">✓</span>
                <strong>Semua data harus valid dan dapat dipertanggungjawabkan</strong><br>
                <span style="font-size: 13px; color: #757575;">Data palsu atau tidak valid akan langsung ditolak</span>
            </div>
        </div>

        <div style="background-color: #fff9c4; padding: 15px; border-radius: 8px; border-left: 4px solid #fbc02d; margin: 20px 0;">
            <strong style="color: #f57f17;">💡 Tips:</strong>
            <span style="color: #424242;">
                Periksa kembali semua data sebelum submit. Pastikan foto KK jelas,
                NIK benar, dan tidak ada duplikasi dengan pendaftaran sebelumnya.
            </span>
        </div>

        <p class="message">
            Kami mohon maaf atas ketidaknyamanan ini dan berharap Anda dapat
            melengkapi persyaratan dengan benar pada pendaftaran selanjutnya.
        </p>

        <div class="divider"></div>

        {{-- Kontak --}}
        <div class="contact-section">
            <div class="contact-title">💬 BUTUH BANTUAN?</div>
            <div style="color: #424242;">
                Jika Anda memiliki pertanyaan atau memerlukan klarifikasi lebih lanjut
                mengenai alasan penolakan, silakan hubungi kami:
                <div style="margin-top: 15px;">
                    <strong>Email:</strong> support@mudikgratis.com<br>
                    <strong>Telepon:</strong> 021-1234567<br>
                    <strong>Jam Operasional:</strong> Senin - Jumat, 08:00 - 17:00 WIB
                </div>
            </div>
        </div>

        <p class="message" style="text-align: center; color: #757575;">
            Terima kasih atas pengertian dan kerjasamanya.<br>
            Semoga Anda dapat melengkapi persyaratan dengan baik.
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p style="margin: 0 0 10px 0;">
            Email ini dikirim secara otomatis oleh sistem.<br>
            Mohon tidak membalas email ini.
        </p>
        <p style="margin: 10px 0 0 0;">© 2026 Mudik Gratis Lebaran. All rights reserved.</p>
    </div>

</div>
</body>
</html>