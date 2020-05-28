<?php


namespace App\DataAdapter;

use App\Address;
use App\Http\Controllers\Controller;
use Exception;

abstract class DataAdapter extends Controller
{
    protected $filename;

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
        $data = [];
        $this->loadData($this->filename, $data);
        $this->trimData($data);
        $this->addKeys($data);
        $this->standardizeData($data);
        $this->combineSameAddresses($data);
        $this->spiltAddressLines($data);
        $this->createHashes($data);
        $this->pushDataToDb($data);
        $this->saveData($this->filename, $data);
        return $data;
    }

    /**
     * Verify if file exists and then grab all data
     * @param $filename
     * @param $data
     * @throws Exception
     */
    abstract protected function loadData($filename, &$data);

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
    abstract protected function standardizeData(&$data);

    /**
     * Combine similar data into one object
     * @param array $data
     */
    abstract protected function combineSameAddresses(&$data);

    /**
     * Split address into street, st. number and flat number
     * @param array $data
     */
    abstract protected function spiltAddressLines(&$data);

    /**
     * Create hash to easily compare addresses
     * Hashes are created from type and address(city, street, number and flat)
     * @param array $data
     */
    abstract protected function createHashes(&$data);

    /**
     * Save data to file
     * @param $filename
     * @param $data
     */
    abstract protected function saveData($filename, &$data);

    private function pushDataToDb(array &$data)
    {
        $this->pushAddressToDb($data);
        $this->pushBatchToDb($data);
        $this->pushOrderToDb($data);
    }

    private function pushAddressToDb(array &$data)
    {
        $loaded = 0;
        $new = 0;

        try {
            foreach ($data as &$orderData) {
                //Look for address hash
                $addressFromDB = (new Address)->where('id_hash', $orderData['address_hash'])->first();

                if (is_null($addressFromDB)) {
                    //Address don't exist, push
                    $address = new Address;

                    $address->city = $orderData['city'];
                    $address->street = $orderData['street'];
                    $address->street_number = $orderData['street_number'];
                    $address->flat_number = $orderData['flat_number'];
                    $address->floor = $orderData['floor'];
                    $address->client_name = $orderData['client_name'];
                    $address->delivery_hours = $orderData['delivery_hours'];
                    $address->phone = $orderData['phone'];
                    $address->code = $orderData['code'];
                    $address->comment = $orderData['comment'];
                    $address->id_hash = $orderData['address_hash'];
                    $address->save();

                    $orderData['address_id'] = $address->id;
                    $new++;
                } else {
                    $orderData['address_id'] = $addressFromDB->id;
                    $loaded++;
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        echo "$loaded / $new\n";
    }

    private function pushBatchToDb(array &$data)
    {
    }

    private function pushOrderToDb(array &$data)
    {
    }
}
