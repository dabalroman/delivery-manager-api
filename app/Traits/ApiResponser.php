<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait ApiResponser{
    /**
     * Build success response
     * @param $data
     * @param int $code
     * @return Response
     */
    public function successResponse($data, $code = Response::HTTP_OK){
        return response($data, $code)->header('Content-Type','application/json');
    }

    public function validResponse($data, $code = Response::HTTP_OK){
        return response()->json(['data' => $data], $code);
    }

    public function errorResponse($message, $code){
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    /**
     * @param $message
     * @param $code
     * @return Response
     */
    public function errorMessage($message, $code){
        return response($message, $code)->header('Content-Type','application/json');
    }
}
