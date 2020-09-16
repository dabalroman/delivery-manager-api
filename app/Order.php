<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Order
 *
 * @property int         $id
 * @property string      $type
 * @property int         $amount
 * @property int         $address_id
 * @property int         $batch_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereAddressId($value)
 * @method static Builder|Order whereAmount($value)
 * @method static Builder|Order whereBatchId($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereType($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @mixin Eloquent
 * @property int         $owner
 * @property int         $assigned_to
 * @method static Builder|Order whereAssignedTo($value)
 * @method static Builder|Order whereOwner($value)
 */
class Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'amount',
        'address_id',
        'batch_id',
        'owner',
        'assigned_to'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];
}
