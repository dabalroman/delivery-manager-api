<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Address
 *
 * @property int $id
 * @property string $city
 * @property string $street
 * @property string $street_number
 * @property string $flat_number
 * @property int $floor
 * @property string $client_name
 * @property string $delivery_hours
 * @property string $phone
 * @property string $code
 * @property string $comment
 * @property string $geo_cord
 * @property string $id_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Address newModelQuery()
 * @method static Builder|Address newQuery()
 * @method static Builder|Address query()
 * @method static Builder|Address whereCity($value)
 * @method static Builder|Address whereClientName($value)
 * @method static Builder|Address whereCode($value)
 * @method static Builder|Address whereComment($value)
 * @method static Builder|Address whereCreatedAt($value)
 * @method static Builder|Address whereDeliveryHours($value)
 * @method static Builder|Address whereFlatNumber($value)
 * @method static Builder|Address whereFloor($value)
 * @method static Builder|Address whereGeoCord($value)
 * @method static Builder|Address whereId($value)
 * @method static Builder|Address whereIdHash($value)
 * @method static Builder|Address wherePhone($value)
 * @method static Builder|Address whereStreet($value)
 * @method static Builder|Address whereStreetNumber($value)
 * @method static Builder|Address whereUpdatedAt($value)
 * @mixin Eloquent
 */
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
