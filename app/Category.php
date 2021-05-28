<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = "categories";// <-- El nombre personalizado

    protected $fillable = [
        'name',
    ];

    public function disfraces(){
        return $this->belongsToMany(Disfraz::class,'costumes_category','category_id','costume_id')->withTimestamps();
    }
}
