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
                                        <a href="{{ route('cms.email-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
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