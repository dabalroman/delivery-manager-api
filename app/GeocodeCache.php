<?php

namespace App;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\GeocodeCache
 *
 * @property string $key
 * @property string $geocode
 * @method static Builder|GeocodeCache newModelQuery()
 * @method static Builder|GeocodeCache newQuery()
 * @method static Builder|GeocodeCache query()
 * @method static Builder|GeocodeCache whereGeocode($value)
 * @method static Builder|GeocodeCache whereKey($value)
 * @mixin Eloquent
 */
class GeocodeCache extends Model
{
    protected $table = 'geocode_cache';
    public $timestamps = false;

    protected $fillable = [
        'key',
        'geocode'
    ];

    protected $hidden = [];
}
