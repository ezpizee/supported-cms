<?php

namespace Ezpizee\SupportedCMS\Exception;

use Ezpizee\Utils\CustomResponse;

use Exception;
use Error as NativeError;
use Throwable;

class Error extends NativeError
{
    /**
     * @var Exception
     */
    private $e;

    public function __construct($msg, $code = 0, Throwable $previous = null)
    {
        if (is_array($msg) || is_object($msg)) {
            $msg = json_encode($msg);
        }
        parent::__construct($msg, $code, $previous);
        $this->e = $previous;
        if (!in_array($code, CustomResponse::HTTP_RESPONSE_CODES)) {
            $code = 500;
        }

        header('Content-Type: application/json');
        http_response_code($code);
        die(json_encode([
            'status' => 'ERROR',
            'code' => $code,
            'message' => $msg,
            'data' => null
        ]));
    }
}