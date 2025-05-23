<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Tentukan kolom yang boleh diisi
    protected $fillable = [
        'username',
        'email',
        'password',
        'photoprofile',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_user_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
