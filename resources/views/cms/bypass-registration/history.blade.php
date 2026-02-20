@extends('layouts.cms')

@section('title', 'Import History')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Bypass Registration Import History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Import ID</th>
                                    <th>Date</th>
                                    <th>Admin</th>
                                    <th>Destination</th>
                                    <th>File</th>
                                    <th>Registrations</th>
                                    <th>Participants</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($imports as $import)
                                    <tr>
                                        <td>
                                            <strong>#{{ $import->id }}</strong>
                                        </td>
                                        <td>
                                            {{ $import->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td>
                                            {{ $import->admin->name ?? 'Unknown' }}
                                        </td>
                                        <td>
                                            {{ $import->destination->name }}
                                        </td>
                                        <td>
                                            <small>{{ $import->filename }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info text-bg-dark">{{ $import->total_registrations }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info text-bg-dark">{{ $import->total_participants }}</span>
                                        </td>
                                        <td>
                                            @if($import->status === 'completed')
                                                <span class="badge badge-success text-bg-success">Completed</span>
                                            @elseif($import->status === 'partial')
                                                <span class="badge badge-warning text-bg-warning">Partial</span>
                                            @else
                                                <span class="badge badge-danger text-bg-danger">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('cms.bypass-registrations.results', $import->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox"></i> No import history yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            Showing {{ $imports->firstItem() ?? 0 }} to {{ $imports->lastItem() ?? 0 }} of {{ $imports->total() }} results
                        </div>
                        <nav>
                            {{ $imports->links() }}
                        </nav>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <div class="m-2 d-flex justify-content-end gap-2">
                        <a href="{{ route('cms.registrations.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Registrations
                        </a>
                        <a href="{{ route('cms.bypass-registrations.form') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-upload"></i> New Import
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
