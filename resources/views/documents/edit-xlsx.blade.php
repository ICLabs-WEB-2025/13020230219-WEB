@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Edit Excel File</h3>
    
    <form action="{{ route('documents.update', $document->id) }}" method="POST">
        @csrf

        <!-- Input untuk mengubah nama file -->
        <div class="mb-3">
            <label for="file_name" class="form-label">File Name</label>
            <input type="text" class="form-control" id="file_name" name="file_name" value="{{ $document->file_name }}" required>
        </div>

        <!-- Textarea untuk mengedit konten file -->
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="10">
                {{ implode("\n", array_map(fn($row) => implode(", ", $row), $content)) }}
            </textarea>
        </div>
        
        <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
    </form>
</div>
@endsection
