<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $activities = Activity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('profile.index', compact('user', 'activities'));
    }

    public function showRecentActivities()
    {
        $user = Auth::user();

        // Ambil aktivitas terkait dokumen dan profil pengguna
        $activities = Activity::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('activity_type', 'profile') // Aktivitas terkait profil
                    ->orWhere('activity_type', 'document'); // Aktivitas terkait dokumen
            })
            ->orderBy('created_at', 'desc') // Urutkan berdasarkan waktu terbaru
            ->take(10) // Batasi jumlah aktivitas yang ditampilkan
            ->get();

        return view('profile.index', compact('activities'));
    }

    public function show()
    {
        $user = Auth::user();
        $activities = Activity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('profile.index', compact('user', 'activities'));
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('profile.update', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:8',
            'photoprofile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',  // Validasi untuk foto profil
        ]);

        // Update profil pengguna
        $user->username = $request->input('username');
        $user->email = $request->input('email');

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        // Menangani upload foto profil
        if ($request->hasFile('photoprofile')) {
            // Hapus foto lama jika ada
            if ($user->photoprofile && Storage::exists('public/' . $user->photoprofile)) {
                Storage::delete('public/' . $user->photoprofile);
            }

            // Simpan foto baru
            $path = $request->file('photoprofile')->store('profile_photos', 'public');
            $user->photoprofile = $path;
        }

        // Menghapus foto jika opsi diaktifkan
        if ($request->has('remove_photo') && $request->remove_photo) {
            // Hapus foto profil jika ada
            if ($user->photoprofile && Storage::exists('public/' . $user->photoprofile)) {
                Storage::delete('public/' . $user->photoprofile);
            }

            $user->photoprofile = null; // Set foto profil menjadi null
        }

        $user->save();

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }




    public function showProfileImage($id)
    {
        $user = Auth::user(); // Mengambil user yang sedang login

        if ($user && $user->photoprofile) {
            $image = $user->photoprofile;  // Mendapatkan data gambar dalam bentuk binary
            return response($image, 200)->header('Content-Type', 'image/jpeg');  // Menampilkan gambar
        }

        return redirect()->route('profile')->with('error', 'Profile picture not found.');
    }
}
