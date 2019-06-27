<?php

namespace Webcore\Elorest\Http\Middleware;

interface IMiddleware
{
    public function __construct($routeObj);

    function middleware($route, $middleware);
}
