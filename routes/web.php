<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

$router->get('xls/{filename}', 'DataCollector@getDataFromXls');

$router->get('resolveGeocode', 'GeocodeController@resolve');

$router->get('batch/{batchID}', 'BatchController@get');

$router->get('route/{routeID}', 'RouteController@get');
$router->put('route/{routeID}', 'RouteController@put');
$router->post('route', 'RouteController@post');

$router->get('/', function () use ($router) {
    return $router->app->version();
});
