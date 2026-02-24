@extends('layouts.cms')

@section('title', 'Admin Management - Mudik Gratis')
@section('page-title', 'Admin Management')

@section('content')
<div class="container-fluid">
    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header with Create Button -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-0">Daftar Admin</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('cms.admin-management.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Tambah Admin Baru
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cms.admin-management.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Cari (Nama/Email)</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Cari nama atau email...">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">Semua Role</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="validator" {{ request('role') === 'validator' ? 'selected' : '' }}>Validator</option>
                        <option value="scanner" {{ request('role') === 'scanner' ? 'selected' : '' }}>Scanner</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('cms.admin-management.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Admin List Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Scan Permission</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Last Login</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admins as $admin)
                        <tr>
                            <td><strong>{{ $admin->name }}</strong></td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                <span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $admin->role)) }}</span>
                            </td>
                            <td>
                                @if ($admin->can_scan)
                                    <span class="badge bg-success">Ya</span>
                                @else
                                    <span class="badge bg-secondary">Tidak</span>
                                @endif
                            </td>
                            <td>
                                @if ($admin->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $admin->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <small>
                                    @if ($admin->last_login_at)
                                        {{ $admin->last_login_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Belum login</span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('cms.admin-management.edit', $admin) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if (auth('admin')->id() !== $admin->id)
                                        <button type="button" class="btn btn-outline-warning"
                                                onclick="toggleAdminStatus({{ $admin->id }})"
                                                title="{{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="bi {{ $admin->is_active ? 'bi-lock' : 'bi-unlock' }}"></i>
                                        </button>

                                        @if (!$admin->isSuperAdmin())
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="deleteAdmin({{ $admin->id }})"
                                                    title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Tidak ada admin yang ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($admins->hasPages())
            <div class="card-footer">
                {{ $admins->links() }}
            </div>
        @endif
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
                <p>Apakah Anda yakin ingin menghapus admin ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
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
function deleteAdmin(adminId) {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/cms/admin-management/${adminId}`;
    deleteModal.show();
}

function toggleAdminStatus(adminId) {
    if (confirm('Apakah Anda yakin ingin mengubah status admin ini?')) {
        fetch(`/cms/admin-management/${adminId}/toggle-active`, {
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
</script>
@endpush
