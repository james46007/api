<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Iva extends Model
{
    protected $table = "iva";// <-- El nombre personalizado

    protected $fillable = [
        'iva',
        'estado'
    ];
}
