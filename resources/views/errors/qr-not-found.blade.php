@extends('layouts.cms')
@section('title', 'QR Code Tidak Ditemukan')
@section('page-title', 'QR Code Tidak Ditemukan')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        QR Code Tidak Ditemukan
                    </h4>
                </div>
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-qr-code-scan" style="font-size: 100px; color: #dc3545;"></i>
                    </div>

                    <div class="alert alert-danger">
                        <strong>Error:</strong> {{ $message }}
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Token yang Dicari:</h6>
                            <code class="d-block p-2 bg-white border rounded">
                                {{ $token }}
                            </code>
                            <small class="text-muted">
                                Panjang: {{ $debug['token_length'] ?? 0 }} karakter
                            </small>
                        </div>
                    </div>

                    <h5 class="mb-3">Kemungkinan Penyebab:</h5>
                    <ul class="list-group mb-4">
                        <li class="list-group-item">
                            <i class="bi bi-1-circle me-2"></i>
                            Token tidak ada di database (QR belum di-generate)
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-2-circle me-2"></i>
                            QR Code sudah kadaluarsa atau dihapus
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-3-circle me-2"></i>
                            Token di-regenerate (QR lama tidak valid lagi)
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-4-circle me-2"></i>
                            Salah environment database (.env)
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-5-circle me-2"></i>
                            QR Code rusak atau tidak terbaca dengan benar
                        </li>
                    </ul>

                    <h5 class="mb-3">Solusi:</h5>
                    <div class="alert alert-info">
                        <ol class="mb-0">
                            <li>Pastikan registrasi sudah di-<strong>approve</strong> oleh admin</li>
                            <li>Cek email participant untuk QR code terbaru</li>
                            <li>Hubungi admin untuk regenerate QR code</li>
                            <li>Verifikasi database environment sudah benar</li>
                        </ol>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('cms.scanner.index') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>
                            Kembali ke Scanner
                        </a>
                        <a href="{{ route('cms.registrations.index') }}" class="btn btn-secondary btn-lg">
                            <i class="bi bi-list-ul me-2"></i>
                            Lihat Registrasi
                        </a>
                    </div>
                </div>
            </div>

            @if(config('app.debug'))
            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning">
                    <strong>Debug Info (Only in Debug Mode)</strong>
                </div>
                <div class="card-body">
                    <pre class="mb-0">{{ json_encode($debug, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
