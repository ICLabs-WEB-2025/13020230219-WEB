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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ZipArchive;
use RarArchive;
use Illuminate\Support\Str;


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
        $filePath = storage_path("app/public/{$document->file_path}");
        // Normalisasi ekstensi file ke huruf kecil untuk konsistensi
        $fileExtension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
        $content = null;
        $preview_type = null;

        // Periksa apakah file benar-benar ada di storage
        if (!Storage::disk('public')->exists($document->file_path)) {
            Log::error("File not found in storage: app/public/{$document->file_path} for document ID: {$document_id}");
            return redirect()->route('documents.index')->with('error', 'File tidak ditemukan di server.');
        }

        try {
            if ($fileExtension == 'docx' || $fileExtension == 'doc') {
                // Untuk file .docx, ekstensi ZipArchive sangat penting.
                // Untuk .doc (format biner lama), mungkin tidak secara langsung, tapi library PhpWord bisa memiliki dependensi internal.
                if ($fileExtension == 'docx' && !class_exists('ZipArchive')) {
                    Log::error('PHP ZipArchive extension is required for DOCX preview but not found.');
                    return redirect()->route('documents.index')->with('error', 'Ekstensi PHP ZipArchive diperlukan untuk pratinjau file DOCX. Harap hubungi administrator server.');
                }

                $readerType = null;
                if ($fileExtension == 'docx') {
                    $readerType = 'Word2007'; // Reader untuk format Office Open XML (.docx)
                } elseif ($fileExtension == 'doc') {
                    $readerType = 'MsDoc';    // Reader untuk format Word 97-2003 (.doc)
                }

                if ($readerType) {
                    try {
                        $phpWord = PhpWordIOFactory::load($filePath, $readerType);
                    } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                        $errorCode = method_exists($e, 'getCode') ? $e->getCode() : null;
                        // Kode 19 (ZipArchive::ER_NOZIP) spesifik untuk masalah pembacaan arsip ZIP (umumnya file .docx)
                        if ($fileExtension == 'docx') {
                            Log::error("Error loading DOCX (not a zip archive - code 19) for file: {$filePath}. Error: " . $e->getMessage());
                            return redirect()->route('documents.index')->with('error', 'Gagal memuat pratinjau file DOCX: File mungkin rusak, bukan format DOCX yang valid, atau ekstensi ZipArchive PHP bermasalah. Error code: 19.');
                        }
                        // Untuk error lain atau error pada file .doc
                        Log::error("PhpWord load failed for file: {$filePath} with reader {$readerType}. Error: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                        return redirect()->route('documents.index')->with('error', 'Gagal memuat pratinjau file Word: ' . $e->getMessage());
                    }

                    // Logika ekstraksi teks yang sedikit lebih baik
                    $extractedTextParagraphs = [];
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $element) {
                            $paragraphText = '';
                            if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                // TextRun adalah kumpulan bagian teks dalam satu paragraf
                                foreach ($element->getElements() as $textElement) {
                                    if (method_exists($textElement, 'getText')) {
                                        $paragraphText .= $textElement->getText();
                                    }
                                }
                            } elseif (method_exists($element, 'getText')) {
                                // Elemen teks langsung (biasanya teks seluruh paragraf jika tidak dalam TextRun)
                                $paragraphText = $element->getText();
                            }
                            // Anda bisa menambahkan penanganan untuk elemen lain di sini jika perlu (misalnya teks dari sel tabel)

                            if (trim($paragraphText) !== '') {
                                $extractedTextParagraphs[] = trim($paragraphText);
                            }
                        }
                    }
                    // Gabungkan paragraf yang diekstrak dengan dua baris baru diantaranya
                    $content = implode("\n\n", $extractedTextParagraphs);
                    $preview_type = 'text';
                } else {
                    // Seharusnya tidak terjadi jika ekstensi sudah 'docx' atau 'doc'
                    return redirect()->route('documents.index')->with('error', 'Jenis file Word tidak dikenal.');
                }
            } elseif ($fileExtension == 'xlsx') {
                $spreadsheet = PhpSpreadsheetIOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $content = $sheet->toArray();
                $preview_type = 'table';
            } elseif ($fileExtension == 'txt') {
                $content = file_get_contents($filePath); // file_get_contents sudah aman karena path sudah divalidasi
                $preview_type = 'text';
            } elseif ($fileExtension == 'pdf') {
                $content = Storage::url($document->file_path); // Menggunakan Storage::url untuk akses publik
                $preview_type = 'pdf';
            } else {
                return redirect()->route('documents.index')->with('error', 'Jenis file tidak didukung untuk pratinjau.');
            }
        } catch (\Exception $e) {
            // Tangkap semua error lain yang mungkin terjadi
            Log::error("General error previewing file ID {$document->id} ({$filePath}): " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return redirect()->route('documents.index')->with('error', 'Gagal memuat pratinjau file. Terjadi kesalahan umum: ' . $e->getMessage());
        }

        return view('documents.view', compact('document', 'content', 'preview_type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:docx,xlsx,txt,pdf|max:10240', // Maks 10MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName(); // e.g., "Laporan Saya (rev 2).docx"

        // 1. Sanitasi nama file untuk keamanan dan kompatibilitas filesystem
        // Mengambil nama dasar tanpa ekstensi
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        // Mengambil ekstensi
        $extension = $file->getClientOriginalExtension(); // Lebih aman daripada pathinfo untuk ekstensi dari upload

        // Membuat versi nama file yang "aman" untuk disimpan di server
        // Str::slug akan mengganti spasi dengan '-' dan menghapus karakter non-alfanumerik
        $safeBaseName = Str::slug($baseName);
        if (empty($safeBaseName)) { // Jika nama file hanya berisi karakter aneh
            $safeBaseName = 'file-' . time();
        }

        $serverFileName = $safeBaseName . '.' . $extension; // e.g., "laporan-saya-rev-2.docx"

        // 2. Penanganan konflik nama file
        $targetDirectory = 'documents'; // Direktori penyimpanan
        $counter = 1;
        $finalServerFileName = $serverFileName;
        // Cek apakah file dengan nama ini sudah ada di disk 'public' dalam direktori target
        while (Storage::disk('public')->exists($targetDirectory . '/' . $finalServerFileName)) {
            $finalServerFileName = $safeBaseName . '_' . $counter . '.' . $extension;
            $counter++;
        }
        // Sekarang $finalServerFileName adalah nama unik yang akan digunakan di server
        // contoh: "laporan-saya-rev-2.docx" atau "laporan-saya-rev-2_1.docx" jika ada konflik

        // 3. Simpan file ke server dengan nama yang sudah diproses
        // $path akan berisi 'documents/nama_file_server_final.ext'
        $path = $file->storeAs($targetDirectory, $finalServerFileName, 'public');

        if (!$path) {
            return redirect()->route('documents.upload')
                ->with('error', 'Gagal menyimpan file. Pastikan direktori penyimpanan dapat ditulis.');
        }

        // 4. Simpan informasi ke database
        $document = Document::create([
            'user_id' => Auth::id(),
            // 'file_name' tetap menyimpan NAMA ASLI yang diupload pengguna, untuk tampilan
            'file_name' => $originalName,
            // 'file_path' menyimpan path relatif dengan NAMA FILE SERVER yang sudah diproses
            'file_path' => $path,
            'file_type' => $extension,
        ]);

        Activity::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengunggah dokumen: ' . $originalName,
        ]);

        return redirect()->route('documents.index')
            ->with('success', "Dokumen '{$originalName}' berhasil diunggah.");
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
                $content = ''; // Inisialisasi konten kosong

                // Menambahkan logika untuk mengambil teks saja, bukan dalam bentuk HTML
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text = trim($element->getText());
                            if ($text !== '') {
                                $content .= $text . "\n";  // Menambahkan teks tanpa tag HTML
                            }
                        }
                    }
                }

                // Catat aktivitas
                Activity::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Edit document: ' . $document->file_name,
                ]);

                // Menampilkan halaman edit dengan teks biasa
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
            if ($file_extension == 'xlsx') {
                $spreadsheet = PhpSpreadsheetIOFactory::load($file_path);
                $sheet = $spreadsheet->getActiveSheet();

                // Hapus semua isi sheet
                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 0) {
                    $sheet->removeRow(1, $highestRow);
                }

                // Ambil dari textarea, bukan dari 'cells'
                $rawText = $request->input('content', '');

                if (trim($rawText) !== '') {
                    $rows = explode("\n", trim($rawText));
                    foreach ($rows as $rowIndex => $row) {
                        $columns = array_map('trim', explode(',', $row));
                        foreach ($columns as $colIndex => $value) {
                            if ($value !== '') {
                                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                                $sheet->setCellValue($columnLetter . ($rowIndex + 1), $value);
                            }
                        }
                    }
                }

                // Simpan file
                $writer = PhpSpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save($file_path);

                $document->save();

                // Catat aktivitas
                Activity::create([
                    'user_id' => Auth::id(),
                    'activity' => 'Updated Excel document: ' . $document->file_name,
                ]);

                return redirect()->route('documents.index')->with('success', 'Excel file updated.');
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

    public function download($document_id)
    {
        try {
            $document = Document::findOrFail($document_id);

            if ($document->user_id === Auth::id()) {
                if (!Storage::disk('public')->exists($document->file_path)) {
                    Log::warning("PEMILIK - File tidak ditemukan: Dok. ID: {$document->id}, User ID: " . Auth::id() . ", Path: {$document->file_path}");
                    return redirect()->back()->with('error', 'File dokumen (milik) tidak ditemukan.'); // Pesan spesifik 1
                }
                Log::info("PEMILIK - Mengunduh file: Dok. ID: {$document->id}, User ID: " . Auth::id() . ", Path: {$document->file_path}");
                return Storage::disk('public')->download($document->file_path, $document->file_name);
            }

            // B. Pemeriksaan Otorisasi Pengguna yang Dibagikan
            $sharedDocument = DocumentShare::where('document_id', $document_id)
                ->where('email', Auth::user()->email)
                ->first();

            if ($sharedDocument) {
                if (!Storage::disk('public')->exists($document->file_path)) {
                    Log::warning("SHARED - File tidak ditemukan: Dok. ID: {$document->id}, User Email: " . Auth::user()->email . ", Path: {$document->file_path}");
                    return redirect()->back()->with('error', 'File dokumen (dibagikan) tidak ditemukan.'); // Pesan spesifik 2
                }
                Log::info("SHARED - Mengunduh file: Dok. ID: {$document->id}, User Email: " . Auth::user()->email . ", Path: {$document->file_path}");
                return Storage::disk('public')->download($document->file_path, $document->file_name);
            }

            // C. Akses Ditolak
            Log::warning("DITOLAK - Akses unduh: Dok. ID: {$document->id}, User ID: " . Auth::id());
            return abort(403, 'Anda tidak memiliki izin untuk mengunduh dokumen ini.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("GAGAL UNDUH - Dokumen tidak ditemukan: ID: {$document_id}. Error: " . $e->getMessage());
            return abort(404, 'Dokumen yang Anda cari tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error("GAGAL UNDUH - Kesalahan umum: ID: {$document_id}. Error: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return redirect()->route('documents.index')->with('error', 'Terjadi kesalahan sistem saat mencoba mengunduh dokumen.');
        }
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
            return redirect()->route('documents.index')->with('error', 'Unauthorized');
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

        // Share document
        $document->sharedWith()->create(['email' => $request->email]);

        // Log activity
        Activity::create([
            'user_id' => Auth::id(),
            'activity' => 'Shared document: ' . $document->file_name . ' with ' . $request->email,
        ]);

        // Redirect with success message
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
