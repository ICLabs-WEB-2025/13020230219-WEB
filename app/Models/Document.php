<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan (jika berbeda dari nama default)
    protected $table = 'documents';

    // Kolom yang dapat diisi melalui mass assignment
    protected $fillable = [
        'user_id',     // ID pengguna yang mengupload dokumen
        'file_name',   // Nama file yang di-upload
        'file_path',   // Path file di storage
        'access_edit', // Akses edit (misalnya boolean)
    ];

    public function sharedWith()
    {
        return $this->hasMany(DocumentShare::class);
    }

    // Relasi dengan model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
