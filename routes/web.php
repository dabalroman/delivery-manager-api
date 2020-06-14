<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

$router->get('/xls/{filename}', 'DataCollector@getDataFromXls');

$router->get('batch/{batchID}', 'BatchController@get');

$router->get('/', function () use ($router) {
    return $router->app->version();
});
