<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [ 'user_id', 'friend_user_id'];

    /**
     * Relasi dengan User (pemilik akun).
     */
    public function user()
    {
        return $this->belongsTo(User::class); // Relasi ke User (pemilik akun)
    }

    /**
     * Relasi dengan User (teman).
     */
    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_user_id'); // Relasi ke User sebagai teman
    }

    /**
     * Relasi dengan Activity yang terkait dengan teman.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'friend_id'); // Relasi ke Activity yang terkait dengan teman
    }
}
