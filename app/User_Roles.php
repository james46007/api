<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Roles extends Model
{

    protected $table = "user_roles";// <-- El nombre personalizado

    protected $fillable = [
        'user_id', 'rol_id',
    ];
    
}
