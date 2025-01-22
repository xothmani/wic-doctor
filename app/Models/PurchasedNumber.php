<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasedNumber extends Model
{
    protected $fillable = ['phone_number', 'user_id'];

    // Define relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
