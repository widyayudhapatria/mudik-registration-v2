@extends('layouts.cms')

@section('title', 'Confirm Email Log Fix')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Email Log Fix
                    </h5>
                </div>
                <div class="card-body">
                    @if($importData)
                        <div class="alert alert-danger" role="alert">
                            <h6 class="font-weight-bold"><i class="fas fa-exclamation-triangle"></i> PERINGATAN PENTING</h6>
                            <p class="mb-2">
                                Tindakan ini akan mengubah status email log secara permanen dari <strong>SENT</strong> menjadi <strong>FAILED</strong>.
                                Pastikan data yang akan diproses sudah benar. Proses ini tidak dapat di-undo.
                            </p>
                            <p class="mb-0">
                                <strong>Hanya email yang memenuhi syarat berikut yang akan diupdate:</strong><br>
                                ✓ Email log status = <span class="badge bg-success">SENT</span><br>
                                ✓ Form link status = <span class="badge bg-success">PENDING</span>
                            </p>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Summary</h6>
                            <ul class="mb-0">
                                <li><strong>File:</strong> {{ $importData['filename'] }}</li>
                                <li><strong>Total Emails:</strong> {{ $importData['total_emails'] }}</li>
                                <li class="mt-2 border-top pt-2">
                                    <strong>Status Breakdown:</strong>
                                    <ul class="list-unstyled mb-0 pl-3">
                                        <li><span class="badge bg-success">Can Fix (Status SENT):</span> {{ $importData['status_counts']['can_fix'] }}</li>
                                        <li><span class="badge bg-danger">Already Failed:</span> {{ $importData['status_counts']['already_failed'] }}</li>
                                        <li><span class="badge bg-warning">Pending:</span> {{ $importData['status_counts']['pending'] }}</li>
                                        <li><span class="badge bg-secondary">No Log:</span> {{ $importData['status_counts']['no_log'] }}</li>
                                    </ul>
                                </li>
                                <li class="mt-2 border-top pt-2">
                                    <strong style="color: #27ae60;">
                                        <i class="fas fa-check-circle"></i>
                                        Yang akan diupdate: {{ $importData['status_counts']['can_fix'] }} email
                                    </strong>
                                </li>
                            </ul>
                        </div>

                        @if($importData['status_counts']['can_fix'] > 0)
                            <h6 class="font-weight-bold mt-4 mb-3">Preview Email yang Akan Diupdate (Max 20)</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered">
                                    <thead class="table-success">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="28%">Email</th>
                                            <th width="12%">Email Status</th>
                                            <th width="12%">FormLink Status</th>
                                            <th width="18%">Sent At</th>
                                            <th width="25%">Aksi yang Akan Dilakukan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $count = 0;
                                        @endphp
                                        @foreach($importData['email_statuses'] as $status)
                                            @if($status['can_fix'] && $count < 20)
                                                @php
                                                    $count++;
                                                @endphp
                                                <tr>
                                                    <td>{{ $count }}</td>
                                                    <td><code>{{ $status['email'] }}</code></td>
                                                    <td><span class="badge bg-success">SENT</span></td>
                                                    <td><span class="badge bg-success">PENDING</span></td>
                                                    <td>{{ $status['sent_at'] }}</td>
                                                    <td>
                                                        <span class="badge bg-danger">→ FAILED</span>
                                                        <small class="text-muted d-block">failed_at = {{ $status['sent_at'] }}</small>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($importData['status_counts']['can_fix'] > 20)
                                <p class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Menampilkan 20 data pertama. Total yang akan diupdate: <strong>{{ $importData['status_counts']['can_fix'] }}</strong> email.
                                </p>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <h6 class="font-weight-bold">Tidak Ada Email yang Perlu Diupdate</h6>
                                <p class="mb-0">
                                    Semua email dalam file tidak memiliki status "SENT" di email log terakhir mereka.
                                    Tidak ada perubahan yang akan dilakukan.
                                </p>
                            </div>
                        @endif

                        @if($importData['status_counts']['already_failed'] > 0 || $importData['status_counts']['no_log'] > 0 || $importData['status_counts']['pending'] > 0)
                            <h6 class="font-weight-bold mt-4 mb-3">Email yang Akan Dilewati</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="30%">Email</th>
                                            <th width="15%">Email Status</th>
                                            <th width="15%">FormLink Status</th>
                                            <th width="35%">Alasan Dilewati</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $skipCount = 0;
                                        @endphp
                                        @foreach($importData['email_statuses'] as $status)
                                            @if(!$status['can_fix'] && $skipCount < 10)
                                                @php
                                                    $skipCount++;
                                                @endphp
                                                <tr>
                                                    <td>{{ $skipCount }}</td>
                                                    <td><code>{{ $status['email'] }}</code></td>
                                                    <td>
                                                        @if($status['latest_status'] === 'sent')
                                                            <span class="badge bg-success">SENT</span>
                                                        @elseif($status['latest_status'] === 'failed')
                                                            <span class="badge bg-danger">FAILED</span>
                                                        @elseif($status['latest_status'] === 'pending')
                                                            <span class="badge bg-warning">PENDING</span>
                                                        @elseif($status['latest_status'] === 'no_log')
                                                            <span class="badge bg-secondary">NO LOG</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ strtoupper($status['latest_status']) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(isset($status['form_link_status']))
                                                            @if($status['form_link_status'] === 'pending')
                                                                <span class="badge bg-success">PENDING</span>
                                                            @elseif($status['form_link_status'] === 'submitted')
                                                                <span class="badge bg-info">SUBMITTED</span>
                                                            @elseif($status['form_link_status'] === 'approved')
                                                                <span class="badge bg-primary">APPROVED</span>
                                                            @elseif($status['form_link_status'] === 'rejected')
                                                                <span class="badge bg-danger">REJECTED</span>
                                                            @elseif($status['form_link_status'] === 'no_form_link')
                                                                <span class="badge bg-secondary">NO LINK</span>
                                                            @else
                                                                <span class="badge bg-secondary">{{ strtoupper($status['form_link_status']) }}</span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-secondary">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $status['skip_reason'] ?? 'Unknown reason' }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if(($importData['total_emails'] - $importData['status_counts']['can_fix']) > 10)
                                <p class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Menampilkan 10 data pertama. Total yang akan dilewati:
                                    <strong>{{ $importData['total_emails'] - $importData['status_counts']['can_fix'] }}</strong> email.
                                </p>
                            @endif
                        @endif

                        <h6 class="font-weight-bold mt-4 mb-3">Konfirmasi</h6>
                        <form id="confirmForm" method="POST" action="{{ route('cms.email-log-fix.confirm') }}">
                            @csrf

                            <div class="form-check mb-4">
                                <input type="checkbox" class="form-check-input" id="confirmCheckbox" name="confirm" value="on" required>
                                <label class="form-check-label" for="confirmCheckbox">
                                    <strong>Saya memahami risiko dan siap untuk mengubah status email log dari SENT → FAILED</strong>
                                </label>
                            </div>

                            <div class="form-group">
                                @if($importData['status_counts']['can_fix'] > 0)
                                    <button type="submit" class="btn btn-danger" id="confirmBtn">
                                        <i class="bi bi-check-circle"></i> Proses Update ({{ $importData['status_counts']['can_fix'] }} emails)
                                    </button>
                                @endif
                                <a href="{{ route('cms.email-log-fix.form') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>

                        <div id="processingState" style="display: none;" class="text-center mt-4">
                            <div class="spinner-border text-danger" role="status">
                                <span class="sr-only">Processing...</span>
                            </div>
                            <p class="mt-2">Memproses update... mohon tunggu</p>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            Session expired. Please <a href="{{ route('cms.email-log-fix.form') }}">mulai ulang</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmForm = document.getElementById('confirmForm');
    const processingState = document.getElementById('processingState');
    const confirmBtn = document.getElementById('confirmBtn');

    if (confirmForm) {
        confirmForm.addEventListener('submit', function(e) {
            e.preventDefault();

            confirmBtn.disabled = true;
            processingState.style.display = 'block';

            const formData = new FormData(confirmForm);

            axios.post('{{ route("cms.email-log-fix.confirm") }}', formData)
                .then(response => {
                    if (response.data.success) {
                        window.location.href = response.data.redirect;
                    } else {
                        alert('Error: ' + (response.data.error || 'Unknown error'));
                        processingState.style.display = 'none';
                        confirmBtn.disabled = false;
                    }
                })
                .catch(error => {
                    const errMsg = error.response?.data?.error || 'Unknown error';
                    alert('Error: ' + errMsg);
                    processingState.style.display = 'none';
                    confirmBtn.disabled = false;
                });
        });
    }
});
</script>
@endpush
