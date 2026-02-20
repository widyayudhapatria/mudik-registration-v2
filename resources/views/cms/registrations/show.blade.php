@extends('layouts.cms')

@section('title', 'Detail Pendaftaran - CMS Admin')
@section('page-title', 'Detail Pendaftaran #' . $registration->id)

@push('styles')
<style>
    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    .detail-section {
        margin-bottom: 30px;
    }
    .detail-label {
        color: #757575;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    .detail-value {
        font-weight: 600;
        color: #212121;
        font-size: 1.05rem;
    }
    .participant-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
    }
    .document-preview {
        max-width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    @media (max-width: 992px) {
        .detail-card {
            padding: 25px;
        }

        .participant-card {
            padding: 16px;
        }
    }

    @media (max-width: 768px) {
        .detail-card {
            padding: 20px;
            margin-bottom: 16px;
        }

        .detail-section {
            margin-bottom: 20px;
        }

        .detail-section-title {
            font-size: 1.1rem;
        }

        .detail-label {
            font-size: 0.85rem;
        }

        .detail-value {
            font-size: 0.95rem;
        }

        .participant-card {
            padding: 16px;
            margin-bottom: 12px;
        }

        .participant-card h6 {
            font-size: 0.95rem;
        }

        .document-preview {
            max-width: 100%;
        }

        /* Stack sidebar on mobile */
        .col-lg-8,
        .col-lg-4 {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .detail-card {
            padding: 16px;
        }

        .detail-card h5 {
            font-size: 1rem;
        }

        .detail-card h6 {
            font-size: 0.9rem;
        }

        .btn {
            font-size: 0.9rem;
            padding: 8px 16px;
        }
    }

    .medium-zoom-overlay,
    .medium-zoom-image--opened {
        z-index: 1001 !important;
    }
</style>
@endpush

@section('content')
<div class="mb-3">
    <a href="{{ route('cms.registrations.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="row">
    <!-- Main Info -->
    <div class="col-lg-8">
        <div class="detail-card">
            <h5 class="fw-bold mb-4">Informasi Perwakilan</h5>

            <div class="row detail-section mb-0">
                <div class="col-md-6 mb-3">
                    <div class="detail-label">Nama Lengkap</div>
                    <div class="detail-value">{{ $registration->representative_name }}</div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="detail-label">NIK</div>
                    <div class="detail-value">{{ $registration->representative_nik }}</div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="detail-label">Tanggal Lahir</div>
                    <div class="detail-value">{{ $registration->representative_birth_date->format('d/m/Y') }}</div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">{{ $registration->formLink->email }}</div>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <h5 class="fw-bold mb-4">Informasi Data Keluarga</h5>
            <div class="row detail-section mb-0">
                <div class="col-md-6 mb-3">
                    <div class="detail-label">Jumlah Anggota Keluarga</div>
                    <div class="detail-value">{{ $registration->family_count }} orang</div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="detail-label">Nomor Kartu Keluarga</div>
                    <div class="detail-value">{{ $registration->kk_number }}</div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="detail-label">Tujuan</div>
                    <div class="detail-value">{{ $registration->destination->name }}</div>
                </div>
            </div>
        </div>

        <!-- Participants -->
        <div class="detail-card">
            <h5 class="fw-bold mb-4">
                <i class="bi bi-people-fill me-2"></i>
                Daftar Peserta ({{ $registration->participants->count() }})
            </h5>

            @foreach($registration->participants as $index => $participant)
            <div class="participant-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-bold mb-2">{{ $index + 1 }}. {{ $participant->full_name }}</h6>
                        <div class="small text-muted mb-1">NIK/KIA: {{ $participant->nik_kia }}</div>
                        <div class="small text-muted">Lahir: {{ $participant->birth_date->format('d/m/Y') }} ({{ $participant->getAge() }} tahun)</div>
                    </div>
                    @if($participant->is_child_under_4)
                    <span class="badge bg-warning text-dark">< 4 tahun</span>
                    @endif
                </div>
            </div>
            @endforeach

            @if($registration->has_child_under_4)
            <div class="alert alert-info">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Menyetujui :</strong> Ada anak dibawah 4 tahun yang akan dipangku.
            </div>
            @endif
        </div>

        <!-- Document KK -->
        <div class="detail-card">
            <h5 class="fw-bold mb-4">Dokumen Kartu Keluarga</h5>

            @if($registration->kk_document_path)
            <div class="text-center">
                @if(Str::endsWith($registration->kk_document_path, '.pdf'))
                <a href="{{ Storage::url($registration->kk_document_path) }}" target="_blank" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>Lihat PDF
                </a>
                @else
                <img id="kkDocumentImage" src="{{ Storage::url($registration->kk_document_path) }}" alt="Kartu Keluarga" class="document-preview" style="cursor: zoom-in;">
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('kkDocumentImage').click()">
                        <i class="bi bi-zoom-in me-2"></i>Perbesar
                    </button>
                </div>
                @endif
            </div>
            @else
            <p class="text-muted text-center">Dokumen tidak tersedia</p>
            @endif
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="detail-card">
            <h6 class="fw-bold mb-3">Status Pendaftaran</h6>

            @if($registration->isBypass())
            <div class="alert alert-info mb-3">
                <i class="bi bi-upload me-2"></i>
                <strong>Import Bypass</strong>
            </div>
            @endif

            @if($registration->isApproved())
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Disetujui</strong>
            </div>

            <div class="small">
                <div class="mb-2">
                    <strong>Disetujui oleh:</strong><br>
                    {{ $registration->approvedBy->name }}
                </div>
                <div class="mb-2">
                    <strong>Waktu:</strong><br>
                    {{ $registration->approved_at->format('d/m/Y H:i') }}
                </div>
                @if($registration->admin_notes)
                <div>
                    <strong>Catatan:</strong><br>
                    {{ $registration->admin_notes }}
                </div>
                @endif
            </div>

            @if($registration->qrCode)
            <hr>
            <a href="{{ route('scan.view', $registration->qrCode->token_qr) }}" class="btn btn-primary w-100" target="_blank">
                <i class="bi bi-qr-code me-2"></i>Lihat QR Code
            </a>
            @endif

            @elseif($registration->isRejected())
            <div class="alert alert-danger mb-3">
                <i class="bi bi-x-circle-fill me-2"></i>
                <strong>Ditolak</strong>
            </div>

            <div class="small">
                <div class="mb-2">
                    <strong>Ditolak oleh:</strong><br>
                    {{ $registration->rejectedBy->name }}
                </div>
                <div class="mb-2">
                    <strong>Waktu:</strong><br>
                    {{ $registration->rejected_at->format('d/m/Y H:i') }}
                </div>
                <div class="mb-2">
                    <strong>Alasan:</strong><br>
                    {{ $registration->rejection_reason }}
                </div>
                @if($registration->admin_notes)
                <div>
                    <strong>Catatan:</strong><br>
                    {{ $registration->admin_notes }}
                </div>
                @endif
            </div>

            @else
            <div class="alert alert-warning mb-3">
                <i class="bi bi-clock-fill me-2"></i>
                <strong>Menunggu Review</strong>
            </div>

            <!-- Approval Form -->
            @can('approve', $registration)
            <form id="approveForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan (opsional)</label>
                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-check-circle-fill me-2"></i>Setujui Pendaftaran
                </button>
            </form>
            @endcan

            <hr>

            <!-- Rejection Form -->
            @can('reject', $registration)
            <form id="rejectForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="rejection_reason" rows="3" placeholder="Jelaskan alasan penolakan" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan (opsional)</label>
                    <textarea class="form-control" name="admin_notes" rows="2" placeholder="Catatan tambahan"></textarea>
                </div>
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-x-circle-fill me-2"></i>Tolak Pendaftaran
                </button>
            </form>
            @endcan
            @endif
        </div>

        <!-- Timeline -->
        <div class="detail-card">
            <h6 class="fw-bold mb-3">Timeline</h6>

            <div class="small">
                <div class="mb-3">
                    <i class="bi bi-circle-fill text-primary me-2" style="font-size: 0.5rem;"></i>
                    <strong>Dibuat:</strong><br>
                    <span class="text-muted ms-3">{{ $registration->created_at->format('d/m/Y H:i') }}</span>
                </div>

                @if($registration->approved_at)
                <div class="mb-3">
                    <i class="bi bi-circle-fill text-success me-2" style="font-size: 0.5rem;"></i>
                    <strong>Disetujui:</strong><br>
                    <span class="text-muted ms-3">{{ $registration->approved_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif

                @if($registration->rejected_at)
                <div class="mb-3">
                    <i class="bi bi-circle-fill text-danger me-2" style="font-size: 0.5rem;"></i>
                    <strong>Ditolak:</strong><br>
                    <span class="text-muted ms-3">{{ $registration->rejected_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/medium-zoom/1.1.0/medium-zoom.min.js" defer></script>
<script>
// Initialize medium-zoom when library is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof mediumZoom !== 'undefined') {
        mediumZoom('#kkDocumentImage', {
            margin: 24,
            background: '#fafafa',
        });
    }
});

document.getElementById('approveForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!confirm('Yakin ingin menyetujui pendaftaran ini?')) return;

    const formData = new FormData(this);

    try {
        const response = await axios.post(
            '{{ route("cms.registrations.approve", $registration) }}',
            Object.fromEntries(formData)
        );

        if (response.data.success) {
            alert('Pendaftaran berhasil disetujui!');
            location.reload();
        }
    } catch (error) {
        alert('Gagal menyetujui: ' + (error.response?.data?.message || error.message));
    }
});

document.getElementById('rejectForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!confirm('Yakin ingin menolak pendaftaran ini?')) return;

    const formData = new FormData(this);

    try {
        const response = await axios.post(
            '{{ route("cms.registrations.reject", $registration) }}',
            Object.fromEntries(formData)
        );

        if (response.data.success) {
            alert('Pendaftaran berhasil ditolak');
            location.reload();
        }
    } catch (error) {
        alert('Gagal menolak: ' + (error.response?.data?.message || error.message));
    }
});
</script>
@endpush
