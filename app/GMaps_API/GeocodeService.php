<?php


namespace App\GMaps_API;

use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Provides geocoding via GAPI with local caching
 *
 * @package App\GMaps_API
 */
class GeocodeService
{
    const CACHE_PATH = '\..\..\storage\app\geocodeServiceCache';
    const CACHE_FILENAME = '\cache.json';

    /**
     * @param string $address
     * @return mixed
     */
    public static function getGeocode(string $address)
    {
        $cached = self::readFromCache($address);

        if ($cached) {
            return $cached;
        }

        $url_address = urlencode($address);
        $key = env('GMAPS_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$url_address}&key={$key}";

        $response = Http::get($url);
        $data = json_decode($response->body());

        $geocode = $data->results[0]->geometry->location->lat . ',' . $data->results[0]->geometry->location->lng;
        self::saveToCache($address, $geocode);

        return $geocode;
    }

    /**
     * @param string $address
     * @param string $geocode
     * @return bool
     */
    private static function saveToCache(string $address, string $geocode)
    {
        try {
            $cachePath = realpath(__DIR__ . self::CACHE_PATH . self::CACHE_FILENAME);
            $cache = [];

            if ($cachePath) {
                $cache = json_decode(file_get_contents($cachePath), true);
            }

            $cache[$address] = $geocode;
            file_put_contents(
                realpath(__DIR__ . self::CACHE_PATH) . self::CACHE_FILENAME,
                json_encode($cache, JSON_PRETTY_PRINT)
            );
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $address
     * @return bool|string
     */
    private static function readFromCache(string $address)
    {
        $cachePath = realpath(__DIR__ . self::CACHE_PATH . self::CACHE_FILENAME);

        if (!$cachePath) {
            return false;
        }

        $cache = json_decode(file_get_contents($cachePath), true);

        return $cache[$address] ?? false;
    }
}
