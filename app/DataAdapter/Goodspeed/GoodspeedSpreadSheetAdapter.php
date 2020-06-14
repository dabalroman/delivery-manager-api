<?php


namespace App\DataAdapter\Goodspeed;


use App\DataAdapter\SpreadsheetDataAdapter;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GoodspeedSpreadSheetAdapter extends SpreadsheetDataAdapter
{
    private $headers = ['klient', 'Adres', 'Kod_miasto', 'Imię i nazwisko', 'Godziny', 'Telefon', 'Uwagi', 'Ilość', 'region'];

    /**
     * @inheritDoc
     */
    protected function loadData($path, &$data)
    {
        try {
            $spreadsheet = IOFactory::load($path);
            $data = $spreadsheet->getActiveSheet()->toArray(null, false, false, false);

            //Get day and month date from format 'NameSurname DD.MM'
            $timezone = new DateTimeZone('Europe/Warsaw');
            $rawDeliveryDate = substr($spreadsheet->getSheetNames()[0], -5, 5) . '.' . date('Y');
            $this->deliveryDate = DateTime::createFromFormat('d.m.Y', trim($rawDeliveryDate), $timezone);
            $now = new DateTime('now', $timezone);

            //New year overflow
            if ($this->deliveryDate->format('d.m') == '01.01' && $now->format('d.m') == '31.12') {
                $this->deliveryDate->add(new DateInterval('P1Y'));
            }

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
                'floor' => null,
                'client_name' => $orderData[3],
                'delivery_hours' => $orderData[4],
                'phone' => $orderData[5],
                'comment' => $orderData[6],
                'amount' => $orderData[7],
                'code' => null,
                'address_hash' => null,
                'address_id' => null
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
            $orderData['delivery_hours'] = $orderData['delivery_hours'] ?? null;
            $orderData['client_name'] = mb_convert_case(trim($orderData['client_name']), MB_CASE_TITLE);
            $orderData['comment'] = trim($orderData['comment']);
        }

        $this->spiltAddress($data);
    }

    /**
     * @inheritDoc
     */
    protected function combineSameAddresses(&$data)
    {
        //Data is ordered alphabetically by address (street + number)
        $initCount = count($data);
        for ($i = 1; $i < $initCount; $i++) {
            if ($data[$i]['street'] == $data[$i - 1]['street']
                && $data[$i]['street_number'] == $data[$i - 1]['street_number']
                && $data[$i]['flat_number'] == $data[$i - 1]['flat_number']
                && $data[$i]['city'] == $data[$i - 1]['city']
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
    protected function organizeData(&$data)
    {
        $this->findKey($data);
    }

    protected function spiltAddress(&$data)
    {
        foreach ($data as &$orderData) {
            //Match address like Aleja bielska 141/10
            preg_match('/^([a-ząćęłńóśżźĄĆĘŁŃÓŚŻŹ.\s]+),?\s(\S+)\/([\S\-]*)/i', $orderData['address'], $split, PREG_UNMATCHED_AS_NULL);

            if (!count($split)) {
                //Match address like Nowa 69 M10
                preg_match('/^([a-ząćęłńóśżźĄĆĘŁŃÓŚŻŹ.\s]+),?\s(\S+)\sM([\w\d]*)/i', $orderData['address'], $split, PREG_UNMATCHED_AS_NULL);
            }

            if (isset($split[3]) && ($split[3] == '-' || $split[3] == '')) {
                $split[3] = null;
            }

            $orderData['street'] = $split[1] ?? null;
            $orderData['street_number'] = $split[2] ?? null;
            $orderData['flat_number'] = $split[3] ?? null;
        }
    }

    protected function findKey(&$data)
    {
        foreach ($data as &$orderData) {
            $orderData['comment'] = str_replace('kluczyk', 'klucz', $orderData['comment']);
            preg_match('/(?:[*#]\d+[*#])|(?:\d+\s*(?:klucz)\s*\d+)|(?:\d+\*\d+)|(?:\d+#)/i', $orderData['comment'], $split, PREG_UNMATCHED_AS_NULL);
            $orderData['code'] = $split[0] ?? null;
        }
    }

    /**
     * @inheritDoc
     */
    protected function createHashes(&$data)
    {
        foreach ($data as &$orderData) {
            $orderData['address_hash'] = md5(
                $orderData['city'] . '#'
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
