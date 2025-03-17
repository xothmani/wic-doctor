<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $table = 'membership';

    protected $fillable = [
        'user_id',
        'pack_id',
        'start_date',
        'end_date',
        'payment_amount',
        'payment_date',
    ];

    public $timestamps = false; // Disable timestamps if your table doesn’t have created_at/updated_at
}
