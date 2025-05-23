<?php
namespace App\Http\Controllers;

use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        // Ambil semua aktivitas yang dilakukan oleh pengguna
        $activities = Activity::latest()->paginate(10);
        return view('activities.index', compact('activities'));
    }
}
