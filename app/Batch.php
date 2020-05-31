<?php

namespace App;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Batch
 *
 * @property int $id
 * @property string $source
 * @property string $import_date
 * @property int $new_addresses_amount
 * @property int $known_addresses_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Batch newModelQuery()
 * @method static Builder|Batch newQuery()
 * @method static Builder|Batch query()
 * @method static Builder|Batch whereAddressAmount($value)
 * @method static Builder|Batch whereCreatedAt($value)
 * @method static Builder|Batch whereId($value)
 * @method static Builder|Batch whereImportDate($value)
 * @method static Builder|Batch whereOrdersAmount($value)
 * @method static Builder|Batch whereSource($value)
 * @method static Builder|Batch whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder|Batch whereKnownAddressesAmount($value)
 * @method static Builder|Batch whereNewAddressesAmount($value)
 * @property int $orders_amount
 */
class Batch extends Model
{
    protected $table = 'import_batch';
    public $timestamps = false;

    protected $fillable = [
        'source',
        'import_date',
        'new_addresses_amount',
        'known_addresses_amount',
        'orders_amount'
    ];

    protected $hidden = [];
}
