<?php

namespace App\Http\Controllers;

use App\GMaps_API\GeocodeService;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GeocodeController extends Controller
{
    use ApiResponser;

    /**
     * @return JsonResponse
     */
    public function resolve()
    {
        $data = GeocodeService::getGeocode("Polska, Sosnowiec, Dietla 7C");

        return $this->successResponse($data, Response::HTTP_OK)->header('Content-Type', 'text/json');
    }
}
