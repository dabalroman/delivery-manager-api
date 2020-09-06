<?php

namespace App\Http\Controllers;

use App\Address;
use App\Route;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    use ApiResponser;

    public function post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'integer',
            'addresses_ids' => 'required|string|regex:/^(\d+,)*(\d+)$/i',
            'batch_id' => 'required|integer|exists:import_batch,id',
            'courier_id' => 'required|integer|exists:courier,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        $route_id = $request->input('route_id');
        $addresses = $request->input('addresses_ids');
        $addresses_array = explode(',', $addresses);
        $batch_id = $request->input('batch_id');
        $courier_id = $request->input('courier_id');

        try {
            //Check if addresses_ids are valid
            if ((new Address)->whereIn('id', $addresses_array)->count() != count($addresses_array)) {
                throw new Exception('Wrong address id!');
            }

            //Save data
            $route = (new Route)->findOrNew($route_id);
            $route->addresses_ids = $addresses;
            $route->id_hash = md5($addresses);
            sort($addresses_array);
            $route->routed_hash = md5(join(',', $addresses_array));
            $route->courier_id = $courier_id;
            $route->batch_id = $batch_id;
            $route->push();

            $data = [
                'route_id' => $route->id,
                'addresses_ids' => $route->addresses_ids,
                'batch_id' => $route->batch_id,
                'courier_id' => $route->courier_id
            ];

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
