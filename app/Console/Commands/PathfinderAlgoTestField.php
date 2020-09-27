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
        unset($key, $bit);

        //Create new, constant array keys
        $bits = array_values($bits);

        //Create square array with bonds between ids
        $bondsArray = [];
        foreach ($query as $baseId) {
            $bondsArray[$baseId] = [];

            foreach ($query as $afterId) {
                $matches = 0;
                $bondStrength = 0;

                if ($baseId == $afterId) {
                    $bondsArray[$baseId][$afterId] = null;
                    continue;
                }

                foreach ($routes as $route) {
                    $afterIdPos = array_search($afterId, $route['ids']);
                    if ($afterIdPos === false) continue;

                    $baseIdPos = array_search($baseId, $route['ids']);
                    if ($baseIdPos === false || $baseIdPos >= $afterIdPos) continue;

                    $matches++;
                    $bondStrength += $afterIdPos - $baseIdPos;
                }
                unset($route);

                $bondsArray[$baseId][$afterId] = ($matches > 0 && $bondStrength > 0)
                    ? 1 / ($bondStrength / $matches)
                    : 0;
            }
            unset($afterId);
        }
        unset($baseId);

        print_r($bits);

        $bitsBondsArray = [];
        //Create square array with bits bonds
        for ($bitAIndex = 0; $bitAIndex < count($bits); $bitAIndex++) {
            for ($bitBIndex = 0; $bitBIndex < count($bits); $bitBIndex++) {
                if ($bitAIndex == $bitBIndex) {
                    $bitsBondsArray[$bitAIndex][$bitBIndex] = null;
                    continue;
                }

                $matches = 0;
                $bondStrength = 1;

                for ($idA = 0; $idA < count($bits[$bitAIndex]); $idA++) {
                    for ($idB = 0; $idB < count($bits[$bitBIndex]); $idB++) {
                        $strength = $bondsArray[$bits[$bitAIndex][$idA]][$bits[$bitBIndex][$idB]];

                        if ($strength > 0) {
                            $matches++;
                            $bondStrength *= $strength;
                        }
                    }
                }

                $bitsBondsArray[$bitAIndex][$bitBIndex] = $matches * $bondStrength;
            }
        }

//        print_r($bitsBondsArray);

        //Arrange and merge bits into route
        $routeParts = [];
        while (count($bitsBondsArray)) {
            //Find max bitsBound
            $maxAIndex = 0;
            $maxBIndex = 0;
            $maxBondStrength = 0;
            foreach ($bitsBondsArray as $keyA => $bitBonds) {
                foreach ($bitBonds as $keyB => $bondStrength) {
                    if ($bondStrength > $maxBondStrength) {
                        $maxBondStrength = $bondStrength;
                        $maxAIndex = $keyA;
                        $maxBIndex = $keyB;
                    }
                }
            }

            echo "$maxAIndex (" . join(',', $bits[$maxAIndex]) . ")"
                . " <-> $maxBIndex (" . join(',', $bits[$maxBIndex]) . ")\n";

            $aPart = array_search($maxAIndex, $routeParts);
            $bPart = array_search($maxBIndex, $routeParts);

            if ($aPart === false && $bPart === false) {
                array_push($routeParts, $maxAIndex, $maxBIndex);
            } else if ($bPart === 0) {
                array_unshift($routeParts, $maxAIndex);
            } else if ($aPart === count($routeParts) - 1) {
                array_push($routeParts, $maxBIndex);
            }

            unset($bitsBondsArray[$maxAIndex], $bitsBondsArray[$maxBIndex]);
        }

        //Flatten routeParts array
        $arrangedRoute = array_map(function ($part) use ($bits) {
            return $bits[$part];
        }, $routeParts);
        $arrangedRoute = array_reduce($arrangedRoute, function ($accumulator, $current) {
            array_push($accumulator, ...$current);
            return $accumulator;
        }, []);

        //Add missing keys
        foreach ($query as $id) {
            if (!in_array($id, $arrangedRoute)) {
                array_push($arrangedRoute, $id);
            }
        }

        echo 'Route: ' . join(',', $arrangedRoute) . "\n";
    }
}
