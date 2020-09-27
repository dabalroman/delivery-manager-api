<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PathfinderAlgoTestField extends Command
{
    protected $signature = "pathfinder:test";

    public function handle()
    {
        $routes = [
            [52, 63, 61, 62, 36, 24, 23, 22, 47, 12, 37, 35, 39, 21, 11, 31, 15, 10, 13, 7, 8, 9, 18, 38, 32, 58, 16, 50, 51, 34, 43, 65, 66, 69, 67, 68, 64, 4, 5, 1, 14, 3, 57, 30, 6, 33, 60, 49, 46, 54, 56, 40, 41, 44, 59, 19, 2, 17, 42, 29, 26, 53, 25, 27, 28, 20, 45, 48, 55],
            [52, 63, 61, 62, 70, 36, 24, 23, 47, 22, 86, 35, 39, 89, 73, 31, 74, 15, 10, 13, 8, 81, 7, 75, 18, 38, 83, 32, 79, 80, 58, 16, 50, 51, 34, 66, 69, 91, 71, 4, 1, 72, 14, 87, 57, 6, 33, 60, 88, 54, 82, 85, 41, 44, 59, 76, 42, 2, 17, 29, 53, 25, 78, 27, 28, 84, 90, 77, 20, 45, 55]
        ];

        $query = [1, 2, 70, 4, 71, 72, 73, 6, 7, 8, 13, 14, 15, 92, 74, 16, 17, 75, 20, 22, 24, 77, 25, 78, 27, 28, 29, 30, 79, 80, 32, 34, 81, 82, 35, 36, 83, 38, 39, 41, 42, 84, 44, 86, 47, 87, 93, 50, 51, 94, 88, 52, 53, 54, 55, 57, 58, 89, 90, 59, 60, 61, 63, 91, 66, 67, 69];

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
                $bits[$key] = array_values($bit['ids']);
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

//        print_r($bits);

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

        print_r($bitsBondsArray);

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
                echo "PUSH BOTH END\n";
                unset($bitsBondsArray[$maxAIndex], $bitsBondsArray[$maxBIndex]);
            } else if ($bPart === 0) {
                array_unshift($routeParts, $maxAIndex);
                echo "PUSH $maxAIndex TO START\n";
                unset($bitsBondsArray[$maxAIndex]);
            } else if ($aPart === count($routeParts) - 1) {
                array_push($routeParts, $maxBIndex);
                echo "PUSH $maxBIndex TO END\n";
                unset($bitsBondsArray[$maxBIndex]);
            } else if ($aPart !== false && $bPart == false) {
                echo "PUSH $maxBIndex AFTER $maxAIndex\n";
                array_splice($routeParts, $aPart + 1, 0, $maxBIndex);
                unset($bitsBondsArray[$maxBIndex]);
            } else if ($bPart !== false && $aPart == false) {
                echo "PUSH $maxAIndex BEFORE $maxBIndex\n";
                array_splice($routeParts, $bPart, 0, $maxAIndex);
                unset($bitsBondsArray[$maxAIndex]);
            } else {
                array_push($routeParts, $maxBIndex);
                echo "PUSH IDK\n";
                unset($bitsBondsArray[$maxBIndex]);
            }

            echo join(',', array_reduce(array_map(function ($part) use ($bits) {
                    return $bits[$part];
                }, $routeParts), function ($accumulator, $current) {
                    array_push($accumulator, ...$current);
                    return $accumulator;
                }, [])) . "\n\n";

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
