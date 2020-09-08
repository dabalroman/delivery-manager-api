<?php


namespace App\GMaps_API;

use Illuminate\Support\Facades\Http;

class GeocodeService
{
    /**
     * @param string $address
     * @return mixed
     */
    public static function getGeocode($address)
    {
        $address = urlencode($address);
        $key = env('GMAPS_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$key}";

        $response = Http::get($url);
        $data = json_decode($response->body());

        return $data->results[0]->geometry->location->lat . ',' . $data->results[0]->geometry->location->lng;
    }
}
