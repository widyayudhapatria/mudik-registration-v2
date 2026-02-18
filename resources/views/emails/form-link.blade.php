<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Link Pendaftaran</title>
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

        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);
            padding: 40px 20px;
            text-align: center;
        }

        .header-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }

        .header-title {
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }

        .header-subtitle {
            color: #ffffff;
            font-size: 16px;
            margin: 0;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .message {
            font-size: 15px;
            line-height: 1.7;
            color: #424242;
            margin-bottom: 25px;
        }

        .cta-container {
            text-align: center;
            margin: 30px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
        }

        .warning-box {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .warning-box-title {
            font-weight: bold;
            color: #e65100;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .warning-text {
            color: #d32f2f;
            font-weight: bold;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1B5E20;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #4CAF50;
        }

        .requirement-list {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .requirement-item {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .requirement-item:last-child {
            border-bottom: none;
        }

        .checkmark {
            color: #4CAF50;
            font-weight: bold;
            margin-right: 8px;
        }

        .contact-section {
            background-color: #e8f5e9;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
        }

        .contact-title {
            font-weight: bold;
            color: #1B5E20;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .footer {
            background-color: #f5f5f5;
            padding: 30px;
            text-align: center;
            color: #757575;
            font-size: 13px;
            line-height: 1.6;
        }

        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
    </style>
</head>

<body>
    <div class="email-container">

        {{-- Header --}}
        <div class="header">
            <div class="header-icon">🚌</div>
            <h1 class="header-title">Mudik Gratis Lebaran 2026</h1>
            <p class="header-subtitle">Program Mudik Bersama Keluarga</p>
        </div>

        {{-- Content --}}
        <div class="content">
            <p class="message">
                Assalamu'alaikum Warahmatullahi Wabarakatuh,<br>
                Yth. <strong>Calon Peserta Mudik</strong>,
            </p>

            <p class="message">
                Sebagai <strong>langkah awal</strong> untuk mengikuti Program Mudik Gratis, setiap calon peserta
                <strong>WAJIB mengisi formulir pendaftaran terlebih dahulu</strong>.
            </p>

            <p class="message">
                Untuk melanjutkan proses, silakan isi formulir pendaftaran melalui link dan tombol di bawah ini.
            </p>

            {{-- CTA --}}
            <div class="cta-container">
                <a href="{{ $formUrl }}" class="cta-button">LINK FORMULIR PENDAFTARAN</a>
            </div>

            {{-- Warning --}}
            <div class="warning-box">
                <div class="warning-box-title">⚠️ PERHATIAN PENTING</div>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Link ini akan <span class="warning-text">kadaluarsa pada {{ $expiredDate }}</span></li>
                    <li>Jika link sudah tidak aktif, silakan kunjungi kembali website resmi kami untuk mendapatkan link
                        pendaftaran terbaru</li>
                </ul>
            </div>

            <div class="divider"></div>

            {{-- Informasi Penting --}}
            <h2 class="section-title">📋 INFORMASI PENTING</h2>
            <div class="requirement-list">
                <div class="requirement-item">
                    <span class="checkmark">✓</span>
                    <strong>Kuota Terbatas:</strong> Pendaftaran dibatasi per hari, jika kuota penuh silahkan coba
                    kembali esok hari
                </div>
                <div class="requirement-item">
                    <span class="checkmark">✓</span>
                    <strong>Data Harus Valid:</strong> Pastikan semua data yang diisi benar dan sesuai dokumen
                </div>
                <div class="requirement-item">
                    <span class="checkmark">✓</span>
                    <strong>Siapkan Dokumen:</strong> Siapkan foto/scan Kartu Keluarga (KK) sebelum mengisi form
                </div>
                <div class="requirement-item">
                    <span class="checkmark">✓</span>
                    <strong>Proses Verifikasi:</strong> Setelah submit, tim kami akan melakukan verifikasi maksimal 2x24
                    jam
                </div>
            </div>

            <div class="divider"></div>

            {{-- Persyaratan --}}
            <h2 class="section-title">📝 PERSYARATAN PENDAFTARAN</h2>
            <ul style="padding-left: 20px; line-height: 2; color: #424242;">
                <li><span class="checkmark">✓</span> Memiliki Kartu Keluarga (KK) yang masih berlaku</li>
                <li><span class="checkmark">✓</span> Setiap anggota keluarga memiliki KTP atau KIA</li>
                <li><span class="checkmark">✓</span> Anak dibawah 4 tahun wajib dipangku selama perjalanan</li>
                <li><span class="checkmark">✓</span> Satu keluarga hanya dapat mendaftar 1 kali</li>
                <li><span class="checkmark">✓</span> Bersedia mengikuti seluruh ketentuan yang berlaku</li>
            </ul>

            <div class="divider"></div>

            {{-- Kontak --}}
            <div class="contact-section">
                <div class="contact-title">💬 BUTUH BANTUAN?</div>
                <div style="color: #424242; line-height: 1.8;">
                    <strong>📧 Email:</strong> support@mudikgratis.com<br>
                    <strong>📞 Telepon:</strong> 021-1234567<br>
                    <strong>🕐 Jam Operasional:</strong> Senin - Jumat, 08:00 - 17:00 WIB
                </div>
            </div>

            <p class="message" style="text-align: center; color: #757575;">
                Terima kasih atas perhatian Anda.<br>
                Kami tunggu pendaftaran Anda! 🙏
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
            <p style="margin: 10px 0; font-size: 12px; color: #999;">© 2026 Mudik Gratis Lebaran. All rights reserved.
            </p>
        </div>

    </div>
</body>

</html>
