<?php

namespace Dandisy\Elorest\Routes;

use Dandisy\Elorest\Http\Request\IRequest;
use Dandisy\Elorest\Http\Response\IResponse;
use Dandisy\Elorest\Repositories\IRepository;
use Dandisy\Elorest\Services\AService;

abstract class ARoute
{
    protected $requestObj;
    protected $repositoryObj;
    protected $responseObj;
    protected $serviceObj; 

    // TODO: seharusnya ini ada di config file elorest.php
    protected $routes = [
        'get',
        'post',
        'put',
        'patch',
        'delete'
    ];

    public function __construct(IRequest $requestObj, IRepository $repositoryObj, IResponse $responseObj, AService $serviceObj)
    {
        $this->requestObj = $requestObj;
        $this->repositoryObj = $repositoryObj;
        $this->responseObj = $responseObj;
        $this->serviceObj = $serviceObj;
    }

    /*
     * Register additional route type
     *
     * @return Object Route
     */
    public function register($routes) {
        if(is_array($routes)) {
            array_merge(self::$routes, $routes);
        } else {
            array_push(self::$routes, $routes);
        }
    }

    public function getRoute() {
        return $this->routes;
    }

    /*
     * Define how the framework get url segments and http requests for get http request type
     *
     * @return Object Route
     */
    abstract public function get();
    
    /*
     * Define how the framework get url segments and http requests for post http request type
     *
     * @return Object Route
     */
    abstract public function post();

    /*
     * Define how the framework get url segments and http requests for put http request type
     *
     * @return Object Route
     */
    abstract public function put();

    /*
     * Define how the framework get url segments and http requests for patch http request type
     *
     * @return Object Route
     */
    abstract public function patch();

    /*
     * Define how the framework get url segments and http requests for delete http request type
     *
     * @return Object Route
     */
    abstract public function delete();

    abstract protected function routeGet($request, $namespaceOrModel, $idOrModel, $id);

    abstract protected function routePost($request, $namespaceOrModel, $model);

    abstract protected function routePut($request, $namespaceOrModel, $idOrModel, $id);

    abstract protected function routePatch($request, $namespaceOrModel, $idOrModel, $id);

    abstract protected function routeDelete($request, $namespaceOrModel, $idOrModel, $id);
}
