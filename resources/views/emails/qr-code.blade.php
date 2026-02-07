<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Tiket Mudik - Pendaftaran Disetujui</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', 'Helvetica', sans-serif;
            background-color: #f5f5f5;
            -webkit-font-smoothing: antialiased;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            padding: 40px 20px;
            text-align: center;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background-color: #ffffff;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }
        .email-title {
            color: #ffffff;
            font-size: 26px;
            font-weight: bold;
            margin: 0 0 10px 0;
        }
        .email-subtitle {
            color: #ffffff;
            font-size: 18px;
            margin: 0;
            opacity: 0.95;
        }
        .email-body {
            padding: 40px 30px;
        }
        .congratulation {
            font-size: 20px;
            font-weight: bold;
            color: #2E7D32;
            margin-bottom: 20px;
            text-align: center;
        }
        .message {
            font-size: 15px;
            line-height: 1.7;
            color: #424242;
            margin-bottom: 25px;
        }
        .qr-container {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
            border: 3px solid #4CAF50;
        }
        .qr-title {
            font-size: 18px;
            font-weight: bold;
            color: #1B5E20;
            margin-bottom: 20px;
        }
        .qr-code {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            display: inline-block;
            margin: 0 auto;
        }
        .qr-note {
            color: #d32f2f;
            font-weight: bold;
            margin-top: 15px;
            font-size: 14px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1B5E20;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #4CAF50;
        }
        .info-card {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-row {
            display: table;
            width: 100%;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #757575;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .info-value {
            color: #212121;
            font-weight: bold;
        }
        .participant-list {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .participant-item {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .participant-item:last-child {
            border-bottom: none;
        }
        .participant-name {
            font-weight: 600;
            color: #212121;
        }
        .child-badge {
            background-color: #fff3e0;
            color: #e65100;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .warning-box {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .warning-title {
            font-weight: bold;
            color: #e65100;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .warning-list {
            margin: 10px 0;
            padding-left: 20px;
        }
        .warning-list li {
            margin-bottom: 10px;
            color: #424242;
            line-height: 1.6;
        }
        .important-box {
            background-color: #ffebee;
            border: 2px solid #f44336;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .important-title {
            font-weight: bold;
            color: #c62828;
            margin-bottom: 15px;
            font-size: 16px;
            text-align: center;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            margin: 10px 0;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .email-footer {
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
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            .qr-container {
                padding: 20px;
            }
            .info-row {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="success-icon">✅</div>
            <h1 class="email-title">SELAMAT!</h1>
            <p class="email-subtitle">Pendaftaran Anda Disetujui</p>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            <p class="congratulation">
                Assalamu'alaikum Warahmatullahi Wabarakatuh,<br>
                Selamat {{ $representativeName }}!
            </p>
            
            <p class="message">
                Pendaftaran mudik gratis Anda telah <strong>DISETUJUI</strong> oleh tim kami.
                Anda dan keluarga sebanyak <strong>{{ $familyCount }} orang</strong> terdaftar sebagai peserta 
                Mudik Gratis Lebaran 2026.
            </p>
            
            <!-- Participant List -->
            <h2 class="section-title">👥 DAFTAR PESERTA MUDIK</h2>
            <div class="participant-list">
                @foreach($participants as $index => $participant)
                <div class="participant-item">
                    <div>
                        <div class="participant-name">{{ $index + 1 }}. {{ $participant['name'] }}</div>
                        <div style="font-size: 13px; color: #757575; margin-top: 4px;">
                            NIK/KIA: {{ $participant['nik_kia'] }}
                        </div>
                    </div>
                    @if($participant['is_child_under_4'])
                    <span class="child-badge">< 4 tahun</span>
                    @endif
                </div>
                @endforeach
            </div>
            
            <div class="divider"></div>
            
            <!-- QR Code Section -->
            <h2 class="section-title">🎫 QR CODE PENUKARAN TIKET</h2>
            
            <div class="qr-container">
                <div class="qr-title">Simpan QR Code ini dengan baik!</div>
                <div class="qr-code">
                    {!! $qrCode !!}
                </div>
                <p class="qr-note">⚠️ QR Code berlaku untuk 1x scan</p>
            </div>
            
            <div class="button-container">
                <a href="{{ $qrViewLink }}" class="cta-button" style="color: #ffffff;">
                    📱 LIHAT QR CODE DI BROWSER
                </a>
            </div>
            
            <div class="divider"></div>
            
            <!-- Exchange Info -->
            <h2 class="section-title">📍 INFORMASI PENUKARAN TIKET</h2>
            
            <div class="info-card">
                <div class="info-row">
                    <div class="info-label">📅 Tanggal</div>
                    <div class="info-value">{{ $exchangeDate }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">🕐 Waktu</div>
                    <div class="info-value">{{ $exchangeTime }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">📍 Lokasi</div>
                    <div class="info-value">{{ $exchangeLocation }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">🏢 Alamat</div>
                    <div class="info-value">{{ $exchangeAddress }}</div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Important Notice -->
            <div class="important-box">
                <div class="important-title">📋 WAJIB DIBAWA SAAT PENUKARAN</div>
                <ul style="margin: 10px 0; padding-left: 20px; text-align: left;">
                    <li><strong>QR Code ini</strong> (cetak atau tunjukkan di smartphone)</li>
                    <li><strong>KTP Asli</strong> perwakilan ({{ $representativeName }})</li>
                    <li><strong>Kartu Keluarga (KK) Asli</strong></li>
                    <li>Dokumen pendukung lainnya jika diminta</li>
                </ul>
            </div>
            
            <!-- Warning Box -->
            <div class="warning-box">
                <div class="warning-title">⚠️ CATATAN PENTING</div>
                <ul class="warning-list">
                    <li><strong>Datang Tepat Waktu:</strong> Keterlambatan dapat menyebabkan pembatalan</li>
                    <li><strong>QR Code One-Time:</strong> Hanya berlaku untuk 1x scan, tidak dapat digunakan ulang</li>
                    @if($hasChildUnder4)
                    <li><strong>Anak Dibawah 4 Tahun:</strong> Wajib dipangku selama perjalanan</li>
                    @endif
                    <li><strong>Tidak Dapat Dipindahtangankan:</strong> Tiket hanya untuk nama yang terdaftar</li>
                    <li><strong>Ikuti Semua Instruksi:</strong> Patuhi arahan petugas di lokasi</li>
                </ul>
            </div>
            
            <div class="divider"></div>
            
            <!-- Departure Info -->
            <h2 class="section-title">🚌 INFORMASI KEBERANGKATAN</h2>
            
            <div class="info-card">
                <div class="info-row">
                    <div class="info-label">📅 Tanggal Keberangkatan</div>
                    <div class="info-value">{{ $departureDate }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">🕐 Waktu Keberangkatan</div>
                    <div class="info-value">{{ $departureTime }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">📍 Lokasi Keberangkatan</div>
                    <div class="info-value">{{ $departureLocation }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">🎯 Tujuan</div>
                    <div class="info-value">{{ $destination }}</div>
                </div>
            </div>
            
            <p class="message" style="margin-top: 30px; text-align: center; font-size: 16px; font-weight: 600; color: #2E7D32;">
                Selamat Mudik! 🎉<br>
                Semoga perjalanan Anda lancar dan selamat sampai tujuan.
            </p>
            
            <div class="divider"></div>
            
            <!-- Contact Section -->
            <div style="background-color: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <div style="font-weight: bold; color: #1B5E20; margin-bottom: 15px;">💬 BUTUH BANTUAN?</div>
                <div style="color: #424242; line-height: 1.8;">
                    <strong>Email:</strong> support@mudikgratis.com<br>
                    <strong>Telepon:</strong> 021-1234567<br>
                    <strong>Jam Operasional:</strong> Senin - Jumat, 08:00 - 17:00 WIB
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 0 0 10px 0;">
                Email ini dikirim secara otomatis oleh sistem.<br>
                Mohon tidak membalas email ini.
            </p>
            <p style="margin: 10px 0 0 0;">
                © 2026 Mudik Gratis Lebaran. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>