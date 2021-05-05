<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Disfraz_Categoria extends Model
{
    protected $table = "costumes_category";// <-- El nombre personalizado

    protected $fillable = [
        'costume_id ', 'category_id ',
    ];
}
