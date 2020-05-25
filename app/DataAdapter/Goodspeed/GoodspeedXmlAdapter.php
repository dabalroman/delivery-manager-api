<?php


namespace App\DataAdapter\Goodspeed;


use App\DataAdapter\DataAdapter;
use Exception;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GoodspeedXmlAdapter extends DataAdapter
{
    private $headers = ['klient', 'Adres', 'Kod_miasto', 'Imię i nazwisko', 'Godziny', 'Telefon', 'Uwagi', 'Ilość', 'region'];

    /**
     * @inheritDoc
     */
    protected function loadData($filename, &$data)
    {
        //Check file name
        if (!preg_match('/^[A-z0-9]+$/', $filename)) {
            throw new \PhpOffice\PhpSpreadsheet\Reader\Exception('Wrong filename', Response::HTTP_NOT_ACCEPTABLE);
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

        $this->verifyHeaders($data, $this->headers);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function verifyHeaders(&$data, $headers)
    {
        foreach ($data[0] as $key => $field) {
            if ($headers[$key] != $field) {
                throw new Exception('Headers don\'t match template', Response::HTTP_NOT_ACCEPTABLE);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function trimData(&$data)
    {
        array_shift($data);

        foreach ($data as &$orderData) {
            array_pop($orderData);
        }
    }

    /**
     * @inheritDoc
     */
    protected function addKeys(&$data)
    {
        foreach ($data as &$orderData) {
            $orderData = [
                'type' => $orderData[0],
                'address' => $orderData[1],
                'city' => $orderData[2],
                'street' => null,
                'street_number' => null,
                'flat_number' => null,
                'client' => $orderData[3],
                'hours' => $orderData[4],
                'phone' => $orderData[5],
                'comment' => $orderData[6],
                'amount' => $orderData[7],
                'hash' => null
            ];
        }
    }

    /**
     * @inheritDoc
     */
    protected function standardizeData(&$data)
    {
        foreach ($data as &$orderData) {
            $orderData['type'] = trim($orderData['type']);
            $orderData['address'] = mb_convert_case(trim($orderData['address']), MB_CASE_TITLE);
            $orderData['city'] = mb_convert_case(trim($orderData['city']), MB_CASE_TITLE);
            $orderData['hours'] = $orderData['hours'] ?? null;
            $orderData['client'] = mb_convert_case(trim($orderData['client']), MB_CASE_TITLE);
            $orderData['comment'] = trim($orderData['comment']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function combineSameAddresses(&$data)
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

    /**
     * @inheritDoc
     */
    protected function spiltAddressLines(&$data)
    {
        foreach ($data as &$orderData) {
            preg_match('/^([a-ząćęłńóśżźĄĆĘŁŃÓŚŻŹ.\s]+)\s(\S+)\/([\S\-]*)/i', $orderData['address'], $split, PREG_UNMATCHED_AS_NULL);
            $orderData['street'] = $split[1] ?? null;
            $orderData['street_number'] = $split[2] ?? null;
            $orderData['flat_number'] = $split[3] ?? null;
        }
    }

    /**
     * @inheritDoc
     */
    protected function createHashes(&$data)
    {
        foreach ($data as &$orderData) {
            $orderData['hash'] = md5(
                $orderData['type'] . '#'
                . $orderData['city'] . '#'
                . $orderData['street'] . '#'
                . $orderData['street_number'] . '#'
                . $orderData['flat_number']
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function saveData($filename, &$data)
    {
        $f = fopen(__DIR__ . '/../../../storage/spreadsheets/' . $filename . '.json', 'w');
        fwrite($f, json_encode($data));
        fclose($f);
    }
}
