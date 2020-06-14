<?php

namespace App;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Courier
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Courier newModelQuery()
 * @method static Builder|Courier newQuery()
 * @method static Builder|Courier query()
 * @method static Builder|Courier whereCreatedAt($value)
 * @method static Builder|Courier whereId($value)
 * @method static Builder|Courier whereName($value)
 * @method static Builder|Courier whereUpdatedAt($value)
 * @method static Builder|Courier whereUserId($value)
 * @mixin Eloquent
 */
class Courier extends Model
{
    protected $table = 'courier';

    protected $fillable = [
        'name',
        'user_id',
    ];

    protected $hidden = [];
}
