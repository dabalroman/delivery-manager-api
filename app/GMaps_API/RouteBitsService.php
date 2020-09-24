<?php


namespace App\GMaps_API;

use App\Pathfinder\PolylineObject;
use App\RouteBit;
use Exception;
use Illuminate\Support\Facades\Http;

class RouteBitsService
{
    private const CACHE_PATH = '\..\..\storage\app\routeBitsServiceCache';
    private const CACHE_FILENAME = '\cache.json';


    /**
     * Get polyline as a string
     *
     * @param string $start geocoded location
     * @param string $end   geocoded location
     * @return array stringified polyline ['length' => $length,'polyline' => $polyline]
     * @throws Exception
     */
    public static function getRouteBit(string $start, string $end)
    {
        $cached = self::readFromDb($start, $end);

        if ($cached) {
            return $cached;
        }

        $cached = self::readFromCache($start, $end);

        if ($cached) {
            self::saveToDb($start, $end, $cached['length'], $cached['polyline']);
            return $cached;
        }

        $key = env('GMAPS_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=$start&destination=$end"
            . "&key={$key}&alternatives=false";

        $response = Http::get($url);
        $data = json_decode($response->body(), true);

        $length = $data['routes'][0]['legs'][0]['distance']['value'];

        //Get steps and extract polyline
        $steps = array_reduce($data['routes'][0]['legs'][0]['steps'], function ($accumulator, $current) {
            array_push($accumulator, $current['polyline']['points']);
            return $accumulator;
        }, []);


        //Combine all steps into one polyline
        $polylineObject = new PolylineObject();
        foreach ($steps as $step) {
            $polylineObject->joinAfter(PolylineObject::fromEncoded($step));
        }

        $polylineObject->simplify();
        $polyline = $polylineObject->encode();

        self::saveToDb($start, $end, $length, $polyline);
        self::saveToCache($start, $end, $length, $polyline);

        return [
            'length' => $length,
            'polyline' => $polyline
        ];
    }

    /**
     * @param string $start
     * @param string $end
     * @return array|false
     */
    private static function readFromDb(string $start, string $end)
    {
        /** @var RouteBit $routeBit */
        $routeBit = RouteBit::whereStart($start)->where('end', $end)->first();

        if (!$routeBit) {
            return false;
        }

        return [
            'length' => $routeBit->length,
            'polyline' => $routeBit->polyline
        ];
    }

    /**
     * @param string $start geocoded location
     * @param string $end   geocoded location
     * @return array|false stringified polyline and it's length
     */
    private static function readFromCache(string $start, string $end)
    {
        $cachePath = realpath(__DIR__ . self::CACHE_PATH . self::CACHE_FILENAME);

        if (!$cachePath) {
            return false;
        }

        $cache = json_decode(file_get_contents($cachePath), true);

        return $cache["$start,$end"] ?? false;
    }

    /**
     * @param string $start
     * @param string $end
     * @param int    $length
     * @param string $polyline
     * @return bool
     */
    private static function saveToDb(string $start, string $end, int $length, string $polyline)
    {
        /** @var RouteBit $routeBit */
        $routeBit = (new RouteBit)->firstOrNew(['start' => $start, 'end' => $end]);

        $routeBit->start = $start;
        $routeBit->end = $end;
        $routeBit->length = $length;
        $routeBit->polyline = $polyline;
        $routeBit->push();

        return true;
    }

    /**
     * @param string $start    geocoded location
     * @param string $end      geocoded location
     * @param int    $length   polyline length
     * @param string $polyline stringified polyline
     * @return bool
     */
    private static function saveToCache(string $start, string $end, int $length, string $polyline)
    {
        try {
            $cachePath = realpath(__DIR__ . self::CACHE_PATH . self::CACHE_FILENAME);
            $cache = [];

            if ($cachePath) {
                $cache = json_decode(file_get_contents($cachePath), true);
            }

            $cache["$start,$end"] = [
                'length' => $length,
                'polyline' => $polyline
            ];

            file_put_contents(
                realpath(__DIR__ . self::CACHE_PATH) . self::CACHE_FILENAME,
                json_encode($cache, JSON_PRETTY_PRINT)
            );
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
