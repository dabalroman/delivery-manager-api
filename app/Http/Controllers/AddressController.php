<?php

namespace App\Http\Controllers;

use App\Address;
use App\Traits\ApiLogger;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    use ApiResponser;
    use ApiLogger;

    /**
     * @param Request $request
     * @param integer $addressId
     * @return JsonResponse
     */
    public function put(Request $request, int $addressId): JsonResponse
    {
        $params = $request->all();
        $params['address_id'] = $addressId;

        $validator = Validator::make($params, [
            'address_id' => 'required|integer|exists:address,id',
            'city' => 'string',
            'street' => 'string',
            'street_number' => 'string',
            'flat_number' => 'string',
            'floor' => 'integer',
            'client_name' => 'string',
            'delivery_hours' => 'string',
            'phone' => 'string',
            'code' => 'string',
            'comment' => 'string',
            'geo_cord' => 'string'
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), $params);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var Address $address */
            $address = (new Address)->find($addressId);
            $hashUpdateNeeded = false;

            if (isset($params['city'])) {
                $address->city = $params['city'];
                $hashUpdateNeeded = true;
            }

            if (isset($params['street'])) {
                $address->street = $params['street'];
                $hashUpdateNeeded = true;
            }

            if (isset($params['street_number'])) {
                $address->street_number = $params['street_number'];
                $hashUpdateNeeded = true;

            }

            if (isset($params['flat_number'])) {
                $address->flat_number = $params['flat_number'];
                $hashUpdateNeeded = true;
            }

            if (isset($params['floor'])) {
                $address->floor = intval($params['floor']);
            }

            if (isset($params['client_name'])) {
                $address->client_name = $params['client_name'];
            }

            if (isset($params['delivery_hours'])) {
                $address->delivery_hours = $params['delivery_hours'];
            }

            if (isset($params['phone'])) {
                $address->phone = $params['phone'];
            }

            if (isset($params['code'])) {
                $address->code = $params['code'];
            }

            if (isset($params['comment'])) {
                $address->comment = $params['comment'];
            }

            if (isset($params['geo_cord'])) {
                $address->geo_cord = $params['geo_cord'];
            }

            if ($hashUpdateNeeded) {
                /** @var Address $temp */
                $temp = (new Address)->find($addressId);

                $address->id_hash = Address::createHash(
                    $params['city'] ?? $temp->city,
                    $params['street'] ?? $temp->street,
                    $params['street_number'] ?? $temp->street_number,
                    $params['flat_number'] ?? $temp->flat_number
                );
            }

            $address->push();

            $data = [
                'address_id' => $address->id,
                'city' => $address->city,
                'street' => $address->street,
                'street_number' => $address->street_number,
                'flat_number' => $address->flat_number,
                'floor' => $address->floor,
                'client_name' => $address->client_name,
                'delivery_hours' => $address->delivery_hours,
                'phone' => $address->phone,
                'code' => $address->code,
                'comment' => $address->comment,
                'geo_cord' => $address->geo_cord,
            ];

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }

    /**
     * @param int $addressId
     * @return JsonResponse
     */
    public function get(int $addressId): JsonResponse
    {
        $params = ['address_id' => $addressId];

        $validator = Validator::make($params, [
            'address_id' => 'required|integer|exists:address,id'
        ]);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), $params);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var Address $address */
            $address = (new Address)->find($addressId);

            $data = [
                'address_id' => $address->id,
                'city' => $address->city,
                'street' => $address->street,
                'street_number' => $address->street_number,
                'flat_number' => $address->flat_number,
                'floor' => $address->floor,
                'client_name' => $address->client_name,
                'delivery_hours' => $address->delivery_hours,
                'phone' => $address->phone,
                'code' => $address->code,
                'comment' => $address->comment,
                'geo_cord' => $address->geo_cord,
                'id_hash' => $address->id_hash
            ];

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
