@extends('layouts.cms')

@section('title', 'Tambah Admin - Mudik Gratis')
@section('page-title', 'Tambah Admin Baru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Form Tambah Admin Baru</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Ada kesalahan pada form:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('cms.admin-management.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="Nama lengkap admin" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   placeholder="admin@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password"
                                   placeholder="Minimalnya 8 karakter" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation"
                                   placeholder="Ulangi password" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror"
                                    id="role" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" class="form-check-input"
                                   id="can_scan" name="can_scan" value="1"
                                   {{ old('can_scan') ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_scan">
                                Izinkan Scan QR Code
                            </label>
                            <small class="d-block text-muted mt-1">
                                Centang jika admin ini diizinkan untuk menscan QR Code
                            </small>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Simpan Admin
                            </button>
                            <a href="{{ route('cms.admin-management.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title mb-3">Informasi Role</h6>
                    <div class="mb-3">
                        <small><strong>Super Admin:</strong></small>
                        <p class="small text-muted mb-2">Akses penuh ke semua fitur management</p>
                    </div>
                    <div class="mb-3">
                        <small><strong>Validator:</strong></small>
                        <p class="small text-muted mb-2">Dapat validasi dan menolak registrasi</p>
                    </div>
                    <div class="mb-3">
                        <small><strong>Scanner:</strong></small>
                        <p class="small text-muted">Dapat menscan QR Code saja</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
