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
     * @param int $batchId
     * @return JsonResponse | Response
     */
    public function get(int $batchId)
    {
        $validator = Validator::make(['batch_id' => $batchId], [
            'batch_id' => 'required|integer|exists:import_batch,id',
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), ['batch_id' => $batchId]);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        $data = [];

        try {
            $batch = (new Batch)->findOrFail($batchId);

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
                ->where('order.batch_id', '=', $batchId)
                ->get();

            $data['route'] = Route::getRouteData((new Route)->where('batch_id', $batch->id)->first()->id);

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }

    /**
     * @param int $userId
     * @return JsonResponse
     */
    public function list(int $userId)
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|integer|exists:user,id',
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), ['user_id' => $userId]);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        $data = [];

        try {
            $data['user_id'] = $userId;
            $data['batches'] = DB::table('import_batch')
                ->select('id', 'delivery_date', 'new_addresses_amount', 'known_addresses_amount', 'orders_amount')
                ->orderBy('delivery_date', 'DESC')
                ->get();

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
