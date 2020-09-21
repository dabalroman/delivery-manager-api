<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Address
 *
 * @property int         $id
 * @property string      $city
 * @property string      $street
 * @property string      $street_number
 * @property string      $flat_number
 * @property int         $floor
 * @property string      $client_name
 * @property string      $delivery_hours
 * @property string      $phone
 * @property string      $code
 * @property string      $comment
 * @property string      $geo_cord
 * @property string      $id_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|RouteBit newModelQuery()
 * @method static Builder|RouteBit newQuery()
 * @method static Builder|RouteBit query()
 * @method static Builder|RouteBit whereCity($value)
 * @method static Builder|RouteBit whereClientName($value)
 * @method static Builder|RouteBit whereCode($value)
 * @method static Builder|RouteBit whereComment($value)
 * @method static Builder|RouteBit whereCreatedAt($value)
 * @method static Builder|RouteBit whereDeliveryHours($value)
 * @method static Builder|RouteBit whereFlatNumber($value)
 * @method static Builder|RouteBit whereFloor($value)
 * @method static Builder|RouteBit whereGeoCord($value)
 * @method static Builder|RouteBit whereId($value)
 * @method static Builder|RouteBit whereIdHash($value)
 * @method static Builder|RouteBit wherePhone($value)
 * @method static Builder|RouteBit whereStreet($value)
 * @method static Builder|RouteBit whereStreetNumber($value)
 * @method static Builder|RouteBit whereUpdatedAt($value)
 * @method whereIn(string $string, array $addresses_array)
 * @method where(string $string, $address_hash)
 * @method find($addressID)
 * @mixin Eloquent
 */
class RouteBit extends Model
{
    /**
     * @var string
     */
    protected $table = 'routebits';

    /**
     * @var string[]
     */
    protected $fillable = [
        'start',
        'end',
        'polyline',
        'length'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [];
}
