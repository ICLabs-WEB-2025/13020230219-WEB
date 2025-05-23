<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentShareController extends Controller
{
    public function index(Request $request)
    {
        $sharedByYou = Document::where('user_id', auth()->id())->with('sharedWith')->get();
        $sharedWithYou = Document::whereHas('sharedWith', fn($query) => $query->where('email', auth()->user()->email))
            ->where('user_id', '!=', auth()->id()) // Pastikan pemilik dokumen bukan pengguna saat ini
            ->get();

        return view('documentShare.index', compact('sharedByYou', 'sharedWithYou'));
    }

    public function share(Request $request, $id)
    {
        $request->validate(['email' => 'required|email']);
        $document = Document::findOrFail($id);
        // Logika untuk menyimpan data share (misalnya, ke tabel pivot document_user)
        // Contoh: $document->sharedWith()->create(['email' => $request->email]);

        return response()->json(['success' => true, 'message' => 'Document shared successfully']);
    }

    public function view($id)
    {
        $document = Document::findOrFail($id);
        return response()->file(storage_path('app/' . $document->file_path));
    }
}
