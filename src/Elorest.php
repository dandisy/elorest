<?php

namespace Webcore\Elorest;

use Webcore\Elorest\Http\Middleware\LaravelMiddleware;
use Webcore\Elorest\Http\Request\LaravelRequest;
use Webcore\Elorest\Http\Response\LaravelResponse;
use Webcore\Elorest\Repository\EloquentRepository;
use Webcore\Elorest\Route\LaravelRoute;
use Webcore\Elorest\Service\LaravelService;

class Elorest
{
    static function routes(array $middleware = null) {
        self::processRoute($middleware);
    }

    protected static function processRoute($middleware) {
        $routes = self::getRegisteredRoute();

        if($middleware) {
            if(isset($middleware['only']))
            {
                foreach($middleware['only'] as $route) {
                    if(in_array($route, $routes)) {
                        self::middleware($route, $middleware['middleware']);
                    }
                }

                $except = array_diff($routes, $middleware['only']);
                foreach($except as $route) {
                    // self::$route();
                    self::routeObjInvoke($route);
                }
            } 
            else if(isset($middleware['except'])) 
            {
                $only = array_diff($routes, $middleware['except']);
                foreach($only as $route) {
                    self::middleware($route, $middleware['middleware']);
                }
                foreach($middleware['except'] as $route) {
                    // self::$route();
                    self::routeObjInvoke($route);
                }
            }
            else
            {
                foreach($routes as $route) {
                    self::middleware($route, $middleware['middleware']);
                }
            }
        } else {
            foreach($routes as $route) {
                // self::$route();
                self::routeObjInvoke($route);
            }
        }
    }

    protected static function getRegisteredRoute() {
        return self::routeObject()->getRoute();
    }

    protected static function middleware($route, $middleware) {
        // return self::middlewareObject()->middleware($route, $middleware);
        self::middlewareObject()->middleware($route, $middleware);
    }

    // TODO : to be loose coupling use strategy pattern
    protected static function routeObject() {
        return new LaravelRoute(
            new LaravelRequest(),
            new EloquentRepository(),
            new LaravelResponse(),
            new LaravelService()
        );
    }

    // TODO : to be loose coupling use strategy pattern
    protected static function middlewareObject() {
        return new LaravelMiddleware(self::routeObject());
    }

    protected static function routeObjInvoke($route) {
        // return self::routeObject()->$route();        
        self::routeObject()->$route();
    }

    // protected static function get() {
    //     return self::routeObject()->get();
    // }

    // protected static function post() {
    //     return self::routeObject()->post();
    // }

    // protected static function put() {
    //     return self::routeObject()->put();
    // }

    // protected static function patch() {
    //     return self::routeObject()->patch();
    // }

    // protected static function delete() {
    //     return self::routeObject()->delete();
    // }
}
