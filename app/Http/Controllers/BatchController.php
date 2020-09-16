<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Route;
use App\Traits\ApiLogger;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BatchController extends Controller
{
    use ApiResponser;
    use ApiLogger;

    /**
     * @param $batchID
     * @return JsonResponse | Response
     */
    public function get($batchID)
    {
        $validator = Validator::make(['batch_id' => $batchID], [
            'batch_id' => 'required|integer|exists:import_batch,id',
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), ['batch_id' => $batchID]);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        $data = [];

        try {
            $batch = (new Batch)->findOrFail($batchID);

            $data['batch_id'] = $batch->id;
            $data['delivery_date'] = $batch->delivery_date;
            $data['new_addresses_amount'] = $batch->new_addresses_amount;
            $data['known_addresses_amount'] = $batch->known_addresses_amount;
            $data['orders_amount'] = $batch->orders_amount;

            $data['orders'] = DB::table('order')
                ->select(
                    'order.id', 'order.type', 'order.amount', 'order.address_id',
                    'address.city', 'address.street', 'address.street_number', 'address.flat_number', 'address.floor',
                    'address.comment', 'address.code', 'address.phone', 'address.geo_cord'
                )
                ->join('address', 'order.address_id', '=', 'address.id')
                ->where('order.batch_id', '=', $batchID)
                ->orderBy('address.street')
                ->get();

            $data['routes'] = (new Route)->where('batch_id', $batch->id)->get();

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
