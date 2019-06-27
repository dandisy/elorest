<?php

namespace Webcore\Elorest\Http\Request;

interface IRequest
{
    /*
    * Get http request object
    *
    * @param Http Request Object $request
    *
    * @return Object $request
    */
    function requestAll($request);
}
