@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold mb-4 text-dark">Your Documents</h3>

    <!-- Success and Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Documents Table -->
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th scope="col">File Name</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    <tr class="transition-all">
                        <td>{{ $document->file_name }}</td>
                        <td class="d-flex flex-wrap gap-2">
                            <!-- View Button -->
                            <a href="{{ route('documents.view', $document->id) }}" class="btn btn-info btn-sm" target="_blank">
                                <i class="fas fa-eye me-1"></i> View
                            </a>

                            <!-- Download Button -->
                            <a href="{{ Storage::url($document->file_path) }}" class="btn btn-success btn-sm" download>
                                <i class="fas fa-download me-1"></i> Download
                            </a>

                            <!-- Update Button -->
                            <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i> Update
                            </a>

                            <!-- Delete Button -->
                            <form action="{{ route('documents.delete', $document->id) }}" method="POST" class="d-inline-block delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </form>

                            <!-- Share Button -->
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareModal{{ $document->id }}">
                                <i class="fas fa-share-alt me-1"></i> Share
                            </button>

                            <!-- Share Modal -->
                            <div class="modal fade" id="shareModal{{ $document->id }}" tabindex="-1" aria-labelledby="shareModalLabel{{ $document->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="shareModalLabel{{ $document->id }}">Share Document: {{ $document->file_name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('documents.share', $document->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="email{{ $document->id }}" class="form-label">Email to Share with:</label>
                                                    <input type="email" name="email" id="email{{ $document->id }}" class="form-control" placeholder="Enter email" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Share Document</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center"> <!-- Updated colspan to 3 -->
                            <div class="alert alert-info mb-0">No files added yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Upload Button -->
    <a href="{{ route('documents.upload') }}" class="btn btn-primary mt-4">
        <i class="fas fa-upload me-1"></i> Upload New Document
    </a>
</div>

<style>
    /* Custom styles for documents page */
    .table-responsive {
        max-height: 500px; /* Limit table height for scrollability */
        overflow-y: auto;
        border-radius: 0.5rem;
    }

    .table th, .table td {
        padding: 0.75rem;
        white-space: nowrap; /* Prevent text wrapping in small screens */
    }

    .btn-sm {
        padding: 0.25rem 0.75rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .alert {
        border-radius: 0.5rem;
    }

    @media (max-width: 576px) {
        h3 {
            font-size: 1.5rem; /* Smaller heading on mobile */
        }

        .table th, .table td {
            font-size: 0.875rem; /* Smaller text for table on mobile */
        }

        .btn-sm {
            font-size: 0.75rem; /* Smaller buttons on mobile */
            padding: 0.2rem 0.5rem;
        }

        .d-flex.gap-2 {
            flex-direction: column; /* Stack buttons vertically on mobile */
            align-items: flex-start;
        }
    }
</style>

@push('scripts')
<script>
    // Handle delete confirmation
    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!confirm('Are you sure you want to delete this document?')) {
                event.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection