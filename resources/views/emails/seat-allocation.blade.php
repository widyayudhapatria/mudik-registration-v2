<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket Mudik Gratis 2026</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
            color: white;
            padding: 25px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .section {
            margin: 25px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #2E7D32;
        }
        .section h2 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #2E7D32;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-table {
            width: 100%;
            margin: 15px 0;
        }
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            font-weight: 600;
            width: 40%;
            color: #555;
        }
        .info-table td:last-child {
            color: #333;
        }
        .seat-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }
        .seat-item {
            background-color: white;
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .seat-item.no-seat {
            background-color: #fff3e0;
            border-color: #ffb74d;
        }
        .seat-item .name {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }
        .seat-item .seat-code {
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 1px;
        }
        .seat-item.no-seat .seat-code {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
        }
        .bus-group {
            margin: 20px 0;
        }
        .bus-header {
            background: linear-gradient(135deg, #1976D2 0%, #0D47A1 100%);
            color: white;
            padding: 12px 15px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .alert {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .alert h3 {
            margin: 0 0 10px 0;
            color: #856404;
            font-size: 16px;
        }
        .alert ul {
            margin: 10px 0;
            padding-left: 20px;
            color: #856404;
        }
        .alert li {
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        .footer strong {
            color: #2E7D32;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 E-Ticket Mudik Gratis 2026</h1>
            <p>Selamat! QR Code Anda Telah Berhasil Di-scan</p>
        </div>

        <div class="section">
            <h2>👤 Informasi Perwakilan</h2>
            <table class="info-table">
                <tr>
                    <td>Nama Perwakilan</td>
                    <td>: <strong>{{ $registration->representative_name }}</strong></td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>: {{ $registration->representative_nik }}</td>
                </tr>
                <tr>
                    <td>Tujuan</td>
                    <td>: <strong>{{ $destination->name }}</strong> ({{ $destination->code }})</td>
                </tr>
                <tr>
                    <td>Jumlah Peserta</td>
                    <td>: {{ $registration->family_count }} orang</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>🚌 Alokasi Nomor Kursi</h2>

            @php
                // Group seats by bus_number
                $grouped = $seatAllocations->groupBy(function($seat) {
                    return $seat->bus_number ?? 'no-seat';
                });
            @endphp

            @foreach($grouped as $busNumber => $seats)
                @if($busNumber !== 'no-seat')
                    <div class="bus-group">
                        <div class="bus-header">
                            🚌 Bus {{ $busNumber }} - {{ $destination->name }}
                        </div>
                        <ul class="seat-list">
                            @foreach($seats as $seat)
                                <li class="seat-item">
                                    <span class="name">{{ $seat->participant->full_name }}</span>
                                    <span class="seat-code">{{ $seat->seat_code }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

            @if(isset($grouped['no-seat']) && $grouped['no-seat']->isNotEmpty())
                <div class="bus-group">
                    <div class="bus-header" style="background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);">
                        👶 Anak Dibawah 4 Tahun (Dipangku)
                    </div>
                    <ul class="seat-list">
                        @foreach($grouped['no-seat'] as $seat)
                            <li class="seat-item no-seat">
                                <span class="name">{{ $seat->participant->full_name }} ({{ $seat->participant->getAge() }} tahun)</span>
                                <span class="seat-code">DIPANGKU</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="alert">
            <h3>⚠️ Informasi Penting</h3>
            <ul>
                <li>Harap datang <strong>30 menit sebelum keberangkatan</strong></li>
                <li>Tunjukkan <strong>email ini</strong> kepada petugas saat boarding</li>
                <li>Anak dibawah 4 tahun <strong>wajib dipangku</strong> selama perjalanan</li>
                <li>Nomor kursi <strong>tidak dapat diubah</strong></li>
                <li>Pastikan membawa <strong>dokumen identitas asli</strong> (KTP/KK)</li>
            </ul>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon tidak membalas.</p>
            <p>Untuk pertanyaan lebih lanjut, hubungi panitia Mudik Gratis 2026.</p>
            <br>
            <strong>Selamat Mudik! 🙏</strong><br>
            <p style="margin-top: 5px;">Tim Mudik Gratis 2026</p>
        </div>
    </div>
</body>
</html>
