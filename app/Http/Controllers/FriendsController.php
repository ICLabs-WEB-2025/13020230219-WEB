<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendsController extends Controller
{
    public function index()
    {
        // Cek apakah ada data teman yang memberikan akses
        $friendsWithAccess = Auth::user()->friendsWithAccess;
        $friendsYouGivenAccess = Auth::user()->friendsYouGivenAccess;

        // Jika tidak ada data, beri nilai default array kosong
        if (!$friendsWithAccess) {
            $friendsWithAccess = [];
        }

        if (!$friendsYouGivenAccess) {
            $friendsYouGivenAccess = [];
        }

        // Kirim data ke view
        return view('documents.share');
    }
}
