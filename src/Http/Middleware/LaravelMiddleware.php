<?php

namespace Dandisy\Elorest\Http\Middleware;

// use Dandisy\Elorest\Http\Middleware\IMiddleware;

class LaravelMiddleware implements IMiddleware
{
    protected $routeObj;

    public function __construct($routeObj)
    {
        $this->routeObj = $routeObj;
    }

    public function middleware($route, $middleware) {
        return $this->routeObj->$route()->middleware($middleware);
    }
}
