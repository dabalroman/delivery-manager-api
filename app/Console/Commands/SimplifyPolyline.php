<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App\Console\Commands;

use App\Pathfinder\PolylineObject;
use Exception;
use Illuminate\Console\Command;

class SimplifyPolyline extends Command
{
    protected $signature = "simplify:polyline {polyline}";

    public function handle()
    {
//        $tolerance = $this->argument('tolerance');
//        $polyline = $this->argument('polyline');

        $a = 'w`ypHewxrBBKHs@NiADY?A@M@I?GAEAGAGAGEKOa@Qc@';
        $b = '_aypH{`yrB\o@Ra@LQHOHQDKBO@I';

        $pll = PolylineObject::fromEncoded($a);

        try {
            $pll->joinAfter(PolylineObject::fromEncoded($b));
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        echo $pll->encode() . "\n";
        $pll->simplify();
        echo $pll->encode() . "\n";
        echo $pll;
    }
}
