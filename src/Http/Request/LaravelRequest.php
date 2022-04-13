<?php

namespace Dandisy\Elorest\Http\Request;

// use Dandisy\Elorest\Http\Request\IRequest;

class LaravelRequest implements IRequest
{
    public function __construct()
    {
        //
    }

    /*
    * Get form input using Laravel Framework
    *
    * @param Request $request
    * @return Collection $request
    */
    public function requestAll($request) {
        return $request->all();
    }

    /*
    * Get URL parameters using Laravel Framework
    *
    * @param Request $request
    * @return Collection $request
    */
    public function requestParamAll($request) {
        return $request->query();
    }
}
