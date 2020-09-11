<?php

namespace App\Http\Controllers;

use App\Address;
use App\Route;
use App\Traits\ApiLogger;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    use ApiResponser;
    use ApiLogger;

    public function post(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'addresses_ids' => 'required|string|regex:/^(\d+,)*(\d+)$/i',
            'batch_id' => 'required|integer|exists:import_batch,id',
            'courier_id' => 'integer|exists:courier,id',
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), $params);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $addresses = $request->input('addresses_ids');
            $addresses_array = explode(',', $addresses);
            $batch_id = $request->input('batch_id');
            $courier_id = $request->input('courier_id');

            //Check if addresses_ids are valid
            if ((new Address)->whereIn('id', $addresses_array)->count() != count($addresses_array)) {
                throw new Exception('Wrong address id!');
            }

            sort($addresses_array);
            $addresses_sorted = join(',', $addresses_array);

            //Save data
            $route = new Route;
            $route->addresses_ids = $addresses;
            $route->id_hash = md5($addresses_sorted);
            $route->routed_hash = md5($addresses);
            $route->courier_id = $courier_id;
            $route->batch_id = $batch_id;
            $route->push();

            $data = [
                'route_id' => $route->id,
                'batch_id' => $route->batch_id,
                'courier_id' => $route->courier_id
            ];

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }

    public function put(Request $request, $routeID)
    {
        $params = $request->all();
        $params['route_id'] = $routeID;

        $validator = Validator::make($params, [
            'route_id' => 'required|integer|exists:route,id',
            'addresses_ids' => 'string|regex:/^(\d+,)*(\d+)$/i',
            'batch_id' => 'integer|exists:import_batch,id',
            'courier_id' => 'integer|exists:courier,id',
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), $params);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var Route $route */
            $route = (new Route)->find($routeID);

            if (isset($params['addresses_ids'])) {

                $addresses = $params['addresses_ids'];
                $addresses_array = explode(',', $addresses);

                //Check if addresses_ids are valid
                if ((new Address)->whereIn('id', $addresses_array)->count() != count($addresses_array)) {
                    throw new Exception('Wrong address id!');
                }

                sort($addresses_array);
                $addresses_sorted = join(',', $addresses_array);

                $route->addresses_ids = $addresses;
                $route->id_hash = md5($addresses_sorted);
                $route->routed_hash = md5($addresses);
            }

            if (isset($params['batch_id'])) {
                $route->batch_id = intval($params['batch_id']);
            }

            if (isset($params['courier_id'])) {
                $route->courier_id = intval($params['courier_id']);
            }

            $route->push();

            $data = [
                'route_id' => $route->id,
                'addresses_ids' => $route->addresses_ids,
                'batch_id' => $route->batch_id,
                'courier_id' => $route->courier_id
            ];

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }

    public function get($routeID)
    {
        $params = ['route_id' => $routeID];

        $validator = Validator::make($params, [
            'route_id' => 'required|integer|exists:route,id'
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), $params);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var Route $route */
            $route = (new Route)->find($routeID);

            $data = [
                'route_id' => $route->id,
                'addresses_ids' => $route->addresses_ids,
                'batch_id' => $route->batch_id,
                'courier_id' => $route->courier_id
            ];

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }

    public function delete($routeID)
    {
        $params = ['route_id' => $routeID];

        $validator = Validator::make($params, [
            'route_id' => 'required|integer|exists:route,id'
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), $params);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var Route $route */
            $route = (new Route)->find($routeID);

            $data = [
                'route_id' => $route->id,
            ];

            $route->delete();
        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
