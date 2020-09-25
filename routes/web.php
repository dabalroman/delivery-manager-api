<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

$router->get('xls/{filename}', 'DataCollector@getDataFromXls');

$router->get('resolveGeocode', 'GeocodeController@resolve');

$router->get('batch/{batchId}', 'BatchController@get');
$router->get('batch/list/{userId}', 'BatchController@list');

$router->get('route/{routeId}', 'RouteController@get');
$router->post('route', 'RouteController@post');
$router->put('route/{routeId}', 'RouteController@put');
$router->delete('route/{routeId}', 'RouteController@delete');

$router->get('route/bit/{start}/{end}', 'RouteBitsController@get');
$router->get('route/bit/{addressPair}', 'RouteBitsController@getByAddressPair');

$router->get('address/{addressId}', 'AddressController@get');
$router->put('address/{addressId}', 'AddressController@put');

$router->get('/', function () use ($router) {
    return $router->app->version();
});
