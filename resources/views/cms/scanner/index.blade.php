@extends('layouts.cms')
@section('title', 'Scanner QR Code')
@section('page-title', 'Scanner QR Code')
@section('content')
<div style="background:white; border-radius:16px; padding:30px;">
    <div id="reader" style="width:100%; max-width:600px; margin:0 auto;"></div>
    <div style="background:#f8f9fa; border-radius:12px; padding:20px; margin-top:20px;">
        <h6 class="fw-bold mb-3">Atau Masukkan Token Manual</h6>
        <form id="manualForm">
            <div class="input-group">
                <input type="text" class="form-control" id="tokenInput" placeholder="Token QR">
                <button type="submit" class="btn btn-primary">Validasi</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function extractToken(scannedValue) {
        try {
            const url = new URL(scannedValue);
            if (url.searchParams.has('t')) {
                return url.searchParams.get('t');
            }
            const pathParts = url.pathname.split('/').filter(Boolean);
            const scanIndex = pathParts.indexOf('scan');
            if (scanIndex !== -1 && pathParts[scanIndex + 1]) {
                return pathParts[scanIndex + 1];
            }
        } catch {
        }
        return scannedValue;
    }

    function redirectToScan(scannedValue) {
        const token = extractToken(scannedValue.trim());
        if (token) {
            window.location.href = `/cms/scanner/scan/${encodeURIComponent(token)}`;
        }
    }

    const scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
    scanner.render(scannedValue => {
        scanner.pause();
        redirectToScan(scannedValue);
    });

    document.getElementById('manualForm').addEventListener('submit', e => {
        e.preventDefault();
        const value = document.getElementById('tokenInput').value.trim();
        if (value) redirectToScan(value);
    });
</script>
@endpush