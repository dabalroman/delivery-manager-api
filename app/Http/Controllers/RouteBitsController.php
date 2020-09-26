<?php


namespace App\Http\Controllers;


use App\Address;
use App\GMaps_API\RouteBitsService;
use App\Traits\ApiLogger;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RouteBitsController extends Controller
{
    use ApiResponser;
    use ApiLogger;

    /**
     * @param string $start geocoded location with dots replaced by underscore
     * @param string $end   geocoded location with dots replaced by underscore
     * @return JsonResponse
     */
    public function get(string $start, string $end): JsonResponse
    {
        $params = ['start' => $start, 'end' => $end];

        $validator = Validator::make($params, [
            'start' => 'required|regex:/^\d+(\_\d+)?,\d+(\_\d+)?$/',
            'end' => 'required|regex:/^\d+(\_\d+)?,\d+(\_\d+)?$/'
        ]);

        $start = str_replace('_', '.', $start);
        $end = str_replace('_', '.', $end);

        if ($validator->fails()) {
            $this->logValidationFailure($validator->errors()->all(), $params);
            return $this->errorResponse($validator->errors()->all(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = RouteBitsService::getRouteBit($start, $end);
        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }

    /**
     * @param string $addressPairs Addresses id's in form [id,id]
     * @return JsonResponse
     */
    public function getByAddressPair(string $addressPairs): JsonResponse
    {
        $addressesIds = explode(',', $addressPairs);
        $idsAmount = count($addressesIds);

        if ($idsAmount % 2 !== 0 && $idsAmount >= 2) {
            $this->logValidationFailure('Wrong ids amount', ['amount' => $idsAmount]);
            return $this->errorResponse('Wrong ids amount', Response::HTTP_BAD_REQUEST);
        }

        $data = [];

        try {

            for ($i = 0; $i < $idsAmount; $i += 2) {
                /** @var Address $start */
                $start = (new Address)->find($addressesIds[$i])->geo_cord;
                $end = (new Address)->find($addressesIds[$i + 1])->geo_cord;

                $routeBit = RouteBitsService::getRouteBit($start, $end);
                $routeBit['id'] = $addressesIds[$i] . ',' . $addressesIds[$i + 1];
                array_push($data, $routeBit);
            }

        } catch (Exception $e) {
            $this->logError($e);
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($data, Response::HTTP_OK);
    }
}
