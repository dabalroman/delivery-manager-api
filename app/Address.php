<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';

    protected $fillable = [
        'city',
        'street',
        'street_number',
        'flat_number',
        'floor',
        'client_name',
        'delivery_hours',
        'phone',
        'code',
        'comment',
        'geo_cord',
        'id_hash',
    ];

    protected $hidden = [];
}
