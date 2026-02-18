@extends('layouts.cms')
@section('title', 'Scanner QR Code')
@section('page-title', 'Scanner QR Code')

@push('styles')
    <style>
        .scanner-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .scanner-header-bar {
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
            color: white;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .scanner-header-bar h5 {
            margin: 0;
            font-weight: 600;
            font-size: 15px;
        }

        #reader {
            border-radius: 0;
            overflow: hidden;
            width: 100% !important;
        }

        #reader video {
            width: 100% !important;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
        }

        .stat-card h3 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
        }

        .stat-card small {
            opacity: 0.85;
            font-size: 12px;
        }

        .stat-card.success-stat {
            background: linear-gradient(135deg, #43a047 0%, #1b5e20 100%);
        }

        .stat-card.failed-stat {
            background: linear-gradient(135deg, #e53935 0%, #b71c1c 100%);
        }

        /* Result Panel */
        #scanResultPanel {
            border-radius: 12px;
            overflow: hidden;
        }

        .result-header {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 15px;
        }

        .result-body {
            padding: 18px;
        }

        .result-valid {
            border: 2px solid #a5d6a7;
            background: white;
        }

        .result-success {
            border: 2px solid #a5d6a7;
            background: white;
        }

        .result-error {
            border: 2px solid #ef9a9a;
            background: white;
        }

        .result-info {
            border: 2px solid #90caf9;
            background: white;
        }

        .result-warning {
            border: 2px solid #ffe082;
            background: white;
        }

        .result-valid .result-header {
            background: #e8f5e9;
            color: #1b5e20;
            border-bottom: 2px solid #a5d6a7;
        }

        .result-success .result-header {
            background: #e8f5e9;
            color: #1b5e20;
            border-bottom: 2px solid #a5d6a7;
        }

        .result-error .result-header {
            background: #ffebee;
            color: #b71c1c;
            border-bottom: 2px solid #ef9a9a;
        }

        .result-info .result-header {
            background: #e3f2fd;
            color: #0d47a1;
            border-bottom: 2px solid #90caf9;
        }

        .result-warning .result-header {
            background: #fff8e1;
            color: #e65100;
            border-bottom: 2px solid #ffe082;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
            font-size: 14px;
        }

        .info-table td:first-child {
            color: #666;
            font-weight: 500;
            width: 140px;
            white-space: nowrap;
            padding-right: 10px;
        }

        .info-table td:last-child {
            color: #222;
            font-weight: 600;
        }

        .participant-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .participant-list li {
            padding: 7px 10px;
            border-radius: 6px;
            background: #f5f5f5;
            margin-bottom: 5px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .participant-list li.child {
            border-left: 3px solid #ff9800;
        }

        .btn-confirm {
            background: linear-gradient(135deg, #43a047 0%, #1b5e20 100%);
            border: none;
            color: white;
            padding: 11px 26px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: 0.2s;
        }

        .btn-confirm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(27, 94, 32, 0.3);
            color: white;
        }

        .btn-confirm:disabled {
            opacity: 0.6;
            transform: none;
        }

        .btn-cancel-scan {
            background: #f5f5f5;
            border: 1px solid #ddd;
            color: #555;
            padding: 11px 18px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            transition: 0.2s;
        }

        .btn-cancel-scan:hover {
            background: #eee;
        }

        .scanner-controls {
            padding: 14px 18px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-badge.inactive {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .status-badge.error {
            background: #ffebee;
            color: #c62828;
        }

        .status-badge.loading {
            background: #e3f2fd;
            color: #1565c0;
        }

        .loading-overlay {
            text-align: center;
            padding: 24px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px dashed #ccc;
        }

        .scan-again-btn {
            border: 2px solid #2e7d32;
            color: #2e7d32;
            background: white;
            padding: 9px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: 0.2s;
        }

        .scan-again-btn:hover {
            background: #e8f5e9;
        }

        #reader {
            width: 100% !important;
            max-width: 100%;
        }

        #reader video {
            width: 100% !important;
            height: auto !important;
        }

        @media (max-width: 768px) {
            #reader {
                /* min-height: 60vh; */
            }
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">

        {{-- Left: Scanner --}}
        <div class="col-lg-7">

            {{-- Scanner Camera Card --}}
            <div class="scanner-card">
                <div class="scanner-header-bar">
                    <i class="bi bi-qr-code-scan fs-5"></i>
                    <h5>Kamera Scanner</h5>
                    <div class="ms-auto">
                        <span id="statusBadge" class="status-badge inactive">
                            <span>●</span>
                            <span id="statusText">Tidak Aktif</span>
                        </span>
                    </div>
                </div>

                <div id="reader" style="display:none;"></div>

                <div class="scanner-controls d-flex gap-2 align-items-center flex-wrap">
                    <button id="startBtn" class="btn btn-success btn-sm" onclick="startScanner()">
                        <i class="bi bi-camera-video me-1"></i> Mulai Scan
                    </button>
                    <button id="stopBtn" class="btn btn-outline-danger btn-sm" onclick="stopScanner()"
                        style="display:none;">
                        <i class="bi bi-stop-circle me-1"></i> Stop
                    </button>
                    <small class="text-muted">Kamera belakang digunakan otomatis</small>
                </div>
            </div>

            {{-- Manual Input Card --}}
            <div class="scanner-card">
                <div class="scanner-header-bar" style="background: linear-gradient(135deg, #455a64 0%, #263238 100%);">
                    <i class="bi bi-keyboard fs-5"></i>
                    <h5>Input Token Manual</h5>
                </div>
                <div class="p-3">
                    <form id="manualForm" onsubmit="handleManualInput(event)">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-qr-code text-muted"></i></span>
                            <input type="text" class="form-control" id="tokenInput"
                                placeholder="Paste token QR (64 karakter)..." autocomplete="off" spellcheck="false">
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-search me-1"></i>Cari
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">Token berupa 64 karakter alfanumerik</small>
                    </form>
                </div>
            </div>

            {{-- Scan Result Panel --}}
            <div id="scanResultPanel" style="display:none;"></div>

        </div>

        {{-- Right: History --}}
        <div class="col-lg-5">

            {{-- History --}}
            <div class="scanner-card">
                <div class="scanner-header-bar" style="background: linear-gradient(135deg, #37474f 0%, #263238 100%);">
                    <i class="bi bi-clock-history fs-5"></i>
                    <h5>Riwayat Scan</h5>
                    <span id="historyCount" class="ms-auto badge bg-white text-dark">0</span>
                </div>
                <div id="historyList" style="max-height: 480px; overflow-y: auto; padding: 10px;">
                    <p class="text-muted text-center small my-3">Belum ada riwayat scan</p>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const CSRF = '{{ csrf_token() }}';
        let html5QrCode = null;
        let isScanning = false;
        let isProcessing = false;
        let stats = {
            total: 0,
            success: 0,
            failed: 0
        };
        let history = [];

        // ── Scanner Lifecycle ──────────────────────────────────

        async function startScanner() {
            if (isScanning) return;
            try {
                setStatus('loading', 'Mengakses kamera...');
                document.getElementById('reader').style.display = 'block';
                document.getElementById('startBtn').style.display = 'none';
                document.getElementById('stopBtn').style.display = 'inline-block';
                hideResult();

                const readerEl = document.getElementById('reader');
                const qrSize = Math.min(readerEl.offsetWidth, window.innerHeight) * 0.9;

                html5QrCode = new Html5Qrcode('reader');
                await html5QrCode.start({
                        facingMode: 'environment'
                    }, {
                        fps: 15,
                        qrbox: {
                            width: qrSize,
                            height: qrSize
                        },
                        aspectRatio: 1.0
                    },
                    onScanSuccess,
                    () => {}
                );

                isScanning = true;
                setStatus('active', 'Scanner Aktif');

            } catch (err) {
                console.error('Start scanner error:', err);
                setStatus('error', 'Error: ' + (err.message || 'Tidak bisa akses kamera'));
                document.getElementById('reader').style.display = 'none';
                document.getElementById('startBtn').style.display = 'inline-block';
                document.getElementById('stopBtn').style.display = 'none';
            }
        }

        async function stopScanner() {
            if (html5QrCode && isScanning) {
                try {
                    await html5QrCode.stop();
                    html5QrCode.clear();
                } catch (e) {}
            }
            isScanning = false;
            isProcessing = false;
            html5QrCode = null;
            document.getElementById('reader').style.display = 'none';
            document.getElementById('startBtn').style.display = 'inline-block';
            document.getElementById('stopBtn').style.display = 'none';
            setStatus('inactive', 'Tidak Aktif');
        }

        // ── QR Scan Callback ──────────────────────────────────

        async function onScanSuccess(decodedText) {
            if (isProcessing) return;
            isProcessing = true;
            if (html5QrCode && isScanning) html5QrCode.pause(true);
            setStatus('loading', 'Memproses...');

            const token = extractToken(decodedText);
            if (!token) {
                showResult('error', '❌ QR Code Tidak Valid',
                    'Format tidak dikenali. Pastikan QR berasal dari sistem Mudik Gratis 2026.');
                stats.total++;
                stats.failed++;
                updateStats();
                scheduleResume(3000);
                return;
            }

            await doValidate(token);
        }

        // ── Manual Input ──────────────────────────────────────

        function handleManualInput(e) {
            e.preventDefault();
            const token = document.getElementById('tokenInput').value.trim();
            if (!token) return;
            if (!/^[a-zA-Z0-9]{64}$/.test(token)) {
                showResult('error', '❌ Token Tidak Valid',
                    `Token harus 64 karakter alfanumerik. Anda memasukkan ${token.length} karakter.`);
                return;
            }
            isProcessing = true;
            if (html5QrCode && isScanning) html5QrCode.pause(true);
            doValidate(token);
        }

        // ── API: Validate ─────────────────────────────────────

        async function doValidate(token) {
            showLoading('Memvalidasi QR Code...');
            try {
                const res = await fetch(`/cms/scanner/api/validate?token_qr=${encodeURIComponent(token)}`, {
                    credentials: 'include',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();

                if (json.success && json.data) {
                    showValidResult(json.data, token);
                    setStatus('active', 'QR Valid – Konfirmasi?');
                } else {
                    const msg = json.message || 'QR Code tidak valid.';
                    const extra = buildErrorExtra(json);
                    console.log('Validation failed:', { msg, extra, data: json.data });
                    // Use raw=true so HTML extra info renders properly
                    showResult('error', '❌ Validasi Gagal',
                        `<p class="mb-0" style="font-size:14px;">${msg}</p>${extra}`, true);
                    // Inject failed scan to history
                    const d = json.data;
                    if (d && d.registration) {
                        console.log('Injecting to history:', d.registration);
                        injectToHistory({
                            status: 'failed',
                            representative_name: d.registration.representative_name,
                            failure_reason: msg,
                            scanned_at: new Date().toISOString()
                        });
                    } else {
                        console.log('No data or registration to inject');
                    }
                    stats.total++;
                    stats.failed++;
                    updateStats();
                    scheduleResume(4000);
                }
            } catch (err) {
                console.error('Validate error:', err);
                showResult('error', '❌ Koneksi Error', 'Tidak bisa terhubung ke server. Coba lagi.');
                scheduleResume(3000);
            }
        }

        // ── API: Consume ──────────────────────────────────────

        async function confirmScan(token) {
            showLoading('Mengkonfirmasi scan...');
            try {
                const res = await fetch('/cms/scanner/api/consume', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token_qr: token
                    })
                });
                const json = await res.json();

                if (json.success && json.data) {
                    const d = json.data;
                    const reg = d.registration;
                    showResult('success', '✅ Scan Berhasil!', buildSuccessHTML(d, reg), true);
                    // Inject successful scan to history
                    injectToHistory({
                        status: 'success',
                        representative_name: reg.representative_name,
                        family_count: reg.family_count,
                        scanned_at: d.scanned_at
                    });
                    stats.total++;
                    stats.success++;
                    updateStats();
                    document.getElementById('tokenInput').value = '';
                    scheduleResume(6000);
                } else {
                    const msg = json.message || 'Scan gagal.';
                    showResult('error', '❌ Scan Gagal', msg);
                    stats.total++;
                    stats.failed++;
                    updateStats();
                    scheduleResume(4000);
                }
            } catch (err) {
                console.error('Consume error:', err);
                showResult('error', '❌ Koneksi Error', 'Tidak bisa terhubung ke server.');
                scheduleResume(3000);
            }
        }

        function cancelScan() {
            isProcessing = false;
            hideResult();
            setStatus(isScanning ? 'active' : 'inactive', isScanning ? 'Scanner Aktif' : 'Tidak Aktif');
            if (html5QrCode && isScanning) html5QrCode.resume();
        }

        // ── Calculate Age ──────────────────────────────────────

        function calculateAge(birthDate) {
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            return age;
        }

        // ── UI Rendering ──────────────────────────────────────

        function showValidResult(data, token) {
            const reg = data.registration;
            const pax = data.participants || [];
            const pHTML = pax.map(p => `
                <li class="${p.is_child_under_4 ? 'child' : ''}">
                    <i class="bi bi-person-fill text-secondary"></i>
                    <span>${p.full_name}, ${p.birth_date} (${p.age} tahun)</span>
                    ${p.is_child_under_4 ? '<span class="badge bg-warning text-dark ms-auto small">Anak &lt;4th</span>' : ''}
                </li>`).join('');

            const panel = document.getElementById('scanResultPanel');
            panel.className = 'result-valid';
            panel.innerHTML = `
                <div class="result-header">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    QR Code Valid — Konfirmasi Scan
                </div>
                <div class="result-body">
                    <table class="info-table w-100 mb-3">
                        <tr><td>Nama Perwakilan</td><td>${reg.representative_name}</td></tr>
                        <tr><td>Tujuan</td><td><strong>${reg.destination_name ?? '-'}</strong></td></tr>
                        <tr><td>No. KK</td><td>${reg.kk_number}</td></tr>
                        <tr><td>Email</td><td>${data.email ?? '-'}</td></tr>
                        <tr><td>Berlaku</td><td>${formatDate(data.qr_code.valid_from)} – ${formatDate(data.qr_code.valid_until)}</td></tr>
                    </table>

                    ${pax.length ? `<div class="mb-3">
                                                                <p class="small text-muted fw-bold mb-1">DAFTAR PESERTA MUDIK (${data.participants_summary.total} orang)</p>
                                                                <ul class="participant-list">${pHTML}</ul>
                                                            </div>` : ''}

                    ${reg.has_child_under_4 ? `<div class="alert alert-warning py-2 px-3 small mb-3">
                                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                                <strong>Perhatian:</strong> Anak dibawah 4 tahun wajib dipangku selama perjalanan!
                                                            </div>` : ''}

                    <div class="d-flex gap-2 flex-wrap mt-2">
                        <button class="btn btn-confirm" onclick="confirmScan('${token}')">
                            <i class="bi bi-check2-circle me-1"></i> Konfirmasi Scan
                        </button>
                        <button class="btn btn-cancel-scan" onclick="cancelScan()">
                            <i class="bi bi-x me-1"></i> Batal
                        </button>
                    </div>
                </div>`;
            panel.style.display = 'block';
            panel.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        function buildSuccessHTML(d, reg) {
            const pax = d.participants || [];
            const pHTML = pax.map(p => `
        <li class="${p.is_child_under_4 ? 'child' : ''}">
            <i class="bi bi-person-check-fill text-success"></i>
            ${p.full_name}, ${p.birth_date} (${p.age} tahun)
            ${p.is_child_under_4 ? '<span class="badge bg-warning text-dark ms-auto small">Anak &lt;4th</span>' : ''}
        </li>`).join('');

            return `
        <table class="info-table w-100 mb-3">
                <tr><td>Nama Perwakilan</td><td>${reg.representative_name}</td></tr>
                <tr><td>Tujuan</td><td><strong>${reg.destination_name ?? '-'}</strong></td></tr>
                <tr><td>Jumlah Tiket</td><td><strong>${d.participants_summary.total} tiket</strong></td></tr>
            <tr><td>Waktu Scan</td><td>${formatDatetime(d.scanned_at)}</td></tr>
            <tr><td>Petugas</td><td>${d.scanned_by?.name ?? '-'}</td></tr>
        </table>
        ${pax.length ? `<div class="mb-3">
                                                    <p class="small text-muted fw-bold mb-1">DAFTAR PESERTA MUDIK (${d.participants_summary.total} orang)</p>
                                                    <ul class="participant-list">${pHTML}</ul>
                                                </div>` : ''}
        ${d.warnings?.length ? `<div class="alert alert-warning py-2 px-3 small mb-2">
                                                    <i class="bi bi-exclamation-triangle me-1"></i> ${d.warnings[0]}
                                                </div>` : ''}
        <div class="alert alert-success py-2 px-3 small mb-3">
            <i class="bi bi-ticket-perforated me-1"></i> Tiket dapat ditukarkan kepada peserta!
        </div>
        <button class="scan-again-btn btn" onclick="cancelScan()">
            <i class="bi bi-arrow-repeat me-1"></i> Scan Lagi
        </button>`;
        }

        function buildErrorExtra(json) {
            if (!json.data) return '';
            const d = json.data;
            const reg = d.registration;
            const pax = d.participants || [];

            if (!reg) return '';

            // Build participants list for error display
            const pHTML = pax.length ? pax.map(p => `
                <li class="${p.is_child_under_4 ? 'child' : ''}">
                    <i class="bi bi-person-fill text-secondary"></i>
                    <span>${p.full_name} (${p.age !== undefined ? p.age : calculateAge(p.birth_date)} tahun)</span>
                    ${p.is_child_under_4 ? '<span class="badge bg-warning text-dark ms-auto small">Anak &lt;4th</span>' : ''}
                </li>`).join('') : '';

            let extra = '';
            extra += `<div style="margin-top: 16px; padding-top: 16px; border-top: 2px solid #ccc; background: #f9f9f9; padding: 12px; border-radius: 6px;">`;
            extra += `<p style="font-weight: 700; margin: 0 0 12px 0; font-size: 14px; color: #333;">📋 Info Pendaftar:</p>`;
            extra += `<table class="info-table w-100" style="font-size: 13px; margin-bottom: 12px; background: white; padding: 8px; border-radius: 4px;">`;
            extra += `<tr><td style="padding: 4px 0;">Nama</td><td style="padding: 4px 0;"><strong>${reg.representative_name}</strong></td></tr>`;
            extra += `<tr><td style="padding: 4px 0;">Tujuan</td><td style="padding: 4px 0;"><strong>${reg.destination_name || '-'}</strong></td></tr>`;
            extra += `<tr><td style="padding: 4px 0;">No. KK</td><td style="padding: 4px 0;">${reg.kk_number}</td></tr>`;
            extra += `</table>`;

            if (pax.length) {
                extra += `<p style="font-weight: 700; margin: 12px 0 8px 0; font-size: 14px; color: #333;">👥 Peserta (${d.participants_summary?.total || pax.length} orang):</p>`;
                extra += `<ul class="participant-list" style="margin: 0; background: white; padding: 8px; border-radius: 4px;">${pHTML}</ul>`;
            }

            if (d.scanned_at) {
                extra += `<p style="font-weight: 700; margin: 12px 0 4px 0; font-size: 14px; color: #333;">📅 Info Waktu Scan:</p>`;
                extra += `<small class="text-muted" style="display: block; margin-bottom: 4px;">Waktu: ${formatDatetime(d.scanned_at)}</small>`;
                if (d.scanned_by) {
                    extra += `<small class="text-muted">Petugas: <strong>${d.scanned_by}</strong></small>`;
                }
            }
            if (d.valid_from) {
                extra += `<p style="font-weight: 700; margin: 12px 0 4px 0; font-size: 14px; color: #333;">📆 Periode Berlaku:</p>`;
                extra += `<small class="text-muted">${formatDate(d.valid_from)} – ${formatDate(d.valid_until)}</small>`;
            }
            extra += `</div>`;
            return extra;
        }

        function showResult(type, title, html, raw = false) {
            const icons = {
                success: 'bi-check-circle-fill text-success',
                error: 'bi-x-circle-fill text-danger',
                info: 'bi-info-circle-fill text-primary',
                warning: 'bi-exclamation-triangle-fill text-warning'
            };
            const panel = document.getElementById('scanResultPanel');
            panel.className = `result-${type}`;
            panel.innerHTML = `
        <div class="result-header">
            <i class="bi ${icons[type] ?? 'bi-circle'} fs-5"></i> ${title}
        </div>
        <div class="result-body">
            ${raw ? html : `<p class="mb-0" style="font-size:14px;">${html}</p>`}
        </div>`;
            panel.style.display = 'block';
            panel.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        function showLoading(msg = 'Memproses...') {
            const panel = document.getElementById('scanResultPanel');
            panel.className = '';
            panel.style.display = 'block';
            panel.innerHTML = `
        <div class="loading-overlay">
            <div class="spinner-border text-primary mb-2" style="width:2rem;height:2rem;"></div>
            <p class="text-muted mb-0 small">${msg}</p>
        </div>`;
        }

        function hideResult() {
            const panel = document.getElementById('scanResultPanel');
            panel.style.display = 'none';
            panel.innerHTML = '';
        }

        // ── History ───────────────────────────────────────────

        async function loadScanHistory() {
            try {
                const res = await fetch('/cms/scanner/api/history', {
                    credentials: 'include',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();
                if (json.success && json.data) {
                    renderRemoteHistory(json.data);
                }
            } catch (err) {
                console.error('Load history error:', err);
            }
        }

        function injectToHistory(item) {
            console.log('injectToHistory called with:', item);
            const el = document.getElementById('historyList');
            if (!el) {
                console.error('historyList element not found!');
                return;
            }
            // Remove empty state message if exists
            const emptyMsg = el.querySelector('.text-muted.text-center');
            if (emptyMsg) {
                console.log('Removing empty message');
                emptyMsg.remove();
            }

            const h = item;
            const historyItem = document.createElement('div');
            historyItem.className = 'd-flex align-items-start gap-2 mb-2 p-2 rounded';
            historyItem.style.cssText =
                `background:${h.status === 'success' ? '#f1f8e9' : '#fce4ec'}; border-left:3px solid ${h.status === 'success' ? '#66bb6a' : '#ef5350'};`;
            historyItem.innerHTML = `
                <i class="bi bi-${h.status === 'success' ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'} mt-1" style="font-size:13px;flex-shrink:0;"></i>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-semibold text-truncate" style="font-size:13px;">${h.representative_name}</div>
                    <div class="text-muted" style="font-size:11px;">${h.status === 'success' ? h.family_count + ' tiket' : h.failure_reason}</div>
                </div>
                <small class="text-muted text-nowrap" style="font-size:11px;">${new Date(h.scanned_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'})}</small>
            `;
            // Insert at beginning (newest first)
            el.insertBefore(historyItem, el.firstChild);
            console.log('Item injected to history');

            // Update count
            const countEl = document.getElementById('historyCount');
            if (countEl) {
                const currentCount = parseInt(countEl.textContent) || 0;
                countEl.textContent = currentCount + 1;
                console.log('History count updated to:', currentCount + 1);
            }
        }

        function renderRemoteHistory(logs) {
            const el = document.getElementById('historyList');
            if (!logs || logs.length === 0) {
                el.innerHTML = '<p class="text-muted text-center small my-3">Belum ada riwayat scan</p>';
                document.getElementById('historyCount').textContent = '0';
                return;
            }
            document.getElementById('historyCount').textContent = logs.length;
            el.innerHTML = logs.map(h => `
        <div class="d-flex align-items-start gap-2 mb-2 p-2 rounded"
             style="background:${h.status === 'success' ? '#f1f8e9' : '#fce4ec'}; border-left:3px solid ${h.status === 'success' ? '#66bb6a' : '#ef5350'};">
            <i class="bi bi-${h.status === 'success' ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'} mt-1" style="font-size:13px;flex-shrink:0;"></i>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="fw-semibold text-truncate" style="font-size:13px;">${h.representative_name}</div>
                <div class="text-muted" style="font-size:11px;">${h.status === 'success' ? h.family_count + ' tiket' : h.failure_reason}</div>
            </div>
            <small class="text-muted text-nowrap" style="font-size:11px;">${new Date(h.scanned_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit'})}</small>
        </div>`).join('');
        }

        // ── Helpers ───────────────────────────────────────────

        function extractToken(text) {
            text = text.trim();
            try {
                if (text.includes('://') || text.startsWith('/')) {
                    const base = text.startsWith('/') ? window.location.origin + text : text;
                    const url = new URL(base);
                    const t = url.searchParams.get('t') || url.pathname.split('/').pop();
                    return t && /^[a-zA-Z0-9]{64}$/.test(t) ? t : null;
                }
            } catch (e) {}
            return /^[a-zA-Z0-9]{64}$/.test(text) ? text : null;
        }

        function setStatus(type, text) {
            const badge = document.getElementById('statusBadge');
            badge.className = `status-badge ${type}`;
            document.getElementById('statusText').textContent = text;
        }

        function updateStats() {
            // Stats updated in local memory, displayed in dashboard only
        }

        function scheduleResume(ms) {
            setTimeout(() => {
                isProcessing = false;
                if (html5QrCode && isScanning) {
                    html5QrCode.resume();
                    setStatus('active', 'Scanner Aktif');
                }
            }, ms);
        }

        function formatDate(iso) {
            if (!iso) return '-';
            return new Date(iso).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function formatDatetime(iso) {
            if (!iso) return '-';
            return new Date(iso).toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        window.addEventListener('beforeunload', () => {
            if (html5QrCode && isScanning) html5QrCode.stop().catch(() => {});
        });

        // Load scan history when page loads (only once, no auto-refresh)
        document.addEventListener('DOMContentLoaded', () => {
            loadScanHistory();
        });
    </script>
@endpush
