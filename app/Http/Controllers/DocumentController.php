<?php

namespace App\Http\Controllers;

use DOMDocument;
use App\Models\Activity;
use App\Models\Document;
use App\Models\DocumentShare;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpSpreadsheet\PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PhpPresentationIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ZipArchive;
use RarArchive;

class DocumentController extends Controller
{

    // Menampilkan daftar dokumen yang dimiliki oleh pengguna
    public function index()
    {
        // Mengambil dokumen yang dimiliki oleh pengguna yang sedang login
        $documents = Document::where('user_id', Auth::id())->get();

        // Mengirimkan data dokumen ke view
        return view('documents.index', compact('documents'));
    }

    public function view($document_id)
    {
        $document = Document::findOrFail($document_id);
        $file_path = storage_path("app/public/{$document->file_path}");
        $file_extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
        $content = null;
        $preview_type = null;

        try {
            if ($file_extension == 'docx') {
                $phpWord = PhpWordIOFactory::load($file_path);
                $content = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $content .= $element->getText() . "\n";
                        }
                    }
                }
                $preview_type = 'text';
            } elseif ($file_extension == 'xlsx') {
                $spreadsheet = PhpSpreadsheetIOFactory::load($file_path);
                $sheet = $spreadsheet->getActiveSheet();
                $content = $sheet->toArray();
                $preview_type = 'table';
            } elseif ($file_extension == 'txt') {
                $content = file_get_contents($file_path);
                $preview_type = 'text';
            } elseif ($file_extension == 'pdf') {
                // PDF akan ditampilkan dengan iframe atau PDF.js
                $content = Storage::url($document->file_path);
                $preview_type = 'pdf';
            } else {
                return redirect()->route('documents.index')->with('error', 'Jenis file tidak didukung untuk pratinjau.');
            }
        } catch (\Exception $e) {
            return redirect()->route('documents.index')->with('error', 'Gagal memuat pratinjau file: ' . $e->getMessage());
        }

        return view('documents.view', compact('document', 'content', 'preview_type'));
    }

    public function store(Request $request)
    {
        // Validasi file yang di-upload
        $request->validate([
            'file' => 'required|mimes:docx,txt,xlsx,pdf,doc,pptx,jpeg,png|max:50240', // Max 10MB
        ]);

        // Simpan file ke storage
        $filePath = $request->file('file')->store('documents', 'public');

        // Simpan informasi file ke database
        $document = new Document();
        $document->user_id = Auth::id();
        $document->file_name = $request->file('file')->getClientOriginalName();
        $document->file_path = $filePath;
        $document->save();

        // Redirect ke halaman dokumen dengan pesan sukses
        return redirect()->route('documents.index')->with('success', 'Document uploaded successfully!');
    }

    // Menampilkan halaman edit file berdasarkan extension
    public function edit($document_id)
    {
        $document = Document::findOrFail($document_id);
        $file_path = storage_path("app/public/{$document->file_path}");
        $file_extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
        $content = '';

        try {
            if ($file_extension == 'docx') {
                $phpWord = PhpWordIOFactory::load($file_path);
                $content = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text = trim($element->getText());
                            if ($text !== '') {
                                $content .= '<p>' . htmlspecialchars($text) . '</p>';
                            }
                        }
                    }
                } // Catat aktivitas
                Activity::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Edit document: ' . $document->file_name,
                ]);
                return view('documents.edit-docx-ckeditor5', compact('content', 'document'));
            } elseif ($file_extension == 'xlsx') {
                $spreadsheet = PhpSpreadsheetIOFactory::load($file_path);
                $sheet = $spreadsheet->getActiveSheet();
                $content = $sheet->toArray();

                // Catat aktivitas
                Activity::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Edit document: ' . $document->file_name,
                ]);
                return view('documents.edit-xlsx', compact('content', 'document'));
            } elseif ($file_extension == 'txt') {
                // Membaca konten file .txt
                // Catat aktivitas
                Activity::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Edit document: ' . $document->file_name,
                ]);
                $content = file_get_contents($file_path);  // Membaca isi file tanpa <br/>
                return view('documents.edit-txt', compact('content', 'document'));
            } else {
                return redirect()->route('documents.index')->with('error', 'Jenis file tidak didukung untuk pengeditan.');
            }
        } catch (\Exception $e) {
            // Debugging: Tampilkan detail error
            // dd($e->getMessage());
            return redirect()->route('documents.index')->with('error', 'Gagal memuat file untuk pengeditan: ' . $e->getMessage());
        }
    }

    public function downloadAsPdf($document_id)
    {
        $document = Document::findOrFail($document_id);
        $file_path = storage_path("app/public/{$document->file_path}");
        $file_extension = pathinfo($document->file_name, PATHINFO_EXTENSION);

        try {
            if ($file_extension == 'pdf') {
                // Jika file sudah PDF, langsung unduh
                return response()->download($file_path, str_replace(".{$file_extension}", '.pdf', $document->file_name));
            }

            $dompdf = new Dompdf();
            $html = '<html><body>';

            if ($file_extension == 'docx') {
                $phpWord = PhpWordIOFactory::load($file_path);
                $content = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $content .= htmlspecialchars($element->getText()) . "<br>";
                        }
                    }
                }
                $html .= "<div>" . $content . "</div>";
            } elseif ($file_extension == 'xlsx') {
                $spreadsheet = PhpSpreadsheetIOFactory::load($file_path);
                $sheet = $spreadsheet->getActiveSheet();
                $content = $sheet->toArray();

                $html .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                foreach ($content as $row) {
                    $html .= "<tr>";
                    foreach ($row as $cell) {
                        $html .= "<td style='padding: 5px;'>" . htmlspecialchars($cell ?? '') . "</td>";
                    }
                    $html .= "</tr>";
                }
                $html .= "</table>";
            } elseif ($file_extension == 'txt') {
                $content = file_get_contents($file_path);
                $html .= "<pre>" . htmlspecialchars($content) . "</pre>";
            } else {
                return redirect()->back()->with('error', 'Jenis file tidak didukung untuk konversi ke PDF.');
            }

            $html .= '</body></html>';

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->stream(str_replace(".{$file_extension}", '.pdf', $document->file_name), ['Attachment' => true]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunduh file sebagai PDF: ' . $e->getMessage());
        }
    }

    public function showUploadForm()
    {
        return view('documents.upload');  // Pastikan Anda sudah memiliki view 'documents.upload'
    }

    public function update(Request $request, $document_id)
    {
        $document = Document::findOrFail($document_id);
        $file_path = storage_path("app/public/{$document->file_path}");
        $file_extension = pathinfo($document->file_name, PATHINFO_EXTENSION);

        // Update nama file yang dimasukkan pengguna
        $document->file_name = $request->input('file_name');
        $document->save();

        try {
            // Untuk file .docx (Word)
            if ($file_extension == 'docx') {
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $section = $phpWord->addSection();
                $content = $request->input('content');

                // Parse HTML dari CKEditor 5 menggunakan DOMDocument
                $dom = new DOMDocument();
                @$dom->loadHTML($content); // @ untuk suppress warning tentang HTML tidak valid
                $paragraphs = $dom->getElementsByTagName('p');

                // Tambahkan setiap paragraf ke dokumen Word
                foreach ($paragraphs as $paragraph) {
                    $text = $paragraph->textContent;
                    if (trim($text) !== '') {
                        $section->addText($text);
                    }
                }

                $newFilePath = storage_path("app/public/updated_{$document->file_name}");
                $writer = PhpWordIOFactory::createWriter($phpWord, 'Word2007');
                $writer->save($newFilePath);
                $document->file_path = "updated_{$document->file_name}";
                $document->save();
            }
            // Untuk file .txt (Text)
            elseif ($file_extension == 'txt') {
                file_put_contents($file_path, $request->input('content'));
            }
            // Untuk file .xlsx (Excel)
            elseif ($file_extension == 'xlsx') {
                $spreadsheet = PhpSpreadsheetIOFactory::load($file_path);
                $sheet = $spreadsheet->getActiveSheet();

                // Hapus semua data di sheet aktif
                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 0) {
                    $sheet->removeRow(1, $highestRow); // Hapus semua baris
                }

                // Ambil data dari input tabel
                $cells = $request->input('cells', []);

                // Jika cells kosong, simpan file tanpa menulis data baru
                if (!empty($cells)) {
                    // Loop untuk setiap baris dan kolom
                    foreach ($cells as $rowIndex => $row) {
                        foreach ($row as $colIndex => $value) {
                            if (trim($value) !== '') { // Hanya tulis nilai yang tidak kosong
                                $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                                $sheet->setCellValue($columnLetter . ($rowIndex + 1), $value);
                            }
                        }
                    }
                }

                // Simpan file yang diperbarui
                $newFilePath = storage_path("app/public/updated_{$document->file_name}");
                $writer = PhpSpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save($newFilePath);
                $document->file_path = "updated_{$document->file_name}";
                $document->save();
            } else {
                return redirect()->route('documents.index')->with('error', 'Jenis file tidak didukung.');
            }
        } catch (\Exception $e) {
            return redirect()->route('documents.index')->with('error', 'Gagal memperbarui dokumen: ' . $e->getMessage());
        }
        Activity::create([
            'user_id' => Auth::id(),
            'activity' => 'Updated document: ' . $document->file_name,
        ]);
        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diperbarui!');
    }

    /**
     * Menghapus dokumen yang telah di-upload.
     */
    public function delete($document_id)
    {
        $document = Document::findOrFail($document_id);

        if ($document->user_id !== Auth::id()) {
            return redirect()->route('documents.index')->with('error', 'You are not authorized to delete this document.');
        }

        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // Catat aktivitas
        Activity::create([
            'user_id' => Auth::id(),
            'activity' => 'Deleted document: ' . $document->file_name,
        ]);
        $document->delete();
        return redirect()->route('documents.index')->with('success', 'Document deleted successfully!');
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);
        $sharedDocument = SharedDocument::where('document_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($sharedDocument && $sharedDocument->can_download) {
            return response()->download(storage_path('app/' . $document->file_path));
        }

        return abort(403, 'You do not have permission to download this document.');
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index1(Request $request)
    {
        $sharedByYou = Document::where('user_id', auth()->id())->with('sharedWith')->get();
        $sharedWithYou = Document::whereHas('sharedWith', fn($query) => $query->where('email', auth()->user()->email))
            ->where('user_id', '!=', auth()->id()) // Pastikan pemilik dokumen bukan pengguna saat ini
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'sharedByYou' => $sharedByYou,
                'sharedWithYou' => $sharedWithYou
            ]);
        }

        return view('documents.index', compact('sharedByYou', 'sharedWithYou'));
    }

    public function share(Request $request, $id)
    {
        $request->validate(['email' => 'required|email']);
        $document = Document::findOrFail($id);

        if ($document->user_id !== auth()->id()) {
            return redirect()->route('documentShare.index')->with('error', 'Unauthorized');
        }

        // Cegah berbagi ke diri sendiri
        if ($request->email === auth()->user()->email) {
            return redirect()->route('documents.index')->with('error', 'You cannot share this document with yourself.');
        }

        // Cegah berbagi dokumen yang sama ke email yang sudah ada
        $existingShare = DocumentShare::where('document_id', $id)
            ->where('email', $request->email)
            ->first();

        if ($existingShare) {
            return redirect()->route('documents.index')->with('error', 'This document is already shared with this email.');
        }

        $document->sharedWith()->create(['email' => $request->email]);

        Activity::create([
            'user_id' => Auth::id(),
            'activity' => 'Shared document: ' . $document->file_name . ' with ' . $request->email,
        ]);

        return redirect()->route('documents.index')->with('success', 'Document shared successfully');
    }

    public function unshare(Request $request, $document_id, $share_id)
    {
        $document = Document::findOrFail($document_id);

        if ($document->user_id !== auth()->id()) {
            return redirect()->route('documentShare.index')->with('error', 'Unauthorized');
        }

        $share = DocumentShare::where('id', $share_id)
            ->where('document_id', $document_id)
            ->firstOrFail();

        $share->delete();

        Activity::create([
            'user_id' => Auth::id(),
            'activity' => 'Removed shared access of document: ' . $document->file_name . ' from ' . $share->email,
        ]);
        return redirect()->route('documentShare.index')->with('success', 'Access deleted successfully');
    }
}
