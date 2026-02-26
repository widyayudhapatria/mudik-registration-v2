@extends('layouts.cms')

@section('title', 'Email Log Fix Results')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle"></i> Email Log Fix - Process Completed
                    </h5>
                </div>
                <div class="card-body">
                    @if($results)
                        <div class="alert alert-success" role="alert">
                            <h6 class="font-weight-bold"><i class="fas fa-check-circle"></i> Proses Selesai</h6>
                            <p class="mb-0">
                                Email log telah berhasil diproses. Berikut adalah ringkasan hasil:
                            </p>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary mb-0">{{ $results['total'] }}</h3>
                                        <p class="text-muted mb-0">Total Emails</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h3 class="text-success mb-0">{{ $results['updated'] }}</h3>
                                        <p class="text-muted mb-0">Updated</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h3 class="text-warning mb-0">{{ $results['skipped'] }}</h3>
                                        <p class="text-muted mb-0">Skipped</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <h3 class="text-danger mb-0">{{ $results['errors'] }}</h3>
                                        <p class="text-muted mb-0">Errors</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Section -->
                        <h6 class="font-weight-bold mt-4 mb-3">Detail Hasil</h6>

                        <!-- Tabs for different status -->
                        <ul class="nav nav-tabs" id="resultTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="updated-tab" data-bs-toggle="tab" data-bs-target="#updated" type="button" role="tab">
                                    <i class="fas fa-check-circle text-success"></i> Updated ({{ $results['updated'] }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="skipped-tab" data-bs-toggle="tab" data-bs-target="#skipped" type="button" role="tab">
                                    <i class="fas fa-info-circle text-warning"></i> Skipped ({{ $results['skipped'] }})
                                </button>
                            </li>
                            @if($results['errors'] > 0)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="errors-tab" data-bs-toggle="tab" data-bs-target="#errors" type="button" role="tab">
                                        <i class="fas fa-exclamation-triangle text-danger"></i> Errors ({{ $results['errors'] }})
                                    </button>
                                </li>
                            @endif
                        </ul>

                        <div class="tab-content mt-3" id="resultTabsContent">
                            <!-- Updated Tab -->
                            <div class="tab-pane fade show active" id="updated" role="tabpanel">
                                @php
                                    $updatedItems = array_filter($results['details'], fn($item) => $item['status'] === 'updated');
                                @endphp

                                @if(count($updatedItems) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped table-bordered">
                                            <thead class="table-success">
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="30%">Email</th>
                                                    <th width="15%">Email Log ID</th>
                                                    <th width="25%">Original Sent At</th>
                                                    <th width="25%">New Failed At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($updatedItems as $index => $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td><code>{{ $item['email'] }}</code></td>
                                                        <td>{{ $item['email_log_id'] }}</td>
                                                        <td>{{ $item['original_sent_at'] }}</td>
                                                        <td>{{ $item['new_failed_at'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        Tidak ada email yang diupdate.
                                    </div>
                                @endif
                            </div>

                            <!-- Skipped Tab -->
                            <div class="tab-pane fade" id="skipped" role="tabpanel">
                                @php
                                    $skippedItems = array_filter($results['details'], fn($item) => $item['status'] === 'skipped');
                                @endphp

                                @if(count($skippedItems) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped table-bordered">
                                            <thead class="table-warning">
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="35%">Email</th>
                                                    <th width="60%">Reason</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($skippedItems as $index => $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td><code>{{ $item['email'] }}</code></td>
                                                        <td>{{ $item['reason'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        Tidak ada email yang dilewati.
                                    </div>
                                @endif
                            </div>

                            <!-- Errors Tab -->
                            @if($results['errors'] > 0)
                                <div class="tab-pane fade" id="errors" role="tabpanel">
                                    @php
                                        $errorItems = array_filter($results['details'], fn($item) => $item['status'] === 'error');
                                    @endphp

                                    @if(count($errorItems) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped table-bordered">
                                                <thead class="table-danger">
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th width="35%">Email</th>
                                                        <th width="60%">Error Reason</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($errorItems as $index => $item)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td><code>{{ $item['email'] }}</code></td>
                                                            <td class="text-danger">{{ $item['reason'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            Tidak ada error.
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="mt-4">
                            <a href="{{ route('cms.email-log-fix.form') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Process Another File
                            </a>
                            <a href="{{ route('cms.dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-house"></i> Back to Dashboard
                            </a>
                        </div>

                    @else
                        <div class="alert alert-danger">
                            No results found. Please <a href="{{ route('cms.email-log-fix.form') }}">start a new process</a>.
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
    // Initialize Bootstrap tabs
    const triggerTabList = document.querySelectorAll('#resultTabs button');
    triggerTabList.forEach(triggerEl => {
        const tabTrigger = new bootstrap.Tab(triggerEl);

        triggerEl.addEventListener('click', event => {
            event.preventDefault();
            tabTrigger.show();
        });
    });
});
</script>
@endpush
