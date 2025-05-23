<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentShare extends Model
{
    protected $fillable = ['document_id', 'email'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
