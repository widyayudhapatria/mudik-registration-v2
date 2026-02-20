@extends('layouts.cms')

@section('title', 'Import Results')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle"></i> Import Bypass Registrations - Results
                    </h5>
                </div>
                <div class="card-body">
                    @if($import->status === 'completed')
                        <div class="alert alert-success" role="alert">
                            <h6 class="font-weight-bold">✅ Import Successful!</h6>
                            <p class="mb-0">{{ $import->successful }} registrations imported successfully!</p>
                        </div>
                    @elseif($import->status === 'partial')
                        <div class="alert alert-warning" role="alert">
                            <h6 class="font-weight-bold">⚠️ Partial Import</h6>
                            <p class="mb-0">{{ $import->successful }} succeed, {{ $import->failed }} failed</p>
                        </div>
                    @else
                        <div class="alert alert-danger" role="alert">
                            <h6 class="font-weight-bold">❌ Import Failed</h6>
                            <p class="mb-0">{{ $import->notes }}</p>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Import ID</h6>
                                    <p class="card-text font-weight-bold">{{ $import->id }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Date</h6>
                                    <p class="card-text">{{ $import->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Destination</h6>
                                    <p class="card-text">{{ $import->destination->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Admin</h6>
                                    <p class="card-text">{{ $import->admin->name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title text-white">Successful</h6>
                                    <p class="card-text font-weight-bold" style="font-size: 1.5rem;">
                                        {{ $import->successful }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning">
                                <div class="card-body">
                                    <h6 class="card-title">Total Participants</h6>
                                    <p class="card-text font-weight-bold" style="font-size: 1.5rem;">
                                        {{ $import->total_participants }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card @if($import->failed > 0) bg-danger text-white @else bg-info text-white @endif">
                                <div class="card-body">
                                    <h6 class="card-title">Failed</h6>
                                    <p class="card-text font-weight-bold" style="font-size: 1.5rem;">
                                        {{ $import->failed }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($import->error_details && count($import->error_details) > 0)
                        <div class="mt-4">
                            <h6 class="font-weight-bold mb-3"><i class="fas fa-exclamation-triangle text-danger"></i> Error Details</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>Email</th>
                                            <th>Error Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($import->error_details as $error)
                                            <tr>
                                                <td><code>{{ $error['email'] ?? 'unknown' }}</code></td>
                                                <td>{{ $error['error'] ?? 'Unknown error' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h6 class="font-weight-bold mb-3">
                            <i class="fas fa-list"></i> Imported Registrations
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rep Name</th>
                                        <th>Email</th>
                                        <th>Participants</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($registrations as $reg)
                                        <tr>
                                            <td>{{ $reg->representative_name }}</td>
                                            <td><small>{{ $reg->formLink->email }}</small></td>
                                            <td>
                                                <span class="badge badge-primary text-bg-dark">{{ $reg->participants->count() }}</span>
                                            </td>
                                            <td>
                                                @if($reg->isApproved())
                                                    <span class="badge badge-success text-bg-success">Approved</span>
                                                @elseif($reg->isRejected())
                                                    <span class="badge badge-danger text-bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge badge-warning text-bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('cms.registrations.show', $reg->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No registrations</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('cms.bypass-registrations.form') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-upload"></i> Import More
                        </a>
                        <a href="{{ route('cms.bypass-registrations.history') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-clock-history"></i> View History
                        </a>
                        <a href="{{ route('cms.registrations.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-file-text"></i> All Registrations
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
