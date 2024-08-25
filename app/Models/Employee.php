<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'salary',
        'position',
        'department',
        'address',
        'hire_date',
        'active',
        'username',
        'password',
    ];

    /**
     * The roles that belong to the employee.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'employee_roles');
    }
}
