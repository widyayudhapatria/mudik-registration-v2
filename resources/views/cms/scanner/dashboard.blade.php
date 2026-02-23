@extends('layouts.cms')
@section('title', 'Dashboard Scanner')
@section('page-title', 'Dashboard Scanner QR Code')

@push('styles')
    <style>
        .dashboard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 18px;
            text-align: center;
        }

        .stat-box.total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-box.scanned {
            background: linear-gradient(135deg, #43a047 0%, #1b5e20 100%);
        }

        .stat-box.unscanned {
            background: linear-gradient(135deg, #e53935 0%, #b71c1c 100%);
        }

        .stat-box.percentage {
            background: linear-gradient(135deg, #ff6f00 0%, #e65100 100%);
        }

        .stat-box.today {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
        }

        .stat-box.registrations {
            background: linear-gradient(135deg, #7b1fa2 0%, #4a148c 100%);
        }

        .stat-box h4 {
            margin: 0 0 8px 0;
            font-size: 13px;
            font-weight: 500;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-box .value {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }

        .stat-box .unit {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 4px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .dashboard-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .last-update {
            font-size: 12px;
            color: #999;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead th {
            background: #f5f5f5;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }

        table tbody tr:hover {
            background: #fafafa;
        }

        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #43a047 0%, #1b5e20 100%);
            transition: width 0.3s ease;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge.success {
            background: #e8f5e9;
            color: #1b5e20;
        }

        .badge.warning {
            background: #fff3e0;
            color: #e65100;
        }

        .pagination {
            display: flex;
            gap: 5px;
            margin-top: 15px;
            justify-content: center;
        }

        .pagination button {
            padding: 6px 10px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: 0.2s;
        }

        .pagination button:hover:not(:disabled) {
            background: #f5f5f5;
            border-color: #999;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination button.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .kota-progress {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .kota-progress strong {
            min-width: 120px;
            text-align: right;
        }

        .destination-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .destination-row:hover {
            background: #fafafa;
        }

        .destination-row strong {
            font-weight: 600;
        }

        .destination-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 12px 16px;
            background: #f5f5f5;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 2px solid #e0e0e0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- Summary Stats --}}
        <div class="stats-container">
            <div class="stat-box total">
                <h4><i class="bi bi-people-fill me-2" aria-hidden="true"></i>Total Peserta Mudik</h4>
                <p class="value" id="totalParticipants">0</p>
            </div>
            <div class="stat-box scanned">
                <h4><i class="bi bi-check2-circle me-2" aria-hidden="true"></i>Sudah Scan</h4>
                <p class="value" id="scannedParticipants">0</p>
                <div class="unit">peserta</div>
            </div>
            <div class="stat-box unscanned">
                <h4><i class="bi bi-x-circle-fill me-2" aria-hidden="true"></i>Belum Scan</h4>
                <p class="value" id="unscannedParticipants">0</p>
                <div class="unit">peserta</div>
            </div>
            <div class="stat-box percentage">
                <h4><i class="bi bi-percent me-2" aria-hidden="true"></i>Persentase</h4>
                <p class="value" id="completionPercentage">0%</p>
            </div>
            <div class="stat-box today">
                <h4><i class="bi bi-calendar-day me-2" aria-hidden="true"></i>Scan Hari Ini</h4>
                <p class="value" id="todayScans">0</p>
            </div>
        </div>

        {{-- Scan Logs Section --}}
        <div class="dashboard-card">
            <div class="dashboard-header">
                <h4><i class="bi bi-list-check me-2"></i>Riwayat Scan Terbaru</h4>
                <small class="last-update">Update terakhir: <span id="logsUpdateTime">-</span></small>
            </div>

            <div class="table-responsive">
                <table id="scanLogsTable" class="table table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Nama Perwakilan</th>
                            <th>Kota</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Petugas</th>
                            <th>Log Pesan</th>
                        </tr>
                    </thead>
                    <tbody id="scanLogsBody">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-hourglass-split me-2"></i>Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <button id="prevBtn" onclick="loadScanLogs(currentPage - 1)">← Sebelumnya</button>
                <span id="pageInfo" style="padding: 6px 12px; font-size: 12px; color: #666;"></span>
                <button id="nextBtn" onclick="loadScanLogs(currentPage + 1)">Selanjutnya →</button>
            </div>
        </div>

        {{-- Breakdown by Destination --}}
        <div class="dashboard-card">
            <div class="dashboard-header">
                <h4><i class="bi bi-geo-alt me-2"></i>Breakdown Scan per Kota</h4>
                <small class="last-update">Update terakhir: <span id="destUpdateTime">-</span></small>
            </div>

            <div id="destinationBreakdownContainer">
                <div class="empty-state">
                    <i class="bi bi-hourglass-split"></i>
                    <p>Loading data...</p>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        let currentPage = 1;

        async function loadStatistics() {
            try {
                const res = await fetch('/cms/scanner/api/statistics', {
                    credentials: 'include',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();

                if (json.success && json.data) {
                    const d = json.data;

                    document.getElementById('totalParticipants').textContent = d.total_participants;
                    document.getElementById('scannedParticipants').textContent = d.scanned_participants;
                    document.getElementById('unscannedParticipants').textContent = d.unscanned_participants;
                    document.getElementById('completionPercentage').textContent = d.completion_percentage + '%';
                    document.getElementById('todayScans').textContent = d.today_scans;
                    document.getElementById('logsUpdateTime').textContent = formatTime(new Date());
                }
            } catch (err) {
                console.error('Load statistics error:', err);
            }
        }

        async function loadScanLogs(page = 1) {
            try {
                const res = await fetch(`/cms/scanner/api/scan-logs?page=${page}`, {
                    credentials: 'include',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();

                if (json.success && json.data) {
                    currentPage = json.pagination.current_page;
                    const tbody = document.getElementById('scanLogsBody');

                    if (json.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox me-2"></i>Belum ada riwayat scan
                                </td>
                            </tr>`;
                    } else {
                        tbody.innerHTML = json.data.map(log => {
                            const isFailed = (log.status || '').toLowerCase() === 'failed' || (log.status || '').toLowerCase() === 'gagal';
                            const badgeClass = isFailed ? 'badge bg-danger' : 'badge bg-success';
                            const icon = isFailed ? 'x-circle-fill' : 'check-circle-fill';
                            const statusText = isFailed ? 'Gagal' : 'Berhasil';
                            return `
                            <tr>
                                <td>${log.waktu}</td>
                                <td><strong>${log.nama}</strong></td>
                                <td>${log.destination ?? 'N/A'}</td>
                                <td class="text-center"><span class="badge bg-primary">${log.jumlah ?? 'N/A'} orang</span></td>
                                <td><span class="${badgeClass}"><i class="bi bi-${icon} me-1"></i>${statusText}</span></td>
                                <td>${log.petugas}</td>
                                <td>${log.failure_reason ?? 'N/A'}</td>
                            </tr>`;
                        }).join('');
                    }

                    // Update pagination
                    document.getElementById('pageInfo').textContent =
                        `Halaman ${json.pagination.current_page} dari ${json.pagination.last_page}`;
                    document.getElementById('prevBtn').disabled = json.pagination.current_page === 1;
                    document.getElementById('nextBtn').disabled = !json.pagination.has_more;
                    document.getElementById('logsUpdateTime').textContent = formatTime(new Date());
                }
            } catch (err) {
                console.error('Load scan logs error:', err);
            }
        }

        async function loadDestinationBreakdown() {
            try {
                const res = await fetch('/cms/scanner/api/scan-by-destination', {
                    credentials: 'include',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json();

                if (json.success && json.data) {
                    const container = document.getElementById('destinationBreakdownContainer');

                    if (json.data.length === 0) {
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-geo-alt"></i>
                                <p>Tidak ada data kota</p>
                            </div>`;
                        return;
                    }

                    // Build a responsive Bootstrap table for destination breakdown
                    let html = `
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Kota</th>
                                        <th class="text-center">Kuota</th>
                                        <th class="text-center">Total Peserta</th>
                                        <th class="text-center">Sudah Scan</th>
                                        <th class="text-center">Belum Scan</th>
                                        <th class="text-center">Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    json.data.forEach(dest => {
                        html += `
                                    <tr>
                                        <td><strong>${dest.kota}</strong></td>
                                        <td class="text-center">${dest.total_quota}</td>
                                        <td class="text-center">${dest.total_peserta}</td>
                                        <td class="text-center"><span class="badge bg-success">${dest.sudah_scan}</span></td>
                                        <td class="text-center"><span class="badge bg-warning text-dark">${dest.belum_scan}</span></td>
                                        <td class="text-center">
                                            <strong>${dest.persentase}%</strong>
                                            <div class="progress-bar-container" style="margin-top: 6px;">
                                                <div class="progress-bar" style="width: ${dest.persentase}%"></div>
                                            </div>
                                        </td>
                                    </tr>`;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>`;

                    container.innerHTML = html;
                    document.getElementById('destUpdateTime').textContent = formatTime(new Date());
                }
            } catch (err) {
                console.error('Load destination breakdown error:', err);
            }
        }

        function formatTime(date) {
            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        // Initial load
        loadStatistics();
        loadScanLogs(1);
        loadDestinationBreakdown();

        // Auto-refresh every 30 seconds
        setInterval(() => {
            loadStatistics();
            loadScanLogs(currentPage);
            loadDestinationBreakdown();
        }, 30000);
    </script>
@endpush
