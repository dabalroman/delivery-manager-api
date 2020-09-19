<?php


namespace App\Pathfinder;


use App\Route;

class Pathfinder
{
    private int $batchId;

    /**
     * Pathfinder constructor.
     *
     * @param $batchId
     */
    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
     * Create route without without any optimizations
     *
     * @param int[] $addressesIds
     * @return Route
     */
    public function simpleRoute(array $addressesIds): Route
    {
        $route = new Route();

        $route->batch_id = $this->batchId;
        $route->addresses_ids = join(',', $addressesIds);
        $route->id_hash = Route::createIdHash($addressesIds);
        $route->routed_hash = Route::createRoutedHash($addressesIds);

        return $route;
    }
}
