<?php

namespace Webcore\Elorest\Http\Response;

use Illuminate\Http\Response;
// use Webcore\Elorest\Http\Response\IResponse;

class LaravelResponse implements IResponse
{
    public function __construct()
    {
        //
    }

    public function responsJson($status, $message, $code = 200, $data = null) {
        return (new Response(json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]), $code))
            ->header('Content-Type', 'application/json');
    }
}
