@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="display-4 text-center mb-5 fw-bold text-dark">Dashboard</h1>
    <div class="row g-4 justify-content-center">
        <!-- Card untuk menampilkan jumlah dokumen yang dibagikan (milik pengguna) -->
        <div class="col-12 col-md-6 col-lg-4 d-flex justify-content-center">
            <div class="card border-0 shadow-sm h-100 transition-all" style="width: 350px;">
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                    <i class="fas fa-file-alt fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title fw-semibold mb-2">Dokumen Saya</h5>
                    <p class="card-text display-5 fw-bold text-dark mb-0">
                        {{ number_format($documentsCount, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card untuk menampilkan jumlah share (dokumen yang dibagikan oleh pengguna) -->
        <div class="col-12 col-md-6 col-lg-4 d-flex justify-content-center">
            <div class="card border-0 shadow-sm h-100 transition-all">
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                    <i class="fas fa-share-alt fa-3x mb-3 text-success"></i>
                    <h5 class="card-title fw-semibold mb-2">Dokumen yang Saya Bagikan</h5>
                    <p class="card-text display-5 fw-bold text-dark mb-0">
                        {{ number_format($sharedWithYouCount ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card untuk menampilkan jumlah dokumen yang dibagikan kepada pengguna -->
        <div class="col-12 col-md-6 col-lg-4 d-flex justify-content-center">
            <div class="card border-0 shadow-sm h-100 transition-all">
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                    <i class="fas fa-inbox fa-3x mb-3 text-info"></i>
                    <h5 class="card-title fw-semibold mb-2">Dokumen Dibagikan kepada Saya</h5>
                    <p class="card-text display-5 fw-bold text-dark mb-0">
                        {{ number_format($sharedByYouCount ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card untuk menampilkan aktivitas terbaru -->
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100 transition-all">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4">Aktivitas Terbaru</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentActivities as $activity)
                                    <tr class="transition-all">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $activity->created_at->diffForHumans() }}</td>
                                        <td>{{ $activity->activity }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada aktivitas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styles for dashboard */
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 12px; /* Rounded corners for modern look */
        min-height: 200px; /* Ensure consistent card height */
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .card-body {
        padding: 2rem; /* Consistent padding for all cards */
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .table-responsive {
        max-height: 300px; /* Limit table height for scrollability */
        overflow-y: auto;
    }

    .table th, .table td {
        padding: 0.75rem;
        white-space: nowrap; /* Prevent text wrapping in small screens */
    }

    .fa-file-alt {
        color: #007bff; /* Primary color for document icon */
    }

    .fa-share-alt {
        color: #28a745; /* Success color for share icon */
    }

    .fa-inbox {
        color: #17a2b8; /* Info color for received documents icon */
    }

    @media (max-width: 576px) {
        .display-4 {
            font-size: 2rem; /* Smaller heading on mobile */
        }

        .display-5 {
            font-size: 1.75rem; /* Adjusted count text size on mobile */
        }

        .table th, .table td {
            font-size: 0.875rem; /* Smaller text for table on mobile */
        }

        .card-body {
            padding: 1.5rem; /* Slightly reduced padding on mobile */
        }

        .card {
            min-height: 150px; /* Adjusted height for mobile */
        }
    }
</style>
@endsection