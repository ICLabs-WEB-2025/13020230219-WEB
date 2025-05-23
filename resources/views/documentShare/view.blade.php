@extends('layouts.app')

@section('content')
<style>
    /* Custom responsive styles */
    .container {
        padding: 1rem;
    }

    .card-header h3 {
        font-size: calc(1.5rem + 1vw); /* Responsive font size */
        margin-bottom: 0;
    }

    .card-header .btn-sm {
        padding: 0.5rem 1rem;
        font-size: calc(0.8rem + 0.2vw);
        margin-left: 0.5rem;
    }

    .card-body pre {
        font-size: calc(0.9rem + 0.2vw);
        max-height: 60vh; /* Limit height to prevent overflow */
        overflow-y: auto;
        white-space: pre-wrap;
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table td {
        font-size: calc(0.9rem + 0.2vw);
        vertical-align: middle;
    }

    .pdf-iframe {
        width: 100%;
        height: 60vh; /* Responsive height */
        border: none;
        display: block;
    }

    .pdf-canvas {
        width: 100% !important; /* Ensure canvas is responsive */
        height: auto !important;
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .card-header h3 {
            font-size: calc(1.2rem + 1vw);
        }

        .card-header .btn-sm {
            width: 100%;
            margin-left: 0;
            margin-bottom: 0.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .pdf-iframe {
            height: 50vh;
        }

        .table td {
            font-size: calc(0.8rem + 0.2vw);
        }

        .card-body pre {
            font-size: calc(0.8rem + 0.2vw);
            max-height: 50vh;
        }
    }

    @media (max-width: 576px) {
        .card-header h3 {
            font-size: calc(1rem + 1vw);
        }

        .pdf-iframe {
            height: 40vh;
        }

        .card {
            margin: 0 0.5rem;
        }
    }
</style>

<div class="container mt-5">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3>{{ $document->file_name }}</h3>
            <div class="action-buttons">
                <a href="{{ Storage::url($document->file_path) }}" class="btn btn-success btn-sm">Download</a>
                <a href="{{ route('documents.download.pdf', $document->id) }}" class="btn btn-warning btn-sm">Download as PDF</a>
                <a href="{{ route('documentShare.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            @if ($preview_type == 'text')
                <pre class="border p-3">{{ $content }}</pre>
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
                <iframe src="{{ $content }}" class="pdf-iframe"></iframe>
            @else
                <p class="text-danger">Pratinjau tidak tersedia untuk jenis file ini.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if ($preview_type == 'pdf')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';

        document.addEventListener('DOMContentLoaded', function() {
            const url = "{{ $content }}";
            const container = document.querySelector('.card-body');
            const iframe = container.querySelector('.pdf-iframe');
            const loadingTask = pdfjsLib.getDocument(url);

            loadingTask.promise.then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                    // Calculate scale based on container width
                    const containerWidth = container.offsetWidth;
                    const scale = Math.min(1.5, containerWidth / page.getViewport({ scale: 1 }).width);
                    const viewport = page.getViewport({ scale: scale });

                    // Create and configure canvas
                    const canvas = document.createElement('canvas');
                    canvas.className = 'pdf-canvas';
                    const context = canvas.getContext('2d');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    // Append canvas and hide iframe if canvas rendering is successful
                    container.appendChild(canvas);
                    iframe.style.display = 'none';

                    // Render PDF page
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);

                    // Adjust canvas size on window resize
                    window.addEventListener('resize', function() {
                        const newContainerWidth = container.offsetWidth;
                        const newScale = Math.min(1.5, newContainerWidth / page.getViewport({ scale: 1 }).width);
                        const newViewport = page.getViewport({ scale: newScale });
                        canvas.width = newViewport.width;
                        canvas.height = newViewport.height;
                        page.render({
                            canvasContext: context,
                            viewport: newViewport
                        });
                    });
                }).catch(function(error) {
                    console.error('Error rendering PDF:', error);
                    // Fallback to iframe if canvas fails
                    iframe.style.display = 'block';
                });
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
                iframe.style.display = 'block';
                container.innerHTML += '<p class="text-danger mt-2">Gagal memuat pratinjau PDF.</p>';
            });
        });
    </script>
@endif
@endsection