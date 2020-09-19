<?php


namespace App\DataAdapter\Goodspeed;


use App\DataAdapter\OrderDataArray;
use App\DataAdapter\SpreadsheetDataAdapter;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Goodspeed xlsx files adapter
 *
 * @package App\DataAdapter\Goodspeed
 */
class GoodspeedSpreadSheetAdapter extends SpreadsheetDataAdapter
{
    /**
     * Headers from xlsx file
     *
     * @var string[]
     */
    private array $headers = [
        'klient', 'Adres', 'Kod_miasto', 'Imię i nazwisko', 'Godziny', 'Telefon', 'Uwagi', 'Ilość', 'region'
    ];

    /**
     * @inheritDoc
     */
    protected function loadData(string $filename, array &$data)
    {
        try {
            $spreadsheet = IOFactory::load($filename);
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
     * @param array $data
     * @param array $headers
     * @throws Exception
     */
    protected function verifyHeaders(array $data, array $headers)
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
        //Remove headers row
        array_shift($data);

        //Remove last column
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
                OrderDataArray::TYPE => $orderData[0],
                OrderDataArray::FULL_ADDRESS => $orderData[1],
                OrderDataArray::CITY => $orderData[2],
                OrderDataArray::STREET => null,
                OrderDataArray::STREET_NUMBER => null,
                OrderDataArray::FLAT_NUMBER => null,
                OrderDataArray::FLOOR => null,
                OrderDataArray::CLIENT_NAME => $orderData[3],
                OrderDataArray::DELIVERY_HOURS => $orderData[4],
                OrderDataArray::PHONE => $orderData[5],
                OrderDataArray::COMMENT => $orderData[6],
                OrderDataArray::AMOUNT => $orderData[7],
                OrderDataArray::CODE => null,
                OrderDataArray::ADDRESS_HASH => null,
                OrderDataArray::ADDRESS_ID => null
            ];
        }
    }

    /**
     * @inheritDoc
     */
    protected function standardizeData(array &$data)
    {
        foreach ($data as &$orderData) {
            $orderData[OrderDataArray::TYPE] = trim($orderData[OrderDataArray::TYPE]);
            $orderData[OrderDataArray::FULL_ADDRESS] =
                mb_convert_case(trim($orderData[OrderDataArray::FULL_ADDRESS]), MB_CASE_TITLE);
            $orderData[OrderDataArray::CITY] =
                mb_convert_case(trim($orderData[OrderDataArray::CITY]), MB_CASE_TITLE);
            $orderData[OrderDataArray::DELIVERY_HOURS] = $orderData[OrderDataArray::DELIVERY_HOURS] ?? null;
            $orderData[OrderDataArray::CLIENT_NAME] =
                mb_convert_case(trim($orderData[OrderDataArray::CLIENT_NAME]), MB_CASE_TITLE);
            $orderData[OrderDataArray::COMMENT] = trim($orderData[OrderDataArray::COMMENT]);
        }

        $this->spiltAddress($data);
    }

    /**
     * @inheritDoc
     */
    protected function combineSameAddresses(array &$data)
    {
        //Data is ordered alphabetically by address (street + number)
        $initCount = count($data);
        for ($i = 1; $i < $initCount; $i++) {
            if ($data[$i][OrderDataArray::STREET] == $data[$i - 1][OrderDataArray::STREET]
                && $data[$i][OrderDataArray::STREET_NUMBER] == $data[$i - 1][OrderDataArray::STREET_NUMBER]
                && $data[$i][OrderDataArray::FLAT_NUMBER] == $data[$i - 1][OrderDataArray::FLAT_NUMBER]
                && $data[$i][OrderDataArray::CITY] == $data[$i - 1][OrderDataArray::CITY]
            ) {
                $data[$i][OrderDataArray::AMOUNT] += $data[$i - 1][OrderDataArray::AMOUNT];
                unset($data[$i - 1]);
            }
        }

        //Reindex array
        $data = array_values($data);
    }

    /**
     * @inheritDoc
     */
    protected function organizeData(array &$data)
    {
        $this->findKey($data);
    }

    /**
     * @param $data
     */
    protected function spiltAddress(&$data)
    {
        foreach ($data as &$orderData) {
            //Match address like Aleja bielska 141/10
            preg_match(
                '/^([a-ząćęłńóśżźĄĆĘŁŃÓŚŻŹ.\s]+),?\s(\S+)\/([\S\-]*)/i',
                $orderData[OrderDataArray::FULL_ADDRESS],
                $split,
                PREG_UNMATCHED_AS_NULL
            );

            if (!count($split)) {
                //Match address like Nowa 69 M10
                preg_match(
                    '/^([a-ząćęłńóśżźĄĆĘŁŃÓŚŻŹ.\s]+),?\s(\S+)\sM([\w\d]*)/i',
                    $orderData[OrderDataArray::FULL_ADDRESS],
                    $split,
                    PREG_UNMATCHED_AS_NULL
                );
            }

            if (isset($split[3]) && ($split[3] == '-' || $split[3] == '')) {
                $split[3] = null;
            }

            $orderData[OrderDataArray::STREET] = $split[1] ?? null;
            $orderData[OrderDataArray::STREET_NUMBER] = $split[2] ?? null;
            $orderData[OrderDataArray::FLAT_NUMBER] = $split[3] ?? null;
        }
    }

    /**
     * @param $data
     */
    protected function findKey(&$data)
    {
        foreach ($data as &$orderData) {
            $orderData[OrderDataArray::COMMENT] = str_replace('kluczyk', 'klucz', $orderData[OrderDataArray::COMMENT]);
            preg_match(
                '/(?:[*#]\d+[*#])|(?:\d+\s*(?:klucz)\s*\d+)|(?:\d+\*\d+)|(?:\d+#)/i',
                $orderData[OrderDataArray::COMMENT],
                $split,
                PREG_UNMATCHED_AS_NULL
            );
            $orderData[OrderDataArray::CODE] = $split[0] ?? null;
        }
    }

    /**
     * @inheritDoc
     */
    protected function saveData(string $filename, array $data)
    {
        $f = fopen(__DIR__ . '/../../../storage/spreadsheets/' . $filename . '.json', 'w');
        fwrite($f, json_encode($data));
        fclose($f);
    }
}
