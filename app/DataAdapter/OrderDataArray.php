<?php


namespace App\DataAdapter;


abstract class OrderDataArray
{
    public const TYPE = 'type';
    public const FULL_ADDRESS = 'address';
    public const CITY = 'city';
    public const STREET = 'street';
    public const STREET_NUMBER = 'street_number';
    public const FLAT_NUMBER = 'flat_number';
    public const FLOOR = 'floor';
    public const CLIENT_NAME = 'client_name';
    public const DELIVERY_HOURS = 'delivery_hours';
    public const PHONE = 'phone';
    public const COMMENT = 'comment';
    public const AMOUNT = 'amount';
    public const CODE = 'code';
    public const ADDRESS_HASH = 'address_hash';
    public const ADDRESS_ID = 'address_id';
}
