<?php


namespace App\DataAdapter;

use App\Address;
use App\Batch;
use App\GMaps_API\GeocodeService;
use App\Http\Controllers\Controller;
use App\Order;
use App\Pathfinder\Pathfinder;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Response;

//Allow long execution time needed for geocoding requests
ini_set('max_execution_time', 300);

abstract class SpreadsheetDataAdapter extends Controller
{
    protected string $filename;
    protected ?string $path = null;
    protected ?DateTime $deliveryDate = null;
    protected ?int $batchId = null;
    protected int $newAddresses = 0;
    protected int $knownAddresses = 0;
    protected int $loadedOrdersAmount = 0;

    /**
     * SpreadsheetDataAdapter constructor.
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Retrieve data from file and return as standard objects array
     *
     * @return array
     * @throws Exception
     */
    public function retrieveData()
    {
        if (!$this->doesFileExists($this->filename)) {
            throw new Exception('File don\'t exists.');
        }

        if ($this->isAlreadyImported($this->filename)) {
            throw new Exception('File already imported.');
        }

        $data = [];
        $this->loadData($this->path, $data);
        $this->trimData($data);
        $this->addKeys($data);
        $this->standardizeData($data);
        $this->combineSameAddresses($data);
        $this->organizeData($data);
        $this->createHashes($data);
        $this->pushDataToDb($data);
        $this->saveData($this->filename, $data);
        return $data;
    }

    /**
     * Verify filename and file existence
     *
     * @param string $filename
     * @return bool
     * @throws Exception
     */
    private function doesFileExists(string $filename)
    {
        //Check file name
        if (!preg_match('/^[A-z0-9]+$/', $filename)) {
            throw new Exception('Wrong filename', Response::HTTP_NOT_ACCEPTABLE);
        }

        $path = __DIR__ . '/../../storage/spreadsheets/' . $filename;

        //Check if file exists
        if (file_exists($path . '.xls')) {
            $path .= '.xls';
        } else if (file_exists($path . '.xlsx')) {
            $path .= '.xlsx';
        } else {
            return false;
        }

        $this->path = $path;
        return true;
    }

    /**
     * Check db for batches that might have imported that file
     *
     * @param string $filename
     * @return bool
     */
    private function isAlreadyImported(string $filename)
    {
        return !is_null((new Batch)->firstWhere('source', '=', $filename));
    }

    /**
     * Verify if file exists and then grab all data
     *
     * @param string $filename
     * @param array  $data
     * @throws Exception
     */
    abstract protected function loadData(string $filename, array &$data);

    /**
     * Trim data like headers
     *
     * @param $data
     */
    abstract protected function trimData(&$data);

    /**
     * Add keys to array
     *
     * Fills fields TYPE, FULL_ADDRESS, CITY, DELIVERY_HOURS, CLIENT_NAME, COMMENT
     *
     * @param $data
     */
    abstract protected function addKeys(&$data);

    /**
     * Standardize every field of incoming data
     *
     * Fills fields STREET, STREET_NUMBER, FLAT_NUMBER
     *
     * @param array $data
     */
    abstract protected function standardizeData(array &$data);

    /**
     * Combine similar address data into one object
     *
     * Fills field AMOUNT
     *
     * @param array $data
     */
    abstract protected function combineSameAddresses(array &$data);

    /**
     * Finds out code from comment
     *
     * Fills field CODE
     *
     * @param array $data
     */
    abstract protected function organizeData(array &$data);

    /**
     * Create hash to easily compare addresses
     * Hashes are created from type and address(city, street, number and flat)
     *
     * Fills field ADDRESS_HASH
     *
     * @param array $data
     */
    private function createHashes(array &$data)
    {
        foreach ($data as &$orderData) {

            $orderData[OrderDataArray::ADDRESS_HASH] = Address::createHash(
                $orderData[OrderDataArray::CITY],
                $orderData[OrderDataArray::STREET],
                $orderData[OrderDataArray::STREET_NUMBER],
                $orderData[OrderDataArray::FLAT_NUMBER]
            );
        }
    }

    /**
     * Push all data to db
     *
     * @param array $data
     */
    private function pushDataToDb(array &$data)
    {
        $this->pushAddressToDb($data);
        $this->pushBatchToDb($data);
        $this->pushOrdersToDb($data);
        $this->createAndPushRouteToDb($data);
    }

    /**
     * Push (if needed) address data to db
     *
     * Fills field ADDRESS_ID
     *
     * @param array $data
     */
    private function pushAddressToDb(array &$data)
    {
        try {
            foreach ($data as &$orderData) {
                //Look for address hash
                /** @var Address $addressFromDB */
                $addressFromDB = (new Address)->where('id_hash', $orderData[OrderDataArray::ADDRESS_HASH])->first();

                if (is_null($addressFromDB)) {
                    //Address don't exist, geocode and push
                    $address = new Address;

                    $address->city = $orderData[OrderDataArray::CITY];
                    $address->street = $orderData[OrderDataArray::STREET];
                    $address->street_number = $orderData[OrderDataArray::STREET_NUMBER];
                    $address->flat_number = $orderData[OrderDataArray::FLAT_NUMBER];
                    $address->floor = $orderData[OrderDataArray::FLOOR];
                    $address->geo_cord = GeocodeService::getGeocode(
                        $orderData[OrderDataArray::CITY] . ', '
                        . $orderData[OrderDataArray::STREET] . ' '
                        . $orderData[OrderDataArray::STREET_NUMBER]
                    );
                    $address->client_name = $orderData[OrderDataArray::CLIENT_NAME];
                    $address->delivery_hours = $orderData[OrderDataArray::DELIVERY_HOURS];
                    $address->phone = $orderData[OrderDataArray::PHONE];
                    $address->code = $orderData[OrderDataArray::CODE];
                    $address->comment = $orderData[OrderDataArray::COMMENT];
                    $address->id_hash = $orderData[OrderDataArray::ADDRESS_HASH];
                    $address->save();

                    $orderData[OrderDataArray::ADDRESS_ID] = $address->id;
                    $this->newAddresses++;
                } else {
                    $orderData[OrderDataArray::ADDRESS_ID] = $addressFromDB->id;
                    $this->knownAddresses++;
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Push bash data
     *
     * @param array $data
     */
    private function pushBatchToDb(array $data)
    {
        $this->countOrdersAmount($data);

        try {
            $batch = new Batch;

            $batch->source = $this->filename;
            $batch->user_id = 1; //TODO: CHANGE TO CURRENT USER
            $batch->delivery_date = $this->deliveryDate;
            $batch->new_addresses_amount = $this->newAddresses;
            $batch->known_addresses_amount = $this->knownAddresses;
            $batch->orders_amount = $this->loadedOrdersAmount;
            $batch->import_date = Carbon::now();

            $batch->save();

            $this->batchId = $batch->id;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param array $data
     */
    private function countOrdersAmount(array $data)
    {
        foreach ($data as $orderData) {
            $this->loadedOrdersAmount += $orderData[OrderDataArray::AMOUNT];
        }
    }

    /**
     * Push all orders
     *
     * @param array $data
     */
    private function pushOrdersToDb(array $data)
    {
        try {
            foreach ($data as $orderData) {
                $order = new Order;

                $order->type = $orderData[OrderDataArray::TYPE];
                $order->amount = $orderData[OrderDataArray::AMOUNT];
                $order->address_id = $orderData[OrderDataArray::ADDRESS_ID];
                $order->batch_id = $this->batchId;

                $order->owner = 1;
                $order->assigned_to = null;

                $order->save();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Creates and saves route
     *
     * @param array $data
     */
    private function createAndPushRouteToDb(array $data)
    {
        try {
            $addressesIds = array_map(
                function ($orderData) {
                    return $orderData[OrderDataArray::ADDRESS_ID];
                },
                $data
            );

            $pathfinder = new Pathfinder($this->batchId);
            $route = $pathfinder->simpleRoute($addressesIds);
            $route->save();

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Save data to file
     *
     * @param string $filename
     * @param array  $data
     */
    abstract protected function saveData(string $filename, array $data);
}
