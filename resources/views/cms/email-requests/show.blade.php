@extends('layouts.cms')

@section('title', 'Email Request Detail - CMS Admin')
@section('page-title', 'Email Request Detail')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('cms.email-requests.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="row">
        <!-- Email Request Info -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Request Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="200">Email</th>
                                <td>
                                    <strong>{{ $formLink->email }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($formLink->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($formLink->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($formLink->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif($formLink->status === 'submitted')
                                        <span class="badge bg-info">Submitted</span>
                                    @endif
                                    
                                    @if($formLink->expired_at < now())
                                        <span class="badge bg-secondary ms-2">Expired</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Token</th>
                                <td>
                                    <code>{{ $formLink->token }}</code>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $formLink->token }}')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th>Form Link</th>
                                <td>
                                    <a href="{{ $formLink->generated_link }}" target="_blank" class="text-break">
                                        {{ $formLink->generated_link }}
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $formLink->generated_link }}')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th>Request Time</th>
                                <td>{{ $formLink->created_at->format('d F Y, H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Used At</th>
                                <td>
                                    @if($formLink->used_at)
                                        <span class="text-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ $formLink->used_at->format('d F Y, H:i:s') }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not used yet</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Expired At</th>
                                <td class="{{ $formLink->expired_at < now() ? 'text-danger' : '' }}">
                                    {{ $formLink->expired_at->format('d F Y, H:i:s') }}
                                    @if($formLink->expired_at < now())
                                        <i class="bi bi-exclamation-triangle-fill ms-2"></i>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Resend Count</th>
                                <td>
                                    <span class="badge bg-secondary">{{ $formLink->resend_count }} times</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Registration Info -->
            @if($formLink->registration)
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Registration Information</h5>
                        <a href="{{ route('cms.registrations.show', $formLink->registration) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-arrow-right-circle me-1"></i>View Full Detail
                        </a>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="200">Registration ID</th>
                                    <td><code>{{ $formLink->registration->id }}</code></td>
                                </tr>
                                <tr>
                                    <th>Representative Name</th>
                                    <td><strong>{{ $formLink->registration->representative_name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $formLink->registration->representative_phone }}</td>
                                </tr>
                                <tr>
                                    <th>Total Participants</th>
                                    <td>
                                        <span class="badge bg-info">{{ $formLink->registration->participants->count() }} people</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Registration Status</th>
                                    <td>
                                        @if($formLink->registration->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($formLink->registration->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($formLink->registration->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Submitted At</th>
                                    <td>{{ $formLink->registration->created_at->format('d F Y, H:i:s') }}</td>
                                </tr>
                                @if($formLink->registration->approved_at)
                                    <tr>
                                        <th>Approved At</th>
                                        <td>
                                            {{ $formLink->registration->approved_at->format('d F Y, H:i:s') }}
                                            @if($formLink->registration->approvedBy)
                                                <br><small class="text-muted">by {{ $formLink->registration->approvedBy->name }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                @if($formLink->registration->rejected_at)
                                    <tr>
                                        <th>Rejected At</th>
                                        <td>
                                            {{ $formLink->registration->rejected_at->format('d F Y, H:i:s') }}
                                            @if($formLink->registration->rejectedBy)
                                                <br><small class="text-muted">by {{ $formLink->registration->rejectedBy->name }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">No registration submitted yet</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Timeline & Stats -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <small class="text-muted">Days Since Request</small>
                            @php
                                $daysSince = floor($formLink->created_at->diffInDays(now()));
                            @endphp
                            <div class="h4 mb-0">{{ $daysSince }} {{ $daysSince == 1 ? 'day' : 'days' }}</div>
                        </div>
                        <i class="bi bi-calendar-event fs-2 text-primary opacity-25"></i>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <small class="text-muted">Days Until Expiry</small>
                            <div class="h4 mb-0 {{ $formLink->expired_at < now() ? 'text-danger' : '' }}">
                                @if($formLink->expired_at < now())
                                    Expired
                                @else
                                    @php
                                        $daysUntil = floor(now()->diffInDays($formLink->expired_at));
                                    @endphp
                                    {{ $daysUntil }} {{ $daysUntil == 1 ? 'day' : 'days' }}
                                @endif
                            </div>
                        </div>
                        <i class="bi bi-hourglass-split fs-2 text-warning opacity-25"></i>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Link Used</small>
                            <div class="h4 mb-0">
                                @if($formLink->used_at)
                                    <span class="text-success">Yes</span>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </div>
                        </div>
                        <i class="bi bi-link-45deg fs-2 text-info opacity-25"></i>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Created -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $formLink->created_at->format('d M Y, H:i') }}</small>
                                <p class="mb-0">Email request created</p>
                            </div>
                        </div>

                        <!-- Used -->
                        @if($formLink->used_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $formLink->used_at->format('d M Y, H:i') }}</small>
                                    <p class="mb-0">Link accessed</p>
                                </div>
                            </div>
                        @endif

                        <!-- Registration -->
                        @if($formLink->registration)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $formLink->registration->created_at->format('d M Y, H:i') }}</small>
                                    <p class="mb-0">Registration submitted</p>
                                </div>
                            </div>

                            @if($formLink->registration->approved_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <small class="text-muted">{{ $formLink->registration->approved_at->format('d M Y, H:i') }}</small>
                                        <p class="mb-0">Registration approved</p>
                                    </div>
                                </div>
                            @endif

                            @if($formLink->registration->rejected_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-danger"></div>
                                    <div class="timeline-content">
                                        <small class="text-muted">{{ $formLink->registration->rejected_at->format('d M Y, H:i') }}</small>
                                        <p class="mb-0">Registration rejected</p>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Expired -->
                        @if($formLink->expired_at < now())
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $formLink->expired_at->format('d M Y, H:i') }}</small>
                                    <p class="mb-0">Link expired</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-marker {
        position: absolute;
        left: -26px;
        top: 4px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px currentColor;
    }
    
    .timeline-content {
        padding-left: 10px;
    }
    
    .timeline-content small {
        display: block;
        margin-bottom: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
</script>
@endpush