<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = "invoice";// <-- El nombre personalizado

    protected $fillable = [
        'customer_id', 'date','guarantee_id','garantia','discount','subtotal','iva','total','estado'
    ];
}
