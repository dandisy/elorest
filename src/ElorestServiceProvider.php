<?php

namespace Dandisy\Elorest;

use Illuminate\Support\ServiceProvider;
use Dandisy\Elorest\Http\Request\IRequest;
use Dandisy\Elorest\Http\Request\LaravelRequest;
use Dandisy\Elorest\Repositories\IRepository;
use Dandisy\Elorest\Repositories\EloquentRepository;
use Dandisy\Elorest\Http\Middleware\LaravelMiddleware;
use Dandisy\Elorest\Http\Response\IResponse;
use Dandisy\Elorest\Http\Response\LaravelResponse;
use Dandisy\Elorest\Routes\LaravelRoute;
use Dandisy\Elorest\Services\AService;
use Dandisy\Elorest\Services\LaravelService;

class ElorestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'../Exceptions' => app_path('Exceptions'),
        ]);

        $this->publishes([
            __DIR__.'../config/elorest.php' => config_path('elorest.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            IRequest::class,
            LaravelRequest::class
        );

        $this->app->bind(
            IRepository::class,
            EloquentRepository::class
        );

        $this->app->bind(
            IResponse::class,
            LaravelResponse::class
        );

        $this->app->bind(
            AService::class,
            LaravelService::class
        );

        // $this->app->bind(LaravelMiddleware::class, function($app) {
        //     return new LaravelMiddleware($app->make(LaravelRoute::class));
        // });
        $this->app->bind('LaravelMiddleware', function($app) {
            return new LaravelMiddleware($app->make(LaravelRoute::class));
        });
    }
}
