<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alquiler extends Model
{
    protected $table = "rentals";// <-- El nombre personalizado

    protected $fillable = [
        'invoice_id','devuelto'
    ];
}
