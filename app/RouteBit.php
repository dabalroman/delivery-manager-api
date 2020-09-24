<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App;

use App\Traits\HasCompositePrimaryKey;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\RouteBit
 *
 * @property string $start
 * @property string $end
 * @property string $polyline
 * @property int    $length
 * @method static Builder|RouteBit newModelQuery()
 * @method static Builder|RouteBit newQuery()
 * @method static Builder|RouteBit query()
 * @method static Builder|RouteBit whereEnd($value)
 * @method static Builder|RouteBit whereLength($value)
 * @method static Builder|RouteBit wherePolyline($value)
 * @method static Builder|RouteBit whereStart($value)
 * @method firstOrNew(string[] $array)
 * @mixin Eloquent
 */
class RouteBit extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'routebits';
    protected $primaryKey = ['start', 'end'];
    protected $fillable = [
        'start',
        'end',
        'polyline',
        'length'
    ];

    protected $hidden = [];
}
