<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App;

use App\GMaps_API\RouteBitsService;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * App\Route
 *
 * @property int    $id
 * @property string $addresses_ids
 * @property string $id_hash
 * @property string $routed_hash
 * @property int    $courier_id
 * @property int    $batch_id
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

    /**
     * @param int[] $addressesIds
     * @return string
     */
    public static function createIdHash(array $addressesIds): string
    {
        sort($addressesIds);
        return md5(join(',', $addressesIds));
    }

    /**
     * @param int[] $addressesIds
     * @return string
     */
    public static function createRoutedHash(array $addressesIds): string
    {
        return md5(join(',', $addressesIds));
    }

    /**
     * @param int $routeId
     * @return array Full route info
     * [
     * 'route_id' => int, 'addresses_ids' => string,'batch_id' => int,
     * 'courier_id' => int,'route_bits' => ['polyline' => string, 'length' => float]
     * ];
     * @throws Exception
     */
    public static function getRouteData(int $routeId)
    {
        /** @var Route $route */
        $route = (new Route)->find($routeId);

        $addressesCoordinates = DB::table('address')
            ->select('id', 'geo_cord')
            ->whereIn('id', explode(',', $route->addresses_ids))
            ->orderByRaw("FIELD(`id`,$route->addresses_ids)")
            ->get();

        $routeBits = [];
        for ($i = 0; $i < count($addressesCoordinates) - 1; $i++) {
            $start = $addressesCoordinates[$i]->geo_cord;
            $end = $addressesCoordinates[$i + 1]->geo_cord;
            $id = $addressesCoordinates[$i]->id . ',' . $addressesCoordinates[$i + 1]->id;

            $routeBits[$id] = RouteBitsService::getRouteBit($start, $end);
        }

        return [
            'id' => $route->id,
            'addresses_ids' => $route->addresses_ids,
            'batch_id' => $route->batch_id,
            'courier_id' => $route->courier_id,
            'route_bits' => $routeBits
        ];
    }
}
