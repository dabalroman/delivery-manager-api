<?php

namespace App\Console\Commands;

use App\Pathfinder\PolylineSimplifier;
use Exception;
use Illuminate\Console\Command;
use Polyline;

class SimplifyPolyline extends Command
{
    protected $signature = "simplify:polyline {tolerance} {polyline}";

    public function handle()
    {
        $tolerance = $this->argument('tolerance');
        $polyline = $this->argument('polyline');

        try {
            $points = Polyline::pair(Polyline::decode($polyline));
            $points = array_map(function ($point) {
                return ['x' => $point[0], 'y' => $point[1]];
            }, $points);

            $prev = count($points);
            $points = PolylineSimplifier::simplify($points, 0.0003);
            $after = count($points);
            $diff = round((1 - $after / $prev) * 100, 2);

            $points = array_map(function ($point) {
                return [$point['x'], $point['y']];
            }, $points);

            print_r($points);
            echo "\n\n" . Polyline::encode($points) . "\n\n";

            echo "Prev $prev \nAfter $after \nOptimised $diff%";
        } catch (Exception $e) {
            $this->error("An error occurred");
        }
    }
}
