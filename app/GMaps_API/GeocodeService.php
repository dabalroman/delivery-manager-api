<?php


namespace App\GMaps_API;

use App\GeocodeCache;
use Exception;
use Illuminate\Support\Facades\Http;

class GeocodeService
{
    /**
     * @param string $address
     * @return mixed
     */
    public static function getGeocode($address)
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
    private static function saveToCache($address, $geocode)
    {
        try {
            $cache = new GeocodeCache;
            $cache->key = $address;
            $cache->geocode = $geocode;
            $cache->push();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $address
     * @return bool
     */
    private static function readFromCache($address)
    {
        return (new GeocodeCache)->where('key', $address)->first()['geocode'] ?? false;
    }
}
