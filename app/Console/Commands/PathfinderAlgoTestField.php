<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PathfinderAlgoTestField extends Command
{
    protected $signature = "pathfinder:test";

    public function handle()
    {
        $routes = [
            ['G', 'H', 'D', 'E', 'C', 'A'],
            ['A', 'B', 'C', 'D', 'E', 'F', 'G'],
            ['A', 'B', 'F', 'C', 'D', 'G']
        ];

        $query = ['C', 'E', 'F', 'B', 'H', 'X'];

        $routes = array_map(function ($route) {
            return [
                'similarity' => 0,
                'bits' => [],
                'ids' => $route
            ];
        }, $routes);

        //Find route bit groups
        foreach ($routes as &$route) {

            //Find routeIds in query and mark them
            $in_array = array_map(function ($id) use ($query) {
                return in_array($id, $query);
            }, $route['ids']);

            //Calc amount of matches
            $matches = array_reduce($in_array, function ($accumulator, $current) {
                return $accumulator + intval($current);
            }, 0);

            $route['similarity'] = $matches / (count($query) + count($route['ids']) - $matches);

            //Find matching bits
            $tempBit = [];
            foreach ($route['ids'] as $i => $id) {
                if ($in_array[$i]) {
                    array_push($tempBit, $id);
                } else {
                    if (count($tempBit)) {
                        array_push($route['bits'], $tempBit);
                    }

                    $tempBit = [];
                }
            }

            //Flush temp
            if (count($tempBit)) {
                array_push($route['bits'], $tempBit);
            }
        }
        unset($route);

        //Create array with matched bit groups and calculate their value index
        $bits = [];
        foreach ($routes as $route) {
            foreach ($route['bits'] as $bit) {
                array_push($bits, [
                    'ids' => $bit,
                    'value' => count($bit) * $route['similarity']
                ]);
            }
        }
        unset($route);

        //Sort by value index
        usort($bits, function ($a, $b) {
            return $a['value'] < $b['value'];
        });

        //Remove ids that exists more than once
        for ($i = 0; $i < count($bits) - 1; $i++) {
            foreach ($bits[$i]['ids'] as $id) {
                for ($j = $i + 1; $j < count($bits); $j++) {
                    $key = array_search($id, $bits[$j]['ids']);

                    if ($key !== false) {
                        unset($bits[$j]['ids'][$key]);
                    }
                }
            }
        }

        //Remove empty bits and simplify array
        foreach ($bits as $key => $bit) {
            if (count($bit['ids']) === 0) {
                unset($bits[$key]);
            } else {
                $bits[$key] = $bit['ids'];
            }
        }
        unset($key);
        unset($bit);

        //Create square array with bounds between ids
        $boundsArray = [];
        foreach ($query as $baseId) {
            $boundsArray[$baseId] = [];

            foreach ($query as $afterId) {
                $matches = 0;
                $boundStrength = 0;

                if ($baseId == $afterId) {
                    $boundsArray[$baseId][$afterId] = null;
                    continue;
                }

                foreach ($routes as $route) {
                    $afterIdPos = array_search($afterId, $route['ids']);
                    if ($afterIdPos === false) continue;

                    $baseIdPos = array_search($baseId, $route['ids']);
                    if ($baseIdPos === false || $baseIdPos >= $afterIdPos) continue;

                    $matches++;
                    $boundStrength += $afterIdPos - $baseIdPos;
                }
                unset($route);

                echo "$baseId$afterId $matches, $boundStrength\n";

                $boundsArray[$baseId][$afterId] = ($matches > 0 && $boundStrength > 0)
                    ? 1 / ($boundStrength / $matches)
                    : 0;
            }
            unset($afterId);
        }
        unset($baseId);

        print_r($boundsArray);

        print_r($bits);

//        print_r($routes);
    }
}
