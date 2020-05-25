<?php

namespace App\Http\Controllers;

use App\DataAdapter\Goodspeed\GoodspeedXmlAdapter;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

class DataCollector extends BaseController
{
    use ApiResponser;

    /**
     * @param $filename string
     * @return JsonResponse | Response
     */
    public function getDataFromXls($filename)
    {
        $gs = new GoodspeedXmlAdapter($filename);
        try {
            $data = $gs->retrieveData();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
