<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\User
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static where(string $string, $input)
 * @mixin Eloquent
 */
class User extends Model
{
    protected $table = 'user';

    protected $fillable = [
        'name',
        'email',
    ];

    protected $hidden = [];
}
