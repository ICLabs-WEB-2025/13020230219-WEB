@extends('layouts.app')

@section('content')
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
                @if($sharedByYou->isEmpty())
                <!-- If no documents have been shared -->
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="alert alert-info">You haven't shared any documents yet.</div>
                        <a href="{{ route('documents.index') }}" class="btn btn-primary btn-sm">Share Document</a>
                    </td>
                </tr>
                @else
                @foreach($sharedByYou as $sharedDocument)
                @foreach($sharedDocument->sharedWith as $share)
                <tr>
                    <td>{{ $sharedDocument->file_name }}</td>
                    <td>{{ $share->email }}</td>
                    <td>
                        <!-- Delete access form -->
                        <form action="{{ route('documents.unshare', ['document_id' => $sharedDocument->id, 'share_id' => $share->id]) }}" method="POST" class="d-inline delete-access-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete access for {{ $share->email }}?')">Delete Access</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @endforeach
                @endif
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
            <tbody>
                @if($sharedWithYou->isEmpty())
                <!-- If no documents have been shared with the user -->
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="alert alert-info">No documents have been shared with you yet.</div>
                        <a href="{{ route('documents.index') }}" class="btn btn-primary btn-sm">Share Document</a>
                    </td>
                </tr>
                @else
                <!-- If documents have been shared with the user -->
                @foreach($sharedWithYou as $sharedDocument)
                <tr>
                    <td>{{ $sharedDocument->file_name }}</td>
                    <td>{{ $sharedDocument->user->email ?? 'N/A' }}</td>
                    <td>
                        <!-- View document -->
                        <a href="{{ route('documents.view', $sharedDocument->id) }}" class="btn btn-info btn-sm" target="_blank">View</a>
                        <!-- Download document -->
                        <a href="{{ Storage::url($sharedDocument->file_path) }}" class="btn btn-success btn-sm" download>Download</a>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>

        </table>
    </div>

    <!-- Back to dashboard -->
    <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<!-- Modal for Sharing Document -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Share Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('documentShare.share', $sharedDocument->id) }}" method="POST" class="share-form">
                    @csrf
                    <div class="form-group">
                        <label for="email">Email to Share with:</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter email" required>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2">Share Document</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle share form submission using AJAX
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
                        // Close the modal
                        const modal = form.closest('.modal');
                        bootstrap.Modal.getInstance(modal).hide();

                        // Update the "Documents You Shared" table dynamically
                        const tableBody = document.getElementById('sharedByYouTable');
                        const updatedDocs = await fetch('{{ route("documentShare.index") }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(res => res.json());

                        // Repopulate table with updated data
                        tableBody.innerHTML = ''; // Clear the existing rows
                        updatedDocs.sharedByYou.forEach(doc => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td>${doc.file_name}</td>
                            <td>${doc.sharedWith ? doc.sharedWith.map(share => share.email).join(', ') : 'N/A'}</td>
                            <td>
                                <form action="/documents/unshare/${doc.id}" method="POST" class="delete-access-form d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete access?')">Delete Access</button>
                                </form>
                            </td>
                        `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        alert(result.message || 'Failed to share document.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while sharing the document.');
                }
            });
        });

        // Handle delete access form submission using AJAX
        document.querySelectorAll('.delete-access-form').forEach(form => {
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
                        // Update the table after deleting access
                        const tableBody = document.getElementById('sharedByYouTable');
                        const updatedDocs = await fetch('{{ route("documentShare.index") }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(res => res.json());

                        // Repopulate table with updated data
                        tableBody.innerHTML = ''; // Clear the existing rows
                        updatedDocs.sharedByYou.forEach(doc => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td>${doc.file_name}</td>
                            <td>${doc.sharedWith ? doc.sharedWith.map(share => share.email).join(', ') : 'N/A'}</td>
                            <td>
                                <form action="/documents/unshare/${doc.id}" method="POST" class="delete-access-form d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete access?')">Delete Access</button>
                                </form>
                            </td>
                        `;
                            tableBody.appendChild(row);
                        });
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