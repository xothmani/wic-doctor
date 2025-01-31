<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOwnership extends Model
{
    protected $table = 'user_ownership'; // Define the table name

    protected $fillable = [
        'user_id', // The ID of the created user
        'created_by', // The ID of the doctor who created the user
    ];

    // Optional: Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
