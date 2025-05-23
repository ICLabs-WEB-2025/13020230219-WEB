<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Document;
use App\Models\DocumentShare;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard pengguna
     */


    public function index()
    {
        // Ambil data aktivitas terbaru untuk pengguna yang sedang login
        $activities = Activity::where('user_id', auth()->id()) // Mengambil log aktivitas berdasarkan user_id
            ->orderBy('created_at', 'desc') // Urutkan berdasarkan waktu terbaru
            ->take(10) // Ambil 5 aktivitas terbaru
            ->get();

        // Menghitung jumlah dokumen yang dimiliki oleh pengguna
        $documentsCount = Document::where('user_id', auth()->id())->count();

        $sharedWithYouCount = DocumentShare::where('email', auth()->user()->email)  // Memastikan dokumen dibagikan kepada pengguna yang sedang login
            ->count();

        $sharedByYouCount = DocumentShare::whereHas('document', function ($query) {
            $query->where('user_id', auth()->id());  // Pastikan dokumen milik pengguna yang sedang login
        })->count();

        // Menghitung jumlah teman yang dimiliki oleh pengguna (misalnya relasi dengan model Friends)
        $recentActivities = Activity::where('user_id', Auth::id())->latest()->take(5)->get();

        // Mengirimkan data ke view dashboard
        return view('dashboard', compact('recentActivities', 'documentsCount', 'sharedWithYouCount', 'sharedByYouCount'));
    }
}
