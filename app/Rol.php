<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{

    protected $table = "roles";// <-- El nombre personalizado

    protected $fillable = [
        'id',
        'name',
        'estado'
    ];
    
}
