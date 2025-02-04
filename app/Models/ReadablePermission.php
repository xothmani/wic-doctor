<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadablePermission extends Model
{
    // If your table name doesn't follow Laravel's plural convention, specify it:
    protected $table = 'readable_permissions';

    // Define which attributes can be mass assigned.
    protected $fillable = [
        'permission_id',
        'readable_name',
    ];
}
