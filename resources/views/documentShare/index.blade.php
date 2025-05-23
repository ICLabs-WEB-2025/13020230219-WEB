@extends('layouts.app')

@section('content')
<style>
    /* Custom responsive styles */
    .container {
        padding: 1rem;
    }

    h3, h4 {
        font-size: calc(1.5rem + 1vw); /* Responsive font size */
    }

    h4 {
        font-size: calc(1.2rem + 0.8vw);
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table th, .table td {
        vertical-align: middle;
        font-size: calc(0.9rem + 0.2vw);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: calc(0.8rem + 0.2vw);
    }

    .modal-dialog {
        max-width: 90vw; /* Responsive modal width */
    }

    @media (max-width: 768px) {
        .table th, .table td {
            font-size: calc(0.8rem + 0.2vw);
        }

        .btn-sm {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .table thead {
            display: none; /* Hide headers on small screens */
        }

        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .table tbody td {
            display: block;
            text-align: left;
            padding: 0.5rem;
            position: relative;
        }

        .table tbody td::before {
            content: attr(data-label);
            font-weight: bold;
            display: inline-block;
            width: 40%;
            padding-right: 1rem;
        }

        .modal-dialog {
            max-width: 95vw;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-control {
            font-size: calc(0.9rem + 0.2vw);
        }
    }

    @media (max-width: 576px) {
        h3 {
            font-size: calc(1.2rem + 1vw);
        }

        h4 {
            font-size: calc(1rem + 0.8vw);
        }

        .alert {
            font-size: calc(0.8rem + 0.2vw);
        }
    }
</style>

<div class="container mt-5">
    <h3>Shared Documents</h3>

    <!-- Display success message if any -->
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Display error message if any -->
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <!-- Documents You Shared -->
    <h4 class="mt-4">Documents You Shared</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>File Name</th>
                    <th>Shared With</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="sharedByYouTable">
                @forelse($sharedByYou as $sharedDocument)
                @if($sharedDocument->sharedWith->isNotEmpty())
                @foreach($sharedDocument->sharedWith as $share)
                <tr>
                    <td data-label="File Name">{{ $sharedDocument->file_name }}</td>
                    <td data-label="Shared With">{{ $share->email }}</td>
                    <td data-label="Actions">
                        <!-- Delete access -->
                        <form action="{{ route('documents.unshare', ['document_id' => $sharedDocument->id, 'share_id' => $share->id]) }}" method="POST" class="delete-access-form d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete access for {{ $share->email }}?')">Delete Access</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td data-label="File Name">{{ $sharedDocument->file_name }}</td>
                    <td data-label="Shared With">N/A</td>
                    <td data-label="Actions">
                        <!-- Share document -->
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareModal{{ $sharedDocument->id }}">Share</button>

                        <!-- Modal for sharing document -->
                        <div class="modal fade" id="shareModal{{ $sharedDocument->id }}" tabindex="-1" aria-labelledby="shareModalLabel{{ $sharedDocument->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="shareModalLabel{{ $sharedDocument->id }}">Share Document: {{ $sharedDocument->file_name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Share form -->
                                        <form action="{{ route('documents.share', $sharedDocument->id) }}" method="POST" class="share-form">
                                            @csrf
                                            <div class="form-group">
                                                <label for="email{{ $sharedDocument->id }}">Email to Share with:</label>
                                                <input type="email" name="email" id="email{{ $sharedDocument->id }}" class="form-control" placeholder="Enter email" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-2">Share Document</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="alert alert-info">You haven't shared any documents yet.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Documents Shared with You -->
    <h4 class="mt-4">Documents Shared with You</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>File Name</th>
                    <th>Shared By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="sharedWithYouTable">
                @forelse($sharedWithYou as $sharedDocument)
                <tr>
                    <td data-label="File Name">{{ $sharedDocument->file_name }}</td>
                    <td data-label="Shared By">{{ $sharedDocument->user->email ?? 'N/A' }}</td>
                    <td data-label="Actions">
                        <!-- View document -->
                        <a href="{{ route('documents.view', $sharedDocument->id) }}" class="btn btn-info btn-sm" target="_blank">View</a>
                        <!-- Download document -->
                        <a href="{{ Storage::url($sharedDocument->file_path) }}" class="btn btn-success btn-sm" download>Download</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="alert alert-info">No documents have been shared with you yet.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Back to dashboard -->
    <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle form submission for sharing document
        document.querySelectorAll('.share-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                const actionUrl = form.action;

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();
                    if (result.success) {
                        // Close modal using Bootstrap 5
                        const modal = form.closest('.modal');
                        bootstrap.Modal.getInstance(modal).hide();

                        // Refresh "Documents You Shared" table
                        const tableBody = document.getElementById('sharedByYouTable');
                        tableBody.innerHTML = ''; // Clear table

                        // Fetch updated data
                        const updatedDocs = await fetch('{{ route("documentShare.index") }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(res => res.json());

                        // Repopulate table with updated data
                        if (updatedDocs.sharedByYou && updatedDocs.sharedByYou.length > 0) {
                            updatedDocs.sharedByYou.forEach(doc => {
                                if (doc.shared_with && doc.shared_with.length > 0) {
                                    doc.shared_with.forEach(share => {
                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                            <td data-label="File Name">${doc.file_name}</td>
                                            <td data-label="Shared With">${share.email}</td>
                                            <td data-label="Actions">
                                                <form action="/documents/unshare/${doc.id}/${share.id}" method="POST" class="delete-access-form d-inline">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete access for ${share.email}?')">Delete Access</button>
                                                </form>
                                            </td>
                                        `;
                                        tableBody.appendChild(row);
                                    });
                                } else {
                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                        <td data-label="File Name">${doc.file_name}</td>
                                        <td data-label="Shared With">N/A</td>
                                        <td data-label="Actions">
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareModal${doc.id}">Share</button>
                                        </td>
                                    `;
                                    tableBody.appendChild(row);
                                }
                            });
                        } else {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <div class="alert alert-info">You haven't shared any documents yet.</div>
                                    </td>
                                </tr>
                            `;
                        }

                        // Redirect to show notification
                        window.location.href = '{{ route("documents.index") }}';
                    } else {
                        alert(result.message || 'Failed to share document.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while sharing the document.');
                }
            });
        });

        // Handle delete access form submission
        document.querySelectorAll('.delete-access-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();
                    if (result.success) {
                        // Refresh "Documents You Shared" table
                        const tableBody = document.getElementById('sharedByYouTable');
                        tableBody.innerHTML = ''; // Clear table

                        // Fetch updated data
                        const updatedDocs = await fetch('{{ route("documentShare.index") }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(res => res.json());

                        // Repopulate table with updated data
                        if (updatedDocs.sharedByYou && updatedDocs.sharedByYou.length > 0) {
                            updatedDocs.sharedByYou.forEach(doc => {
                                if (doc.shared_with && doc.shared_with.length > 0) {
                                    doc.shared_with.forEach(share => {
                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                            <td data-label="File Name">${doc.file_name}</td>
                                            <td data-label="Shared With">${share.email}</td>
                                            <td data-label="Actions">
                                                <form action="/documents/unshare/${doc.id}/${share.id}" method="POST" class="delete-access-form d-inline">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete access for ${share.email}?')">Delete Access</button>
                                                </form>
                                            </td>
                                        `;
                                        tableBody.appendChild(row);
                                    });
                                } else {
                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                        <td data-label="File Name">${doc.file_name}</td>
                                        <td data-label="Shared With">N/A</td>
                                        <td data-label="Actions">
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shareModal${doc.id}">Share</button>
                                        </td>
                                    `;
                                    tableBody.appendChild(row);
                                }
                            });
                        } else {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <div class="alert alert-info">You haven't shared any documents yet.</div>
                                    </td>
                                </tr>
                            `;
                        }

                        alert(result.message || 'Access deleted successfully!');
                    } else {
                        alert(result.message || 'Failed to delete access.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting access.');
                }
            });
        });
    });
</script>
@endpush