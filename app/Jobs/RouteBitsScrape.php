<?php

namespace App\Jobs;

use App\Address;
use App\GMaps_API\RouteBitsService;
use App\Traits\ApiLogger;
use Exception;

class RouteBitsScrape extends Job
{
    use ApiLogger;

    /**
     * @var int[]
     */
    protected array $route;

    /**
     * RouteBitsScrape constructor.
     *
     * @param int[] $route Route containing addresses ids
     */
    public function __construct(array $route)
    {
        $this->route = $route;
    }

    public function handle()
    {
        $this->logInfo("Route Bits Scrape job started.");
        $dateTime = microtime(true);

        for ($i = 0; $i < count($this->route) - 1; $i++) {
            $start = (new Address)->find($this->route[$i])->geo_cord;
            $end = (new Address)->find($this->route[$i + 1])->geo_cord;

            try {
                RouteBitsService::getRouteBit($start, $end);
            } catch (Exception $e) {
                $this->logError($e);
            }
        }

        $taskDuration = round(microtime(true) - $dateTime, 2);
        $this->logInfo("Route Bits Scrape job done in $taskDuration sec.");
    }
}
