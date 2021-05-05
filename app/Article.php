<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = "articles";// <-- El nombre personalizado

    protected $fillable = [
        'name','code','price'
    ];
}
