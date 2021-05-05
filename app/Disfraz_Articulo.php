<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Disfraz_Articulo extends Model
{
    protected $table = "articles_costume";// <-- El nombre personalizado

    protected $fillable = [
        'costume_id ', 'article_id ',
    ];
}
