<?php

namespace Webcore\Elorest\Http\Request;

// use Webcore\Elorest\Http\Request\IRequest;

class LaravelRequest implements IRequest
{
    public function __construct()
    {
        //
    }

    /*
    * Get http request object using Laravel Framework
    *
    * @param Request $request
    * @return Collection $request
    */
    public function requestAll($request) {
        return $request->all();
    }
}
