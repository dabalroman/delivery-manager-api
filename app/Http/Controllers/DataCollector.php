<?php

namespace App\Http\Controllers;

use App\DataAdapter\Goodspeed\GoodspeedSpreadSheetAdapter;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DataCollector extends Controller
{
    use ApiResponser;

    /**
     * @param $filename string
     * @return JsonResponse | Response
     */
    public function getDataFromXls($filename)
    {
        try {
            $gs = new GoodspeedSpreadSheetAdapter($filename);
            $data = $gs->retrieveData();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage() . ' ' . $e->getCode(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
