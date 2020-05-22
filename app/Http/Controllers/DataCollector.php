<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller as BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class DataCollector extends BaseController
{
    use ApiResponser;

    /**
     * @param $filename
     * @param $data
     * @throws Exception
     */
    private function extractData($filename, &$data){
        //Check file name
        if (!preg_match('/^[A-z0-9]+$/', $filename)) {
            throw new Exception('Wrong filename', Response::HTTP_NOT_ACCEPTABLE);
        }

        $path = __DIR__ . '/../../../storage/spreadsheets/' . $filename;

        //Check if file exists
        if (file_exists($path . '.xls')) {
            $path .= '.xls';
        } else if (file_exists($path . '.xlsx')) {
            $path .= '.xlsx';
        } else {
            throw new Exception('File not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $spreadsheet = IOFactory::load($path);
            $data = $spreadsheet->getActiveSheet()->toArray(null, false, false, false);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $filename string
     * @return JsonResponse | Response
     */
    public function getDataFromXls($filename)
    {
        //Todo file selection logic
        //Got the right file

        $data = [];
        try {
            $this->extractData($filename, $data);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getMessage());
        }

        return $this->successResponse($data);
    }
}
