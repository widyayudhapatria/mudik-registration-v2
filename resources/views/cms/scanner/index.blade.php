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
const scanner = new Html5QrcodeScanner("reader", {fps:10, qrbox:250});
scanner.render(token => {
    scanner.pause();
    window.location.href = `/cms/scanner/scan/${token}`;
});
document.getElementById('manualForm').addEventListener('submit', e => {
    e.preventDefault();
    const token = document.getElementById('tokenInput').value.trim();
    if(token) window.location.href = `/cms/scanner/scan/${token}`;
});
</script>
@endpush
