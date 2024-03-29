<?php

namespace Dandisy\Elorest\Http\Response;

use Illuminate\Http\Response;
// use Dandisy\Elorest\Http\Response\IResponse;

class LaravelResponse implements IResponse
{
    public function __construct()
    {
        //
    }

    public function response($data = null, $code = 200, $type = 'application/json') {
        return (new Response(json_encode($data), $code))
            ->header('Content-Type', $type);
    }
}
