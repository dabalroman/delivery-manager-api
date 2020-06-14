<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;

class BatchController extends BaseController
{
    use ApiResponser;

    /**
     * @param $batchID
     * @return JsonResponse | Response
     */
    public function get($batchID)
    {
        $batch = (new Batch)->where('id', $batchID)->first();

        $data['batch_id'] = $batch->id;
        $data['delivery_date'] = $batch->delivery_date;
        $data['new_addresses_amount'] = $batch->new_addresses_amount;
        $data['known_addresses_amount'] = $batch->known_addresses_amount;

        $data['orders'] = DB::table('order')
            ->select('order.id', 'order.type', 'order.amount', 'address.city', 'address.street', 'address.street_number', 'address.flat_number')
            ->join('address', 'order.address_id', '=', 'address.id')
            ->where('order.batch_id', '=', $batchID)
            ->orderBy('address.street')
            ->get();

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
