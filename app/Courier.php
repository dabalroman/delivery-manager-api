<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    protected $table = 'courier';

    protected $fillable = [
        'name',
        'user_id',
    ];

    protected $hidden = [];
}
