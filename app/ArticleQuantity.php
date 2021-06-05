<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticleQuantity extends Model
{
    protected $table = "article_quantities";// <-- El nombre personalizado

    protected $fillable = [
        'article_id','alquiler','devuelto','totalDisponible'
    ];
}
