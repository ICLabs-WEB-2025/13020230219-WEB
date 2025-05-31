<?php
namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentShareController extends Controller
{
    public function index(Request $request)
    {
        // Ambil dokumen yang dibagikan oleh pengguna
        $sharedByYou = Document::where('user_id', auth()->id())->with('sharedWith')->get();

        // Ambil dokumen yang dibagikan kepada pengguna
        $sharedWithYou = Document::whereHas('sharedWith', fn($query) => $query->where('email', auth()->user()->email))
            ->where('user_id', '!=', auth()->id()) // Pastikan pemilik dokumen bukan pengguna saat ini
            ->get();

        return view('documentShare.index', compact('sharedByYou', 'sharedWithYou'));
    }

    public function share(Request $request, $id)
    {
        // Validasi email
        $request->validate(['email' => 'required|email']);
        
        // Temukan dokumen berdasarkan ID
        $document = Document::findOrFail($id);

        // Pastikan hanya pemilik dokumen yang bisa berbagi
        if ($document->user_id !== auth()->id()) {
            return redirect()->route('documents.index')->with('error', 'Unauthorized');
        }

        // Cegah berbagi ke diri sendiri
        if ($request->email === auth()->user()->email) {
            return redirect()->route('documents.index')->with('error', 'You cannot share this document with yourself.');
        }

        // Cek jika dokumen sudah dibagikan ke email yang sama
        $existingShare = DocumentShare::where('document_id', $id)
            ->where('email', $request->email)
            ->first();

        if ($existingShare) {
            return redirect()->route('documents.index')->with('error', 'This document is already shared with this email.');
        }

        // Share document ke email
        $document->sharedWith()->create(['email' => $request->email]);

        // Catat aktivitas berbagi
        Activity::create([
            'user_id' => auth()->id(),
            'activity' => 'Shared document: ' . $document->file_name . ' with ' . $request->email,
        ]);

        // Redirect setelah berbagi
        return redirect()->route('documents.index')->with('success', 'Document shared successfully');
    }

    public function view($id)
    {
        // Menampilkan dokumen yang dibagikan
        $document = Document::findOrFail($id);
        return response()->file(storage_path('app/' . $document->file_path));
    }
}
