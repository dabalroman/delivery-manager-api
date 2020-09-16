<?php

namespace App;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Route
 *
 * @property int         $id
 * @property string      $addresses_ids
 * @property string      $id_hash
 * @property string      $routed_hash
 * @property int         $courier_id
 * @property int         $batch_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Route newModelQuery()
 * @method static Builder|Route newQuery()
 * @method static Builder|Route query()
 * @method static Builder|Route whereAddressesIds($value)
 * @method static Builder|Route whereBatchId($value)
 * @method static Builder|Route whereCourierId($value)
 * @method static Builder|Route whereCreatedAt($value)
 * @method static Builder|Route whereId($value)
 * @method static Builder|Route whereIdHash($value)
 * @method static Builder|Route whereRoutedHash($value)
 * @method static Builder|Route whereUpdatedAt($value)
 * @method findOrNew($route_id)
 * @method find($routeID)
 * @method where(string $string, $id)
 * @mixin Eloquent
 */
class Route extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'route';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'addresses_ids',
        'id_hash',
        'routed_hash',
        'batch_id',
        'courier_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];
}
