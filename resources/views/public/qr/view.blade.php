<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Status Pendaftaran Mudik Gratis 2026" />
    <title>Status Pendaftaran | Mudik Gratis 2026</title>

    <link href="https://fonts.googleapis.com/css?family=Quattrocento+Sans:400,700|Roboto:400,500,700" rel="stylesheet" />
    <link href="{{ asset('assets/public/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/public/css/materialdesignicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/public/css/mobiriseicons.css') }}" rel="stylesheet" />

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "Roboto", Arial, Helvetica, sans-serif;
            background-color: #f5f5f5;
            overflow-x: hidden;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* Approved = kuning, Scanned = hijau */
        .page-wrapper.status-approved { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
        .page-wrapper.status-scanned  { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            padding: 30px;
            max-width: 650px;
            width: 100%;
        }

        /* ── Status Header ── */
        .status-header { text-align: center; margin-bottom: 25px; }

        .status-badge {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .status-badge.approved { background-color: #fcd34d; color: #78350f; }
        .status-badge.scanned  { background-color: #86efac; color: #15803d; }

        .status-title    { font-size: 26px; font-weight: 700; color: #1f2937; margin-bottom: 5px; }
        .status-subtitle { font-size: 15px; color: #6b7280; }

        .divider { height: 1px; background-color: #e5e7eb; margin: 20px 0; }

        /* ── Section Titles ── */
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f59e0b;
            display: inline-block;
        }

        /* Scanned state = green accent */
        .status-scanned .section-title { border-bottom-color: #22c55e; }

        /* ── Destination Banner ── */
        .destination-banner {
            border-radius: 6px;
            padding: 18px;
            margin-bottom: 20px;
            color: white;
        }

        /* Approved = orange gradient, Scanned = green gradient (sesuai template) */
        .status-approved .destination-banner { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); }
        .status-scanned  .destination-banner { background: linear-gradient(135deg, #1f7a3e 0%, #145a32 100%); }

        .destination-banner h3 { font-size: 16px; font-weight: 700; margin-bottom: 12px; }

        .destination-grid        { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px; }
        .destination-grid:last-child { margin-bottom: 0; }
        .destination-grid.single { grid-template-columns: 1fr; }

        .destination-item label { font-size: 11px; text-transform: uppercase; opacity: .9; display: block; margin-bottom: 3px; }
        .destination-item p     { font-size: 20px; font-weight: 600; margin: 0; }

        /* ── Info Card ── */
        .info-card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin-bottom: 15px; }

        .info-row {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 12px;
            margin-bottom: 12px;
            align-items: start;
        }
        .info-row:last-child { margin-bottom: 0; }

        .info-label { font-size: 13px; font-weight: 600; text-transform: uppercase; }
        .status-approved .info-label { color: #f59e0b; }
        .status-scanned  .info-label { color: #1f7a3e; }

        .info-value { color: #374151; font-size: 15px; }

        /* ── Passenger List ── */
        .passenger-list { margin-top: 12px; }

        .passenger-item {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .passenger-item:last-child { margin-bottom: 0; }

        .passenger-name { font-weight: 600; color: #1f2937; font-size: 15px; margin-bottom: 2px; }
        .passenger-meta { font-size: 12px; color: #9ca3af; }

        .seat-badge {
            padding: 6px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
            text-align: right;
            min-width: 110px;
            white-space: nowrap;
        }
        .seat-badge.reserved { background-color: #fef3c7; color: #92400e; }
        .seat-badge.scanned  { background-color: #bbf7d0; color: #15803d; }
        .seat-badge.lap      { background-color: #e0e7ff; color: #3730a3; }

        /* ── Action Box ── */
        .action-box {
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            border-left-width: 4px;
            border-left-style: solid;
        }

        .status-approved .action-box { background-color: #eff6ff; border-left-color: #f59e0b; }
        .status-scanned  .action-box { background-color: #f0fdf4; border-left-color: #22c55e; }

        .action-box h5           { color: #1f2937; font-weight: 700; margin-bottom: 8px; font-size: 15px; }
        .action-box p            { color: #374151; margin-bottom: 8px; font-size: 14px; }
        .action-box p:last-child { margin-bottom: 0; }
        .action-box ul           { margin: 0; padding-left: 18px; }
        .action-box li           { color: #374151; margin-bottom: 6px; font-size: 14px; }
        .action-box .note        { font-size: 13px; margin-top: 10px; margin-bottom: 0; }

        .scan-timestamp { font-size: 13px; color: #6b7280; margin-top: 8px; margin-bottom: 0; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .card             { padding: 20px; }
            .status-title     { font-size: 22px; }
            .info-row         { grid-template-columns: 90px 1fr; }
            .destination-grid { grid-template-columns: 1fr; }
            .passenger-item   { flex-direction: column; align-items: flex-start; }
            .seat-badge       { width: 100%; text-align: left; margin-top: 8px; }
        }

        @media (max-width: 480px) {
            .card         { padding: 15px; }
            .status-title { font-size: 20px; }
        }
    </style>
</head>
<body>

@php
    $isScanned   = $qrCode->isScanned(); // pakai method, bukan property (tidak ada accessor is_scanned)
    $reg         = $registration;
    $destination = $reg->destination;
    $formLink    = $reg->formLink;
    $statusClass = $isScanned ? 'status-scanned' : 'status-approved';

    $departureDate = \Carbon\Carbon::parse(config('mudik.schedule.departure_date'));
    $departureTime = \Carbon\Carbon::parse(config('mudik.schedule.departure_time'));
@endphp

<div class="page-wrapper {{ $statusClass }}">
    <div class="card">

        {{-- ── Status Header ── --}}
        <div class="status-header">
            @if ($isScanned)
                <span class="status-badge scanned">
                    <i class="mdi mdi-ticket-confirmation me-1"></i> FINAL (SIAP BERANGKAT)
                </span>
            @else
                <span class="status-badge approved">
                    <i class="mdi mdi-check-circle me-1"></i> PENDAFTARAN APPROVED
                </span>
            @endif

            <h1 class="status-title">Status Pendaftaran Anda</h1>
            <p class="status-subtitle">Keluarga {{ $reg->representative_name }}</p>
        </div>

        <div class="divider"></div>

        {{-- ── Destination Banner ── --}}
        <div class="destination-banner">
            <h3><i class="mdi mdi-map-marker me-1"></i> Tujuan Perjalanan Mudik</h3>

            <div class="destination-grid single">
                <div class="destination-item">
                    <label>Kota / Kabupaten</label>
                    <p>{{ $destination->name }}</p>
                </div>
            </div>

            <div class="destination-grid">
                <div class="destination-item">
                    <label>Tanggal Keberangkatan</label>
                    <p>{{ $departureDate->translatedFormat('d F Y') }}</p>
                </div>
                <div class="destination-item">
                    <label>Jam {{ $isScanned ? 'Berangkat' : 'Keberangkatan' }}</label>
                    <p>{{ $departureTime->format('H:i') }} WIB</p>
                </div>
            </div>
        </div>

        {{-- ── Representative Data ── --}}
        <div>
            <h6 class="section-title">
                <i class="mdi mdi-account-multiple me-1"></i> Data Perwakilan Keluarga
            </h6>
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">{{ $reg->representative_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">NIK</span>
                    <span class="info-value">{{ $reg->representative_nik }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nomor KK</span>
                    <span class="info-value">{{ $reg->kk_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $formLink->email }}</span>
                </div>
            </div>
        </div>

        {{-- ── Participant List ── --}}
        <div style="margin-top: 25px">
            <h6 class="section-title">
                <i class="mdi mdi-account-group me-1"></i>
                {{ $isScanned ? 'Daftar Peserta & Nomor Kursi' : 'Daftar Peserta Mudik' }}
            </h6>
            <div class="passenger-list">
                @foreach ($participants as $index => $participant)
                    @php
                        $isLap = $participant->is_child_under_4;
                        $seat  = $seatAllocations->get($participant->id);

                        if ($isScanned) {
                            $badgeClass = $isLap ? 'lap' : 'scanned';
                            $badgeLabel = $isLap
                                ? 'Dipangku'
                                : ($seat ? $seat->seat_code : '-');
                        } else {
                            $badgeClass = $isLap ? 'lap' : 'reserved';
                            $badgeLabel = $isLap ? 'Dipangku' : 'Reserved';
                        }
                    @endphp
                    <div class="passenger-item">
                        <div>
                            <div class="passenger-name">{{ $index + 1 }}. {{ $participant->full_name }}</div>
                            <div class="passenger-meta">
                                @if ($isLap)
                                    Anak (Dibawah 4 Tahun)
                                @else
                                    ({{ $participant->getAge() }} tahun)
                                @endif
                            </div>
                        </div>
                        <div class="seat-badge {{ $badgeClass }}">
                            <i class="mdi mdi-{{ $isScanned && !$isLap ? 'ticket-account' : 'ticket-confirmation' }} me-1"></i>
                            {{ $badgeLabel }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Action Box ── --}}
        <div class="action-box">
            @if ($isScanned)
                <h5><i class="mdi mdi-check-circle me-1"></i> Status: SIAP BERANGKAT!</h5>
                <p>QR Code Anda telah di-scan dengan sukses ✓</p>
                <ul>
                    <li>Lokasi: <strong>{{ config('mudik.departure_location') }}</strong></li>
                    <li>Berangkat: <strong>Setelah proses penukaran selesai</strong></li>
                </ul>
                @if ($qrCode->scanned_at)
                    <p class="scan-timestamp">
                        <i class="mdi mdi-clock-outline me-1"></i>
                        Di-scan pada: {{ \Carbon\Carbon::parse($qrCode->scanned_at)->translatedFormat('d F Y, H:i') }} WIB
                    </p>
                @endif
            @else
                <h5><i class="mdi mdi-information-outline me-1"></i> Langkah Selanjutnya</h5>
                <p>Pendaftaran Anda telah disetujui ✓</p>
                <ul>
                    <li>
                        Tukar QR Code ini dengan tiket pada
                        <strong>
                            {{ $departureDate->translatedFormat('d F Y') }}
                        </strong>
                    </li>
                    <li>Berkumpul pukul <strong>{{ $departureTime->format('H:i') }} WIB</strong></li>
                    <li>Berangkat pukul : <strong>Setelah proses penukaran selesai</strong></li>
                    <li>Bawa KTP Asli dan Fotocopy KTP (semua peserta mudik)</li>
                    <li>Bawa Fotocopy KK yang diupload saat pendaftaran</li>
                </ul>
                <p class="note">
                    <i class="mdi mdi-alert-circle me-1"></i>
                    Wajib membawa <strong>KTP Asli, Fotocopy KTP, dan Fotocopy KK</strong> saat penukaran tiket.
                </p>
            @endif
        </div>

        {{-- ── QR Code Image (approved only) ── --}}
        @if (!$isScanned)
            <div style="margin-top: 25px; text-align: center;">
                <h6 style="font-size: 16px; font-weight: 700; color: #1f2937; margin-bottom: 15px;
                        padding-bottom: 8px; border-bottom: 2px solid #f59e0b; display: block;">
                    <i class="mdi mdi-qrcode me-1"></i> QR Code Anda
                </h6>
                <div style="display: inline-block; padding: 16px; border: 2px dashed #f59e0b;
                            border-radius: 8px; background: #fffbeb; margin-bottom: 8px;">
                    <img src="{{ $qrBase64 }}"
                        alt="QR Code {{ $reg->representative_name }}"
                        style="width: 200px; height: 200px; display: block;">
                </div>
                <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                    Screenshot atau simpan QR ini untuk penukaran tiket
                </p>
            </div>
        @endif

        {{-- ── Navigation Buttons ── --}}
        <div style="display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap;">
            {{-- Kembali ke Beranda (selalu tampil) --}}
            <a href="{{ route('public.landing') }}"
            style="flex: 1; min-width: 140px; padding: 12px 16px; border-radius: 6px; text-align: center;
                    background: #f3f4f6; color: #374151; font-weight: 600; text-decoration: none;
                    border: 1px solid #e5e7eb; font-size: 14px;">
                <i class="mdi mdi-home me-1"></i> Kembali ke Beranda
            </a>

            {{-- Save QR (approved only) --}}
            @if (!$isScanned)
                <a href="{{ $qrBase64 }}"
                download="qr-mudik-{{ $reg->representative_name }}.png"
                id="btn-save-qr"
                style="flex: 1; min-width: 140px; padding: 12px 16px; border-radius: 6px; text-align: center;
                        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
                        color: white; font-weight: 600; text-decoration: none; font-size: 14px;">
                    <i class="mdi mdi-download me-1"></i> Simpan QR Code
                </a>
            @endif
        </div>

    </div>
</div>

<script src="{{ asset('assets/public/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/public/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>
