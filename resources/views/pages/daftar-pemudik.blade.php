@extends('layouts.app-v2')
@section('title', 'Daftar Pemudik - ' . config('mudik.website.name'))

@push('styles')
    <style>
        html {
            scroll-behavior: smooth;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .traveler-table {
            font-size: 0.95rem;
        }

        .traveler-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .traveler-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .search-card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .result-info {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .support-info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin-top: 1rem;
            margin-bottom: 2rem;
        }

        .badge-count {
            background-color: #0d6efd;
            color: white;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .traveler-table {
                font-size: 0.85rem;
            }

            .traveler-table th,
            .traveler-table td {
                padding: 0.5rem 0.3rem;
            }
        }
    </style>
@endpush

@section('header')
    @include('components.header', [
        'menuItems' => [
            ['label' => 'Beranda', 'href' => url('/public')],
            ['label' => 'Daftar Pemudik', 'href' => '#daftar-pemudik', 'active' => true],
        ],
    ])
@endsection

@section('content')
    <!-- Hero Section -->
    @include('components.hero', [
        'sectionId' => 'daftar-pemudik',
        'slides' => [
            [
                'title' => 'Daftar Pemudik',
                'subtitle' =>
                    'Lihat daftar peserta yang telah <b>disetujui</b> untuk mengikuti program mudik gratis. Cari berdasarkan <b>tujuan</b> atau <b>nama perwakilan keluarga</b>.',
                'buttonText' => '',
                'buttonLink' => '',
            ],
        ],
    ])

    <!-- Daftar Pemudik Section -->
    <section class="section" id="traveler-list">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="title text-center mb-5">
                        <h2>
                            Daftar
                            <b>Pemudik yang Disetujui</b>
                        </h2>
                        <span class="title-border"><i class="mdi mdi-set-none"></i></span>
                    </div>

                    <!-- Search Card -->
                    <div class="card search-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="mdi mdi-magnify"></i> Pencarian Pemudik
                            </h5>
                            <form method="GET" action="{{ route('public.daftar-pemudik') }}" id="searchForm">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label for="destination_id" class="form-label">Tujuan</label>
                                        <select name="destination_id" id="destination_id" class="form-select">
                                            <option value="">-- Semua Tujuan --</option>
                                            @foreach ($destinations as $destination)
                                                <option value="{{ $destination->id }}"
                                                    {{ $selectedDestination == $destination->id ? 'selected' : '' }}>
                                                    {{ $destination->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="search_name" class="form-label">Nama Perwakilan</label>
                                        <input type="text" name="search_name" id="search_name" class="form-control"
                                            placeholder="Cari nama perwakilan..." value="{{ $searchName ?? '' }}"
                                            maxlength="100">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center">
                                        <button type="submit" class="btn btn-custom w-100">
                                            <i class="mdi mdi-magnify"></i> Cari
                                        </button>
                                    </div>
                                </div>
                                @if ($selectedDestination || $searchName)
                                    <div class="mt-lg-0 mt-3">
                                        <a href="{{ route('public.daftar-pemudik') }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="mdi mdi-refresh"></i> Reset Pencarian
                                        </a>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Support Info -->
                    <div class="support-info">
                        <h6 class="mb-2">
                            <i class="mdi mdi-alert-circle-outline"></i>
                            <strong>Informasi Penting</strong>
                        </h6>
                        <p class="mb-0">
                            Jika nama Anda tercantum dalam daftar di bawah tetapi <strong>belum menerima QR Code
                                keberangkatan</strong>
                            melalui email, silakan hubungi tim support kami di
                            <strong>{{ config('mudik.support.phone', '0812-3456-7890') }}</strong>
                            atau Instagram
                            <strong>{{ config('mudik.support.email', 'support@mudikgratis.com') }}</strong>
                            untuk bantuan lebih lanjut.
                        </p>
                    </div>

                    <!-- Result Info -->
                    <div class="result-info mb-1">
                        <i class="mdi mdi-information-outline"></i>
                        Menampilkan <strong>{{ number_format($travelers->count()) }}</strong> pemudik
                        @if ($selectedDestination || $searchName)
                            yang sesuai dengan kriteria pencarian
                        @else
                            yang telah disetujui
                        @endif
                    </div>

                    <!-- Travelers Table -->
                    <div class="table-responsive">
                        @if ($travelers->count() > 0)
                            <table class="table table-striped table-hover traveler-table">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="20%">Tujuan</th>
                                        <th width="25%">Nama Perwakilan</th>
                                        <th width="25%">Email</th>
                                        <th width="15%">No. KK</th>
                                        <th width="10%" class="text-center">Jumlah Pemudik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($travelers as $index => $traveler)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <i class="mdi mdi-map-marker text-primary"></i>
                                                {{ $traveler->destination->name ?? '-' }}
                                            </td>
                                            <td>{{ $traveler->representative_name }}</td>
                                            <td>
                                                <code style="font-size: 0.85rem;">{{ $traveler->masked_email }}</code>
                                            </td>
                                            <td>
                                                <code style="font-size: 0.85rem;">{{ $traveler->masked_kk }}</code>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-count">{{ $traveler->participants_count }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-danger text-center">
                                <i class="mdi mdi-information-outline fs-4"></i>
                                <p class="mb-0 mt-2">
                                    @if ($selectedDestination || $searchName)
                                        <strong>Tidak ada pemudik yang sesuai dengan kriteria pencarian.</strong>
                                    @else
                                        <strong>No available data.</strong>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Check if page loaded with search parameters (means user just searched)
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const hasSearchParams = urlParams.has('destination_id') || urlParams.has('search_name');

            if (hasSearchParams) {
                // Wait for page to fully load, then scroll to results
                setTimeout(function() {
                    const resultsSection = document.getElementById('traveler-list');
                    if (resultsSection) {
                        resultsSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }, 100); // Small delay to ensure page is ready
            }
        })();

        // Auto-submit form when destination is changed (optional - currently disabled)
        // document.getElementById('destination_id').addEventListener('change', function() {
        //     document.getElementById('searchForm').submit();
        // });

        // CLIENT-SIDE XSS PREVENTION for search input
        // Removes dangerous characters in real-time as user types
        document.getElementById('search_name').addEventListener('input', function(e) {
            // Remove HTML tags and dangerous special characters: < > " '
            this.value = this.value.replace(/[<>\"']/g, '');

            // Only allow letters and spaces (matching backend validation)
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        });

        // Prevent form submission with malicious content
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            var searchInput = document.getElementById('search_name').value.trim();

            // Final validation before submit
            if (searchInput.length > 0 && !/^[A-Za-z\s]+$/.test(searchInput)) {
                e.preventDefault();
                alert('Nama hanya boleh berisi huruf dan spasi');
                return false;
            }
        });
    </script>
@endpush
