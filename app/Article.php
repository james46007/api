<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = "articles";// <-- El nombre personalizado

    protected $fillable = [
        'name','code','price'
    ];

    public function disfraces(){
        return $this->belongsToMany(Disfraz::class,'articles_costume','article_id','costume_id')->withTimestamps();
    }
}
