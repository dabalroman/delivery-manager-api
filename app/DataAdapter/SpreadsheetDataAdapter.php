<?php


namespace App\DataAdapter;

use App\Address;
use App\Batch;
use App\GMaps_API\GeocodeService;
use App\Http\Controllers\Controller;
use App\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;

//Allow long execution time needed for geocoding requests
ini_set('max_execution_time', 300);

abstract class SpreadsheetDataAdapter extends Controller
{
    protected $filename;
    protected $path = null;
    protected $deliveryDate = null;
    protected $newAddresses = 0;
    protected $knownAddresses = 0;
    protected $loadedOrdersAmount = 0;
    protected $batchId = null;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Retrieve data from file and return as standard objects array
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
     * Verify if file exists and then grab all data
     * @param string $filename
     * @param array $data
     * @throws Exception
     */
    abstract protected function loadData(string $filename, array &$data);

    /**
     * Trim data like headers
     * @param $data
     */
    abstract protected function trimData(&$data);

    /**
     * Add keys to array
     * @param $data
     */
    abstract protected function addKeys(&$data);

    /**
     * Standardize every field of incoming data
     * @param array $data
     */
    abstract protected function standardizeData(array &$data);

    /**
     * Combine similar data into one object
     * @param array $data
     */
    abstract protected function combineSameAddresses(array &$data);

    /**
     * Split address into street, st. number and flat number
     * @param array $data
     */
    abstract protected function organizeData(array &$data);

    /**
     * Create hash to easily compare addresses
     * Hashes are created from type and address(city, street, number and flat)
     * @param array $data
     */
    private function createHashes(array &$data)
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
     * Save data to file
     * @param string $filename
     * @param array $data
     */
    abstract protected function saveData(string $filename, array $data);

    /**
     * Push all data to db
     * @param array $data
     */
    private function pushDataToDb(array &$data)
    {
        $this->pushAddressToDb($data);
        $this->pushBatchToDb($data);
        $this->pushOrdersToDb($data);
    }

    /**
     * Push (if needed) address data to db
     * @param array $data
     */
    private function pushAddressToDb(array &$data)
    {
        try {
            foreach ($data as &$orderData) {
                //Look for address hash
                /** @var Address $addressFromDB */
                $addressFromDB = (new Address)->where('id_hash', $orderData['address_hash'])->first();

                if (is_null($addressFromDB)) {
                    //Address don't exist, geocode and push
                    $address = new Address;

                    $address->city = $orderData['city'];
                    $address->street = $orderData['street'];
                    $address->street_number = $orderData['street_number'];
                    $address->flat_number = $orderData['flat_number'];
                    $address->floor = $orderData['floor'];
                    $address->geo_cord = GeocodeService::getGeocode($orderData['city'] . ', ' . $orderData['street'] . ' ' . $orderData['street_number']);
                    $address->client_name = $orderData['client_name'];
                    $address->delivery_hours = $orderData['delivery_hours'];
                    $address->phone = $orderData['phone'];
                    $address->code = $orderData['code'];
                    $address->comment = $orderData['comment'];
                    $address->id_hash = $orderData['address_hash'];
                    $address->save();

                    $orderData['address_id'] = $address->id;
                    $this->newAddresses++;
                } else {
                    $orderData['address_id'] = $addressFromDB->id;
                    $this->knownAddresses++;
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Push bash data
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
            $this->loadedOrdersAmount += $orderData['amount'];
        }
    }

    /**
     * Push all orders
     * @param array $data
     */
    private function pushOrdersToDb(array $data)
    {
        try {
            foreach ($data as $orderData) {
                $order = new Order;

                $order->type = $orderData['type'];
                $order->amount = $orderData['amount'];
                $order->address_id = $orderData['address_id'];
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
     * Verify filename and file existence
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
     * @param string $filename
     * @return bool
     */
    private function isAlreadyImported(string $filename)
    {
        return !is_null((new Batch)->firstWhere('source', '=', $filename));
    }
}
