<?php

namespace App\Http\Controllers;

use App\Address;
use App\Batch;
use App\Route;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BatchController extends Controller
{
    use ApiResponser;

    /**
     * @param $batchID
     * @return JsonResponse | Response
     */
    public function get($batchID)
    {
        $data = [];

        try {
            $batch = (new Batch)->findOrFail($batchID);

            $data['batch_id'] = $batch->id;
            $data['delivery_date'] = $batch->delivery_date;
            $data['new_addresses_amount'] = $batch->new_addresses_amount;
            $data['known_addresses_amount'] = $batch->known_addresses_amount;
            $data['orders_amount'] = $batch->orders_amount;

            $data['orders'] = DB::table('order')
                ->select('order.id', 'order.type', 'order.amount', 'order.address_id', 'address.city', 'address.street', 'address.street_number', 'address.flat_number', 'address.comment', 'address.code')
                ->join('address', 'order.address_id', '=', 'address.id')
                ->where('order.batch_id', '=', $batchID)
                ->orderBy('address.street')
                ->get();

            $data['routes'] = (new Route)->where('batch_id', $batch->id)->get();

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }

    public function postRoute(Request $request)
    {
        $data = [];

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
