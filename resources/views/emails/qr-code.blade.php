<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Tiket Mudik</title>
</head>
<body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; background: #f5f5f5;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden;">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); padding: 40px; text-align: center; color: white;">
            <div style="font-size: 60px; margin-bottom: 10px;">✅</div>
            <h1 style="margin: 0; font-size: 28px;">SELAMAT!</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px;">Pendaftaran Anda Disetujui</p>
        </div>
        
        <!-- Body -->
        <div style="padding: 40px 30px;">
            <p style="font-size: 16px; line-height: 1.6; color: #333;">
                <strong>Assalamu'alaikum,</strong><br><br>
                Yth. <strong>{{ $registration->representative_name }}</strong>,<br><br>
                Pendaftaran mudik gratis Anda telah <strong>DISETUJUI</strong>. 
                Anda dan keluarga sebanyak <strong>{{ $registration->participants->count() }} orang</strong> 
                terdaftar sebagai peserta Mudik Gratis Lebaran 2026.
            </p>
            
            <!-- QR Code -->
            <div style="background: #e8f5e9; padding: 30px; border-radius: 12px; text-align: center; margin: 30px 0;">
                <h2 style="color: #1B5E20; margin: 0 0 20px 0;">QR Code Tiket Anda</h2>
                <div style="background: white; padding: 20px; border-radius: 8px; display: inline-block;">
                    <img src="{{ $qrCodeImage }}" alt="QR Code" style="max-width: 300px; height: auto; display: block;">
                </div>
                <p style="color: #d32f2f; font-weight: bold; margin: 15px 0 0 0;">
                    ⚠️ QR Code hanya dapat digunakan 1x
                </p>
            </div>
            
            <!-- Link Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('scan.view', ['token' => $qrCode->token_qr]) }}" 
                   style="display: inline-block; background: #2196F3; color: white; text-decoration: none; padding: 15px 40px; border-radius: 8px; font-weight: bold;">
                    📱 Lihat QR Code di Browser
                </a>
            </div>
            
            <!-- Info -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="margin: 0 0 15px 0; color: #1B5E20;">📋 Informasi Pendaftaran</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 10px 0; color: #757575;">Nama:</td>
                        <td style="padding: 10px 0; font-weight: bold;">{{ $registration->representative_name }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 10px 0; color: #757575;">NIK:</td>
                        <td style="padding: 10px 0; font-weight: bold;">{{ $registration->representative_nik }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #757575;">Jumlah Peserta:</td>
                        <td style="padding: 10px 0; font-weight: bold;">{{ $registration->participants->count() }} orang</td>
                    </tr>
                </table>
            </div>
            
            <!-- Participants -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="margin: 0 0 15px 0; color: #1B5E20;">👥 Daftar Peserta</h3>
                @foreach($registration->participants as $index => $participant)
                <div style="padding: 12px 0; border-bottom: 1px solid #ddd;">
                    <strong>{{ $index + 1 }}. {{ $participant->full_name }}</strong><br>
                    <span style="font-size: 13px; color: #757575;">
                        NIK/KIA: {{ $participant->nik_kia }}
                        @if($participant->is_child_under_4)
                            <span style="background: #fff3e0; color: #e65100; padding: 2px 8px; border-radius: 10px; margin-left: 5px;">
                                < 4 tahun
                            </span>
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
            
            <!-- Warning -->
            <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 20px; margin: 25px 0; border-radius: 4px;">
                <strong style="color: #e65100;">⚠️ CATATAN PENTING</strong>
                <ul style="margin: 10px 0; padding-left: 20px; line-height: 1.8;">
                    <li>QR Code hanya berlaku 1x scan</li>
                    <li>Bawa KTP Asli dan Kartu Keluarga Asli</li>
                    <li>Datang tepat waktu</li>
                    @if($registration->has_child_under_4)
                    <li>Anak dibawah 4 tahun wajib dipangku</li>
                    @endif
                </ul>
            </div>
            
            <p style="text-align: center; font-size: 16px; font-weight: 600; color: #2E7D32; margin: 30px 0;">
                Selamat Mudik! 🎉
            </p>
        </div>
        
        <!-- Footer -->
        <div style="background: #f5f5f5; padding: 20px; text-align: center; color: #757575; font-size: 13px;">
            <p style="margin: 0;">Email ini dikirim otomatis. Mohon tidak membalas.</p>
            <p style="margin: 10px 0 0 0;">© 2026 Mudik Gratis Lebaran</p>
        </div>
    </div>
</body>
</html>