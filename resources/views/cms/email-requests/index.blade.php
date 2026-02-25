@extends('layouts.cms')

@section('title', 'Email Requests - CMS Admin')
@section('page-title', 'Email Requests')

@section('content')
<div class="container-fluid">
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cms.email-requests.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" placeholder="Search email..." value="{{ request('email') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Used</label>
                    <select name="used" class="form-select">
                        <option value="">All</option>
                        <option value="yes" {{ request('used') === 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('used') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Expired</label>
                    <select name="expired" class="form-select">
                        <option value="">All</option>
                        <option value="yes" {{ request('expired') === 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('expired') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('cms.email-requests.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Requests</p>
                            <h3 class="mb-0">{{ $emailRequests->total() }}</h3>
                        </div>
                        <i class="bi bi-envelope-fill fs-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Pending</p>
                            <h3 class="mb-0">{{ \App\Models\FormLink::where('status', 'pending')->count() }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Used Links</p>
                            <h3 class="mb-0">{{ \App\Models\FormLink::whereNotNull('used_at')->count() }}</h3>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Expired</p>
                            <h3 class="mb-0">{{ \App\Models\FormLink::where('expired_at', '<', now())->count() }}</h3>
                        </div>
                        <i class="bi bi-x-circle-fill fs-1 text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Email Request List</h5>
            <span class="badge bg-secondary">{{ $emailRequests->total() }} records</span>
        </div>
        <div class="card-body p-0">
            @if($emailRequests->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No email requests found</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Email Status</th>
                                <th>Token</th>
                                <th>Request Time</th>
                                <th>Used At</th>
                                <th>Expired At</th>
                                <th>Resend Count</th>
                                <th>Registration</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emailRequests as $request)
                                @php
                                    $latestEmailLog = $request->emailLogs->first();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $request->email }}</strong>
                                    </td>
                                    <td>
                                        @if($request->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($request->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($request->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @elseif($request->status === 'submitted')
                                            <span class="badge bg-info">Submitted</span>
                                        @endif

                                        @if($request->expired_at < now())
                                            <span class="badge bg-secondary">Expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($latestEmailLog)
                                            @if($latestEmailLog->status === 'sent')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Sent
                                                </span>
                                            @elseif($latestEmailLog->status === 'failed')
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Failed
                                                </span>
                                                @if($latestEmailLog->retry_count > 0)
                                                    <small class="text-muted d-block mt-1">Retry: {{ $latestEmailLog->retry_count }}</small>
                                                @endif
                                            @elseif($latestEmailLog->status === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-hourglass-split me-1"></i>Pending
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="small">{{ substr($request->token, 0, 12) }}...</code>
                                    </td>
                                    <td>
                                        <small>{{ $request->created_at->format('d M Y H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($request->used_at)
                                            <small class="text-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                {{ $request->used_at->format('d M Y H:i') }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="{{ $request->expired_at < now() ? 'text-danger' : 'text-muted' }}">
                                            {{ $request->expired_at->format('d M Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $request->resend_count }}</span>
                                    </td>
                                    <td>
                                        @if($request->registration)
                                            <a href="{{ route('cms.registrations.show', $request->registration) }}" class="text-primary">
                                                <i class="bi bi-file-text me-1"></i>View
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('cms.email-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($latestEmailLog && $latestEmailLog->status === 'failed')
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-warning resend-email-btn"
                                                    data-form-link-id="{{ $request->id }}"
                                                    data-email="{{ $request->email }}">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($emailRequests->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing {{ $emailRequests->firstItem() }} to {{ $emailRequests->lastItem() }} of {{ $emailRequests->total() }} entries
                    </div>
                    <div>
                        {{ $emailRequests->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle resend email button click
    document.querySelectorAll('.resend-email-btn').forEach(button => {
        button.addEventListener('click', function() {
            const formLinkId = this.dataset.formLinkId;
            const email = this.dataset.email;

            if (!confirm(`Apakah Anda yakin ingin mengirim ulang email ke ${email}?`)) {
                return;
            }

            // Disable button and show loading
            this.disabled = true;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i>';

            // Send request
            fetch(`{{ url('cms/email-requests') }}/${formLinkId}/resend`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert(data.message);
                    // Reload page to update status
                    window.location.reload();
                } else {
                    // Show error message
                    alert(data.message || 'Gagal mengirim ulang email');
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim ulang email');
                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalHtml;
            });
        });
    });
});
</script>
@endpush
