<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registro()
    {
        return $this->belongsTo(Registro::class);
    }
}
