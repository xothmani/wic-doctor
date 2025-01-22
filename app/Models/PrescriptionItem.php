<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = ['prescription_id', 'type', 'name'];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
