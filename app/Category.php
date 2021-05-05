<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = "categories";// <-- El nombre personalizado

    protected $fillable = [
        'name',
    ];
}
