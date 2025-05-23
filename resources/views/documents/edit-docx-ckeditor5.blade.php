@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Edit Document - {{ $document->file_name }}</h3>

    <!-- Form untuk mengedit nama file dan konten -->
    <form action="{{ route('documents.update', $document->id) }}" method="POST">
        @csrf
        <!-- Input untuk mengganti nama file -->
        <div class="mb-3">
            <label for="file_name" class="form-label">File Name</label>
            <input type="text" class="form-control" id="file_name" name="file_name" value="{{ $document->file_name }}" required>
        </div>

        <!-- TinyMCE Container -->
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="15">{{ $content }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
        <a href="{{ route('documents.index', $document->id) }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>

<!-- TinyMCE JS CDN -->
<!-- Place the first <script> tag in your HTML's <head> -->
<!-- <script src="https://cdn.tiny.cloud/1/j387ojbnc6a7dxfycm71a9x8qz80cr1l6fzl0o9go9yywqk8/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

<!-- Place the following <script> and <textarea> tags your HTML's <body>
<script>
    tinymce.init({
        selector: 'textarea',
        plugins: [
            // Core editing features
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
            // Your account includes a free trial of TinyMCE premium features
            // Try the most popular premium features until Jun 1, 2025:
            'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'editimage', 'advtemplate', 'ai', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown', 'importword', 'exportword', 'exportpdf'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        tinycomments_mode: 'embedded',
        tinycomments_author: 'Author name',
        mergetags_list: [{
                value: 'First.Name',
                title: 'First Name'
            },
            {
                value: 'Email',
                title: 'Email'
            },
        ],
        ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
    });
</script>
 -->
@endsection