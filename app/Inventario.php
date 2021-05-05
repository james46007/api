<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = "inventory";// <-- El nombre personalizado

    protected $fillable = [
        'article_id','date','description','entrada','ent_val_uni','ent_val_tot','salida','sal_val_uni','sal_val_tot','existe','exi_val_uni',
        'exi_val_tot','estado'
    ];
}
