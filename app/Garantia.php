<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
    protected $table = "guarantees";// <-- El nombre personalizado

    protected $fillable = [
        'name'
    ];
}
