@extends('layouts.cms')
@section('title', 'Scanner QR Code')
@section('page-title', 'Scanner QR Code')

@push('styles')
    <style>
        .scanner-container {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .recent-scan-item {
            border-left: 4px solid #4CAF50;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .recent-scan-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .recent-scan-item.failed {
            border-left-color: #f44336;
        }

        #reader {
            border-radius: 12px;
            overflow: hidden;
            margin: 0 auto;
            max-width: 600px;
        }

        .manual-input-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <!-- Left Column: Scanner -->
        <div class="col-lg-8">
            <div class="scanner-container">
                <!-- Scanner Status -->
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Status Scanner:</strong>
                    <span id="scannerStatus">Menginisialisasi...</span>
                </div>

                <!-- Camera View -->
                <div id="reader"></div>

                <!-- Manual Input -->
                <div class="manual-input-card">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-keyboard me-2"></i>
                        Atau Masukkan Token Manual
                    </h6>
                    <form id="manualForm">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-qr-code"></i></span>
                            <input type="text" class="form-control" id="tokenInput"
                                placeholder="Paste token QR di sini..." autocomplete="off">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Validasi
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-lightbulb me-1"></i>
                            Token QR berupa string 64 karakter alfanumerik
                        </small>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Statistics & Recent Scans -->
        <div class="col-lg-4">
            <!-- Statistics -->
            <div class="stats-card">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-graph-up me-2"></i>
                    Statistik Hari Ini
                </h6>
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <h2 class="mb-0" id="totalScanned">0</h2>
                        <small>Total Scan</small>
                    </div>
                    <div class="col-6 mb-3">
                        <h2 class="mb-0" id="successScanned">0</h2>
                        <small>Berhasil</small>
                    </div>
                </div>
                <div class="text-center">
                    <small>
                        <i class="bi bi-clock me-1"></i>
                        Last scan: <span id="lastScanTime">-</span>
                    </small>
                </div>
            </div>

            <!-- Recent Scans -->
            <div class="scanner-container">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-clock-history me-2"></i>
                    Scan Terbaru
                </h6>
                <div id="recentScans" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-muted text-center">Belum ada scan</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const recentScans = [];
        let totalScanned = 0;
        let successScanned = 0;
        let html5QrCode = null;

        // Handle scan errors (failed QR reads)
        function onScanError(error) {
            // Silent error handling - jangan alert untuk setiap frame gagal
            // Hanya log ke console untuk debugging
            if (error && error.toString().includes('NotFound')) {
                console.debug('QR Code not found in frame');
            }
        }

        // Initialize scanner dengan facingMode untuk back camera HP
        async function initializeScanner() {
            try {
                html5QrCode = new Html5Qrcode("reader");

                const config = {
                    fps: 15,
                    qrbox: {
                        width: 550,
                        height: 550
                    },
                    aspectRatio: 1.0,
                };

                // Use back/rear camera untuk scanning QR
                // facingMode: "environment" = back camera (lebih baik untuk scanning)
                // facingMode: "user" = front camera
                await html5QrCode.start({
                        facingMode: "environment"
                    },
                    config,
                    onScanSuccess,
                    onScanError
                );

                document.getElementById('scannerStatus').textContent = '✅ Scanner Aktif - Siap Scan';
            } catch (error) {
                console.error('Scanner initialization error:', error);
                document.getElementById('scannerStatus').innerHTML =
                    `❌ Error: ${error.message || 'Tidak bisa akses kamera. Pastikan izin camera sudah diberikan.'}`;
            }
        }

        // Initialize on page load
        window.addEventListener('DOMContentLoaded', initializeScanner);

        function onScanSuccess(decodedText, decodedResult) {
            console.log('QR Code detected:', decodedText);

            // Extract token from URL atau langsung token
    let token = decodedText.trim();

    try {
        // Try parse as URL first
        if (token.includes('://') || token.includes('/')) {
            const url = new URL(token);
            // Try get from query param 't' first
            token = url.searchParams.get('t') || url.pathname.split('/').pop();
        }
    } catch (e) {
        // Bukan URL, anggap sebagai token langsung
        console.log('Not a URL, treating as direct token');
    }

    // Validate token: should be 64 chars alphanumeric
    if (!token || !/^[a-zA-Z0-9]{64}$/.test(token)) {
        console.error('Invalid token format', {
            token: token ? token.substring(0, 32) + '...' : 'empty',
            length: token ? token.length : 0,
        });
        alert(`❌ QR Code tidak valid.\nToken harus 64 karakter alphanumeric.\nDiterima: ${token ? token.length : 0} karakter.`);
        return;
    }

    console.log('Valid token extracted:', token.substring(0, 32) + '...');

    // Stop scanner sebelum navigate
    if (html5QrCode) {
        html5QrCode.stop().catch(err => console.error('Error stopping scanner:', err));
    }

    window.location.href = `/cms/scanner/scan/${token}`;
        }

        // Manual form
        document.getElementById('manualForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const token = document.getElementById('tokenInput').value.trim();

            if (!token) {
                alert('⚠️ Token tidak boleh kosong');
                return;
            }

            if (token.length < 32) {
                alert('⚠️ Token terlalu pendek (min 32 karakter)');
                return;
            }

            window.location.href = `/cms/scanner/scan/${token}`;
        });

        // Update statistics (dummy for now - nanti bisa fetch dari API)
        function addScanToHistory(scanData) {
            recentScans.unshift(scanData);
            if (recentScans.length > 10) recentScans.pop();

            totalScanned++;
            if (scanData.success) successScanned++;

            updateUI();
        }

        function updateUI() {
            document.getElementById('totalScanned').textContent = totalScanned;
            document.getElementById('successScanned').textContent = successScanned;
            document.getElementById('lastScanTime').textContent = new Date().toLocaleTimeString('id-ID');

            const recentScansDiv = document.getElementById('recentScans');
            if (recentScans.length === 0) {
                recentScansDiv.innerHTML = '<p class="text-muted text-center">Belum ada scan</p>';
                return;
            }

            recentScansDiv.innerHTML = recentScans.map(scan => `
        <div class="recent-scan-item ${scan.success ? '' : 'failed'}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${scan.name || 'Unknown'}</strong><br>
                    <small class="text-muted">${scan.time}</small>
                </div>
                <div>
                    ${scan.success
                        ? '<i class="bi bi-check-circle text-success fs-4"></i>'
                        : '<i class="bi bi-x-circle text-danger fs-4"></i>'}
                </div>
            </div>
        </div>
    `).join('');
        }
    </script>
@endpush
