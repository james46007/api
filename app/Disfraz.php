<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Disfraz extends Model
{
    protected $table = "costumes";// <-- El nombre personalizado

    protected $fillable = [
        'name', 'photo','photoCostume'
    ];

    public function categorias(){
        return $this->belongsToMany(Category::class,'costumes_category','costume_id','category_id')->withTimestamps();
    }

    public function articulos(){
        return $this->belongsToMany(Article::class,'articles_costume','costume_id','article_id')->withTimestamps();
    }
}
