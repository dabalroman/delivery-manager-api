<?php

namespace App\Http\Controllers;

use App\DataAdapter\Goodspeed\GoodspeedSpreadSheetAdapter;
use App\Traits\ApiLogger;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DataCollector extends Controller
{
    use ApiResponser;
    use ApiLogger;

    /**
     * @param string $filename
     * @return JsonResponse | Response
     */
    public function getDataFromXls(string $filename)
    {
        try {
            $spreadSheetAdapter = new GoodspeedSpreadSheetAdapter($filename);
            $data = $spreadSheetAdapter->retrieveData();
            $this->logInfo('Imported new batch.', ['records' => count($data)]);
        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage() . ' ' . $e->getCode(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
