@extends('layouts.cms')

@section('title', 'Manajemen Destinasi - CMS Admin')
@section('page-title', 'Manajemen Destinasi')

@push('styles')
    <style>
        .destination-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
        }

        .destination-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .destination-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .destination-name {
            font-size: 20px;
            font-weight: 700;
            color: #212121;
        }

        .destination-code {
            font-size: 14px;
            color: #757575;
            background: #f5f5f5;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .quota-progress {
            margin: 15px 0;
        }

        .quota-bar {
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }

        .quota-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50 0%, #2E7D32 100%);
            transition: width 0.3s ease;
        }

        .quota-bar-fill.warning {
            background: linear-gradient(90deg, #FF9800 0%, #F57C00 100%);
        }

        .quota-bar-fill.danger {
            background: linear-gradient(90deg, #f44336 0%, #d32f2f 100%);
        }

        .quota-info {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 13px;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .nonaktif {
            opacity: 0.6;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header Actions -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Daftar Kota Tujuan Mudik</h5>
                                <p class="text-muted mb-0 small">Kelola destinasi dan alokasi quota per kota</p>
                                @if (isset($global_quota))
                                    <div class="small text-muted mt-1">Global Quota Tahun {{ $global_quota->year }} :
                                        <strong>{{ number_format($global_quota->total_quota) }}</strong> —
                                        Allocated:
                                        <strong>{{ number_format($global_quota->allocated_quota) }}</strong>,
                                        Remaining:
                                        <strong>{{ number_format($global_quota->remaining_global_quota) }}</strong>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-primary" onclick="showAddDestinationModal()">
                                <i class="mdi mdi-plus-circle me-1"></i> Tambah Destinasi
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Destinations List -->
                <div id="destinationsList" class="row">
                    @foreach ($destinations as $destination)
                        <div class="col-12 col-md-6 mb-3">
                            <div class="destination-card {{ $destination['is_active'] ? 'bg-white' : 'bg-secondary nonaktif' }}" data-id="{{ $destination['id'] }}">
                                <div class="destination-header">
                                    <div>
                                        <span class="destination-name">{{ $destination['name'] }}</span>
                                        <span class="destination-code">{{ $destination['code'] }}</span>
                                    </div>
                                    <div>
                                        @if ($destination['is_active'])
                                            <span class="badge bg-success badge-status">Aktif</span>
                                        @else
                                            <span class="badge bg-danger badge-status">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($destination['description'])
                                    <p class="text-muted small mb-3">{{ $destination['description'] }}</p>
                                @endif

                                <div class="quota-progress">
                                    @php
                                        $percentage =
                                            $destination['total_quota'] > 0
                                                ? ($destination['used_quota'] / $destination['total_quota']) * 100
                                                : 0;
                                        $barClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                                    @endphp
                                    <div class="quota-bar">
                                        <div class="quota-bar-fill {{ $barClass }}" style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                    <div class="quota-info">
                                        <span class="text-muted">
                                            <strong>{{ $destination['used_quota'] }}</strong> terpakai dari
                                            <strong>{{ $destination['total_quota'] }}</strong> orang
                                        </span>
                                        <span class="text-primary fw-bold">
                                            {{ $destination['remaining_quota'] }} tersisa
                                        </span>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-4">
                                        <small class="text-muted d-block">Daily Quotas</small>
                                        <strong>{{ $destination['daily_quotas_count'] }}</strong> terjadwal
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Peserta mudik</small>
                                        <strong>{{ $destination['total_participants'] }}</strong> peserta
                                        <small class="text-muted">(*dewasa)</small>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Registrant</small>
                                        <strong>{{ $destination['registrations_count'] }}</strong> pendaftar
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="editDestination({{ $destination['id'] }})">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                        onclick="toggleActive({{ $destination['id'] }}, {{ $destination['is_active'] ? 'false' : 'true' }})">
                                        <i class="bi bi-{{ $destination['is_active'] ? 'eye-slash' : 'eye' }} me-1"></i>
                                        {{ $destination['is_active'] ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                    @if ($destination['registrations_count'] == 0 && $destination['daily_quotas_count'] == 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteDestination({{ $destination['id'] }}, '{{ $destination['name'] }}')">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Destination Modal -->
    <div class="modal fade" id="destinationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Destinasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="destinationForm">
                        <input type="hidden" id="destinationId" value="">

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Kota <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" required minlength="3"
                                maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Kode Kota <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" required minlength="2" maxlength="20"
                                style="text-transform: uppercase;" placeholder="Contoh: MLG, SMG">
                            <small class="text-muted">Kode unik 2-20 karakter (huruf kapital)</small>
                        </div>

                        <div class="mb-3">
                            <label for="total_quota" class="form-label">Total Quota <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="total_quota" required min="1">
                            <small class="text-muted">Jumlah quota yang dialokasikan untuk kota ini</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="display_order" class="form-label">Urutan Tampil</label>
                            <input type="number" class="form-control" id="display_order" value="0"
                                min="0">
                            <small class="text-muted">Urutan tampilan di form pendaftaran (default: 0)</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveDestination()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function showAddDestinationModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Destinasi';
            document.getElementById('destinationForm').reset();
            document.getElementById('destinationId').value = '';
            document.getElementById('is_active').checked = true;
            new bootstrap.Modal(document.getElementById('destinationModal')).show();
        }

        async function editDestination(id) {
            try {
                const response = await fetch(`/cms/destinations/${id}`);
                const result = await response.json();

                if (result.success) {
                    const dest = result.data;
                    document.getElementById('modalTitle').textContent = 'Edit Destinasi';
                    document.getElementById('destinationId').value = dest.id;
                    document.getElementById('name').value = dest.name;
                    document.getElementById('code').value = dest.code;
                    document.getElementById('total_quota').value = dest.total_quota;
                    document.getElementById('description').value = dest.description || '';
                    document.getElementById('display_order').value = dest.display_order || 0;
                    document.getElementById('is_active').checked = dest.is_active;

                    new bootstrap.Modal(document.getElementById('destinationModal')).show();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal memuat data destinasi');
            }
        }

        async function saveDestination() {
            const form = document.getElementById('destinationForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const id = document.getElementById('destinationId').value;
            const data = {
                name: document.getElementById('name').value,
                code: document.getElementById('code').value.toUpperCase(),
                total_quota: parseInt(document.getElementById('total_quota').value),
                description: document.getElementById('description').value || '',
                display_order: parseInt(document.getElementById('display_order').value) || 0,
                is_active: document.getElementById('is_active').checked
            };

            try {
                const url = id ? `/cms/destinations/${id}` : '/cms/destinations';
                const method = id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('destinationModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan destinasi');
            }
        }

        async function toggleActive(id, activate) {
            if (!confirm(`Yakin ingin ${activate ? 'mengaktifkan' : 'menonaktifkan'} destinasi ini?`)) {
                return;
            }

            try {
                const response = await fetch(`/cms/destinations/${id}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal mengubah status destinasi');
            }
        }

        async function deleteDestination(id, name) {
            if (!confirm(
                    `Yakin ingin menghapus destinasi "${name}"?\n\nPeringatan: Tindakan ini tidak dapat dibatalkan!`)) {
                return;
            }

            try {
                const response = await fetch(`/cms/destinations/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menghapus destinasi');
            }
        }
    </script>
@endpush
