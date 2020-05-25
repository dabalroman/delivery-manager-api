<?php


namespace App\DataAdapter;


use Exception;

abstract class DataAdapter
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
}
