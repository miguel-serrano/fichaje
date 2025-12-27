<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroHorario extends Model
{
    use HasFactory;
    protected $table = 'registro_horarios';
    protected $fillable = ['user_id', 'entrada', 'salida'];

    public $timestamps = false;
}

