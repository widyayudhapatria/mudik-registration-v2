@extends('layouts.cms')

@section('title', 'Confirm Import')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle"></i> Confirm Import Bypass Registrations
                    </h5>
                </div>
                <div class="card-body">
                    @if($importData)
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">Import Summary</h6>
                            <ul class="mb-0">
                                <li><strong>Destination:</strong> {{ $importData['destination_name'] }}</li>
                                <li><strong>File:</strong> {{ $importData['filename'] }}</li>
                                <li><strong>Registrations:</strong> {{ $importData['total_registrations'] }}</li>
                                <li><strong>Total Peserta:</strong> {{ $importData['total_participants'] }}</li>
                                <li class="mt-2 border-top pt-2">
                                    <strong>Breakdown Peserta:</strong>
                                    <ul class="list-unstyled mb-0 pl-3">
                                        <li><strong>Dewasa (≥4 tahun):</strong> {{ $importData['total_participants_for_quota'] ?? 0 }}</li>
                                        <li><strong>Anak (<4 tahun):</strong> {{ $importData['total_under_4'] ?? 0 }}</li>
                                    </ul>
                                </li>
                                <li class="mt-2 border-top pt-2"><strong style="color: #e74c3c;">⚠️ Quota hanya dihitung dari peserta dewasa (≥4 tahun)</strong></li>
                            </ul>
                        </div>

                        <h6 class="font-weight-bold mt-4 mb-3">Preview Data</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-4">
                                <thead class="table-light">
                                    <tr>
                                        <th>Representative</th>
                                        <th>Email</th>
                                        <th>NIK</th>
                                        <th>Total</th>
                                        <th>Dewasa (≥4th)</th>
                                        <th>Anak (<4th)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($importData['registrations'] as $reg)
                                        <tr>
                                            <td>{{ $reg['representative_name'] }}</td>
                                            <td>{{ $reg['representative_email'] }}</td>
                                            <td>{{ $reg['representative_nik'] }}</td>
                                            <td>{{ count($reg['participants']) }}</td>
                                            <td><strong>{{ $reg['adult_count'] ?? 0 }}</strong></td>
                                            <td><span class="badge badge-info text-bg-secondary">{{ $reg['under_4_count'] ?? 0 }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(!empty($importData['warnings']))
                            <div class="alert alert-warning">
                                <h6 class="alert-heading font-weight-bold">⚠️ Warnings</h6>
                                <ul class="mb-0">
                                    @foreach($importData['warnings'] as $warning)
                                        <li>{{ $warning['message'] ?? json_encode($warning) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h6 class="font-weight-bold mt-0 mb-2">Confirmation</h6>
                        <form id="confirmForm" method="POST" action="{{ route('cms.bypass-registrations.confirm') }}">
                            @csrf

                            <div class="form-check mb-4">
                                <input type="checkbox" class="form-check-input" id="confirmCheckbox" name="confirm" value="on" required>
                                <label class="form-check-label" for="confirmCheckbox">
                                    Saya telah membaca & memahami data di atas dan siap untuk mengimport
                                </label>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success" id="confirmBtn">
                                    <i class="bi bi-file-arrow-up"></i> Confirm Import
                                </button>
                                <a href="{{ route('cms.bypass-registrations.form') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>

                        <div id="processingState" style="display: none;" class="text-center mt-4">
                            <div class="spinner-border text-success" role="status">
                                <span class="sr-only">Processing...</span>
                            </div>
                            <p class="mt-2">Processing import... please wait</p>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            Session expired. Please <a href="{{ route('cms.bypass-registrations.form') }}">start over</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('confirmForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const processingState = document.getElementById('processingState');
    const confirmBtn = document.getElementById('confirmBtn');
    const form = this;

    confirmBtn.disabled = true;
    processingState.style.display = 'block';

    fetch('{{ route("cms.bypass-registrations.confirm") }}', {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw {response: data};
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
            processingState.style.display = 'none';
            confirmBtn.disabled = false;
        }
    })
    .catch(error => {
        processingState.style.display = 'none';
        confirmBtn.disabled = false;

        if (error.response) {
            alert('Error: ' + (error.response.error || 'Unknown error'));
        } else {
            alert('Error: ' + (error.message || 'Unknown error'));
        }
    });
});
</script>
@endpush
@endsection
