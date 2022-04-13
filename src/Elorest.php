<?php

namespace Dandisy\Elorest;

// use Illuminate\Container\Container;
use Dandisy\Elorest\Http\Middleware\LaravelMiddleware;
use Dandisy\Elorest\Http\Request\LaravelRequest;
use Dandisy\Elorest\Http\Response\LaravelResponse;
use Dandisy\Elorest\Repositories\EloquentRepository;
use Dandisy\Elorest\Routes\LaravelRoute;
use Dandisy\Elorest\Services\LaravelService;

class Elorest
{
    /*
     * Create route object
     *
     * @return Object Route
     */
    protected static function routeObject() {
        // TODO: ini seharusnya tidak hardcode buat instant dr konkrit class disini, biasa dgn DI
        return new LaravelRoute(
            new LaravelRequest(),
            new EloquentRepository(),
            new LaravelResponse(),
            new LaravelService()
        );

        // This make tight coupling with Laravel Framework
        // and must register ElorestServiceProvide to Laravel config/app.php if installed manually
        // // return resolve('Dandisy\Elorest\Route\LaravelRoute');
        // return Container::getInstance()->make(LaravelRoute::class);
    }

    /*
     * Wrapping route object with middleware
     *
     * @return Object Route
     */
    protected static function middlewareObject() {
        // TODO: ini seharusnya tidak hardcode buat instant dr konkrit class disini, biasa dgn DI
        return new LaravelMiddleware(self::routeObject());

        // This make tight coupling with Laravel Framework
        // and must register ElorestServiceProvide to Laravel config/app.php if installed manually
        // // return Container::getInstance()->make(LaravelMiddleware::class);
        // return resolve('LaravelMiddleware');
    }

    // TODO: seharusnya ini tidak static, nantinya akan dipanggil menggunkan facade saja
    public static function routes(array $middleware = null) {
        self::processRoute($middleware);
    }

    /*
     * Prepering the route with its middleware
     *
     * @param Array $middleware
     * @return void
     */
    protected static function processRoute($middleware) {
        $routes = self::getRegisteredRoute();

        if($middleware) {
            if(isset($middleware['only']))
            {
                // handling route asigned middleware
                foreach($middleware['only'] as $route) {
                    // TODO: seharusnya pakai try catch untuk handling ketika route tdk terdaftar
                    if(in_array($route, $routes)) {
                        self::middleware($route, $middleware['middleware']);
                    }
                }

                // handling route not asigned middleware
                $except = array_diff($routes, $middleware['only']);
                foreach($except as $route) {
                    // self::$route();
                    self::routeObjInvoke($route);
                }
            } 
            else if(isset($middleware['except'])) 
            {
                // handling route asigned middleware
                $only = array_diff($routes, $middleware['except']);
                foreach($only as $route) {
                    self::middleware($route, $middleware['middleware']);
                }

                // handling route not asigned middleware
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

    /*
     * Call the route object methods with middleware
     *
     * @param string $route
     * #param Array $middleware
     * @return void
     */
    protected static function middleware($route, $middleware) {
        self::middlewareObject()->middleware($route, $middleware);
    }

    /*
     * Printing the route object methods
     *
     * @param string $route
     * @return void
     */
    protected static function routeObjInvoke($route) {      
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
