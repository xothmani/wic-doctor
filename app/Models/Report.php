<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['fiche_id', 'file_path', 'title', 'description'];

    public function fiche()
    {
        return $this->belongsTo(Fiche::class);
    }
}

