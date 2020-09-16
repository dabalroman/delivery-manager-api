<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Trait ApiLogger - Provides interface for event logging
 *
 * @package App\Traits
 */
trait ApiLogger
{
    /**
     * @param Exception $exception
     */
    public function logError(Exception $exception)
    {
        Log::error(__CLASS__ . ':' . $exception->getFile() . '(' . $exception->getLine() . '): '
            . $exception->getMessage());
    }

    /**
     * @param $failsArray
     * @param array $data
     */
    public function logValidationFailure($failsArray, $data = [])
    {
        Log::notice(__CLASS__ . ': Validation error: ' . join(' / ', $failsArray), $data);
    }
}
