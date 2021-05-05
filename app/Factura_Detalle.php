<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Factura_Detalle extends Model
{
    protected $table = "invoice_detail";// <-- El nombre personalizado

    protected $fillable = [
        'invoice_id', 'article_id','quantity','val_uni','val_tot',
    ];
}
