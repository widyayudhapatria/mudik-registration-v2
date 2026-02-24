@extends('layouts.cms')

@section('title', 'Edit Admin - Mudik Gratis')
@section('page-title', 'Edit Admin: ' . $admin->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Data Admin</h5>
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

                    <form action="{{ route('cms.admin-management.update', $admin) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $admin->name) }}"
                                   placeholder="Nama lengkap admin" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $admin->email) }}"
                                   placeholder="admin@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror"
                                    id="role" name="role" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" {{ old('role', $admin->role) === $value ? 'selected' : '' }}>
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
                                   {{ old('can_scan', $admin->can_scan) ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_scan">
                                Izinkan Scan QR Code
                            </label>
                            <small class="d-block text-muted mt-1">
                                Centang jika admin ini diizinkan untuk menscan QR Code
                            </small>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan
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
            <!-- Admin Info Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Info Admin</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td><strong>{{ $admin->email }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if ($admin->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td><small>{{ $admin->created_at->format('d M Y H:i') }}</small></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Login:</td>
                            <td>
                                <small>
                                    @if ($admin->last_login_at)
                                        {{ $admin->last_login_at->format('d M Y H:i') }}
                                    @else
                                        <span class="text-muted">Belum login</span>
                                    @endif
                                </small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Additional Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Aksi Lainnya</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('cms.admin-management.change-password.show', $admin) }}"
                       class="btn btn-warning btn-sm">
                        <i class="bi bi-key"></i> Ubah Password
                    </a>

                    @if (auth('admin')->id() !== $admin->id)
                        <button type="button" class="btn btn-{{ $admin->is_active ? 'danger' : 'success' }} btn-sm"
                                onclick="toggleStatus()">
                            <i class="bi {{ $admin->is_active ? 'bi-lock' : 'bi-unlock' }}"></i>
                            {{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }} Admin
                        </button>

                        @if (!$admin->isSuperAdmin())
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteAdmin()">
                                <i class="bi bi-trash"></i> Hapus Admin
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Hapus Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus admin <strong>{{ $admin->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('cms.admin-management.destroy', $admin) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleStatus() {
    if (confirm('Apakah Anda yakin ingin mengubah status admin ini?')) {
        fetch(`/cms/admin-management/{{ $admin->id }}/toggle-active`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}

function deleteAdmin() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endpush
