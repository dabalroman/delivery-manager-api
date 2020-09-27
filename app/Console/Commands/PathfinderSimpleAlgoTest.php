<?php /** @noinspection DuplicatedCode */

/** @noinspection PhpMissingFieldTypeInspection */

namespace App\Console\Commands;

use App\Pathfinder\Pathfinder;
use Illuminate\Console\Command;

class PathfinderSimpleAlgoTest extends Command
{
    protected $signature = "pathfinder:simple";

    public function handle()
    {
        $query = [1, 2, 70, 4, 71, 72, 73, 6, 7, 8, 13, 14, 15, 92, 74, 16, 17, 75, 20, 22, 24, 77, 25, 78, 27, 28, 29, 30, 79, 80, 32, 34, 81, 82, 35, 36, 83, 38, 39, 41, 42, 84, 44, 86, 47, 87, 93, 50, 51, 94, 88, 52, 53, 54, 55, 57, 58, 89, 90, 59, 60, 61, 63, 91, 66, 67, 69];

        echo join(',', Pathfinder::simpleRoute($query));
    }
}
