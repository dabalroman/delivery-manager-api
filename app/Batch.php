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
 * @property string $delivery_date
 * @method static Builder|Batch whereDeliveryDate($value)
 * @property int $user_id
 * @method static Builder|Batch whereUserId($value)
 * @method findOrFail($batchID)
 * @method firstWhere(string $string, string $string1, $filename)
 */
class Batch extends Model
{
    protected $table = 'import_batch';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'source',
        'delivery_date',
        'new_addresses_amount',
        'known_addresses_amount',
        'orders_amount',
        'import_date'
    ];

    protected $hidden = [];
}
