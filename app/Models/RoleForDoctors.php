<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleForDoctors extends Model
{
    // Specify the associated table
    protected $table = 'role_for_doctors';

    // Define the fillable attributes
    protected $fillable = ['role_id'];

    // Define the relationship with the Role model
    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    // Define the relationship with the User model (creator/doctor)

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
