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
            $this->verifyHeaders($data, ['klient', 'Adres', 'Kod_miasto', 'Imię i nazwisko', 'Godziny', 'Telefon', 'Uwagi', 'Ilość', 'region']);
            $this->trimData($data);
            $this->addKeys($data);
            $this->combineSameAddresses($data);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getMessage());
        }

        return $this->successResponse($data);
    }

    /**
     * Verify if file exists and then grab all data
     * @param $filename
     * @param $data
     * @throws Exception
     */
    private function extractData($filename, &$data)
    {
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
     * Verify if file headers match template //Todo json templates
     * @param array $data
     * @param array $array
     * @throws Exception
     */
    private function verifyHeaders(array &$data, array $array)
    {
        foreach ($data[0] as $key => $field) {
            if ($array[$key] != $field) {
                throw new Exception('Headers don\'t match template', Response::HTTP_NOT_ACCEPTABLE);
            }
        }
    }

    /**
     * Trim headers and last row
     * @param $data
     */
    private function trimData(&$data)
    {
        array_shift($data);

        foreach ($data as &$orderData) {
            array_pop($orderData);
        }
    }

    private function addKeys(array &$data)
    {
        foreach ($data as &$orderData) {
            $orderData = [
                'type' => $orderData[0],
                'address' => $orderData[1],
                'city' => $orderData[2],
                'client' => $orderData[3],
                'hours' => $orderData[4],
                'phone' => $orderData[5],
                'comment' => $orderData[6],
                'amount' => $orderData[7]
            ];
        }
    }

    /**
     * Combine similar data into one object
     * @param array $data
     */
    private function combineSameAddresses(array &$data)
    {
        //Data is ordered alphabetically by address (street + number)
        $initCount = count($data);
        for ($i = 1; $i < $initCount; $i++) {
            if ($data[$i]['type'] == $data[$i - 1]['type']
                && $data[$i]['address'] == $data[$i - 1]['address']
                && $data[$i]['city'] == $data[$i - 1]['city']
                && ($data[$i]['client'] == $data[$i - 1]['client'] || $data[$i]['phone'] == $data[$i - 1]['phone'])
            ) {
                $data[$i]['amount'] += $data[$i - 1]['amount'];
                unset($data[$i - 1]);
            }
        }

        //Reindex array
        $data = array_values($data);
    }


}
