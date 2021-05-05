<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = "customers";// <-- El nombre personalizado

    protected $fillable = [
        'name','surname','identity_card','direction','cellphone','email',
    ];
}
