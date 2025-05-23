@extends('layouts.app')

@section('content')

<div class="container mr-3 mt-4 mt-md-5 px-3 px-md-4" style="margin-right: 5pc;">
    <div class="card">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 gap-md-0 p-3">
            <h3 class="mb-0 text-center text-md-start fs-4">{{ $document->file_name }}</h3>
            <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-2">
                <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-primary btn-sm w-100 w-md-auto">Edit</a>
                <a href="{{ Storage::url($document->file_path) }}" class="btn btn-success btn-sm w-100 w-md-auto">Download</a>
                <a href="{{ route('documents.download.pdf', $document->id) }}" class="btn btn-warning btn-sm w-100 w-md-auto">Download as PDF</a>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary btn-sm w-100 w-md-auto">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            @if ($preview_type == 'text')
                <pre class="border p-3" style="white-space: pre-wrap; background-color: #f8f9fa; min-height: 200px; max-height: 80vh; overflow-y: auto; border-radius: 0.25rem;">{{ $content }}</pre>
            @elseif ($preview_type == 'table')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            @foreach ($content as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td>{{ $cell ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif ($preview_type == 'pdf')
                <iframe src="{{ $content }}" class="w-100" style="height: 60vh; min-height: 400px; border: none; border-radius: 0.25rem;"></iframe>
            @else
                <p class="text-danger text-center">Pratinjau tidak tersedia untuk jenis file ini.</p>
            @endif
        </div>
    </div>
</div>

<style>
/* Ensure consistency with layouts.app */
@media (max-width: 576px) {
    h3 {
        font-size: 1.25rem;
    }
    .card-header {
        padding: 1rem;
    }
    .table {
        font-size: 0.875rem;
    }
}
@media (min-width: 768px) {
    .btn-sm {
        padding: 0.25rem 0.75rem;
    }
}
.card {
    max-width: 100%;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>

@section('scripts')
@if ($preview_type == 'pdf')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
        const url = "{{ $content }}";
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(function(pdf) {
            pdf.getPage(1).then(function(page) {
                const scale = window.innerWidth < 576 ? 1 : 1.5;
                const viewport = page.getViewport({ scale: scale });
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                canvas.style.width = '100%';
                canvas.style.height = 'auto';
                canvas.style.borderRadius = '0.25rem';
                document.querySelector('.card-body').appendChild(canvas);
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                page.render(renderContext);
            });
        });
    </script>
@endif
@endsection