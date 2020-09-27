<?php


namespace App\Pathfinder;


use App\Route;

class Pathfinder
{
    /**
     * Find route by simple pathfinding
     *
     * @param int[] $addressesIds  Addresses ids in queried route
     * @param int[] $ignoredRoutes Routes ids that should be ignored during pathfinding
     * @return int[] Ordered addresses ids
     */
    public static function simpleRoute(array $addressesIds, $ignoredRoutes = []): array
    {
        $routes = Route::whereNotIn('id', $ignoredRoutes)
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->map(function (Route $route) {
                return explode(',', $route->addresses_ids);
            })
            ->toArray();

        self::statisticalPathfinding($routes, $addressesIds);

        return $addressesIds;
    }

    /**
     * @param int[][] $routes Routes as arrays of ids
     * @param int[]   $query  Addresses ids in queried route
     */
    private static function statisticalPathfinding(array $routes, array &$query)
    {
        //Create distance map
        $distanceMap = [];
        foreach ($routes as $route) {
            foreach ($route as $key => $id) {
                if (!isset($distanceMap[$id])) {
                    $distanceMap[$id] = [
                        'distance' => ($key / count($route)),
                        'amount' => 1
                    ];
                } else {
                    $distanceMap[$id]['distance'] += $key / count($route);
                    $distanceMap[$id]['amount']++;
                }
            }
        }

        $distanceMap = array_map(function ($record) {
            return $record['distance'] / $record['amount'];
        }, $distanceMap);

        //Sort query by distance map
        usort($query, function ($a, $b) use ($distanceMap) {
            if (isset($distanceMap[$a]) && isset($distanceMap[$b])) {
                return ($distanceMap[$a] <= $distanceMap[$b]) ? -1 : 1;
            } else {
                if (isset($distanceMap[$a])) {
                    return -1;
                } else if (isset($distanceMap[$b])) {
                    return 1;
                } else {
                    return -1;
                }
            }
        });
    }
}
