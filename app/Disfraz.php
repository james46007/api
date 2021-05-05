<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Disfraz extends Model
{
    protected $table = "costumes";// <-- El nombre personalizado

    protected $fillable = [
        'name', 'photo','photoCostume'
    ];
}
