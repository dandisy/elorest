<?php

namespace Webcore\Elorest\Route;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Webcore\Elorest\Http\Request\IRequest;
use Webcore\Elorest\Http\Response\IResponse;
use Webcore\Elorest\Repository\IRepository;
use Webcore\Elorest\Route\ARoute;
use Webcore\Elorest\Service\AService;

class LaravelRoute extends ARoute
{
    public function __construct(IRequest $requestObj, IRepository $repositoryObj, IResponse $responseObj, AService $serviceObj)
    {
        parent::__construct($requestObj, $repositoryObj, $responseObj, $serviceObj);
    }

    public function get() {
        return Route::get('elorest/{namespaceOrModel}/{idOrModel?}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel = NULL, $id = NULL) {
            return $this->getProcess($request, $namespaceOrModel, $idOrModel, $id);
        });
    }

    public function post() {
        return Route::post('elorest/{namespaceOrModel}/{model?}', function(Request $request, $namespaceOrModel, $model = null) {
            return $this->postProcess($request, $namespaceOrModel, $model);
        });
    }

    public function put() {
        return Route::put('elorest/{namespaceOrModel}/{idOrModel}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel, $id = null) {
            return $this->putProcess($request, $namespaceOrModel, $idOrModel, $id);
        });
    }

    public function patch() {
        return Route::patch('elorest/{namespaceOrModel}/{idOrModel}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel, $id = null) {
            return $this->patchProcess($request, $namespaceOrModel, $idOrModel, $id);
        });
    }

    public function delete() {
        return Route::delete('elorest/{namespaceOrModel}/{idOrModel}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel, $id = null) {
            return $this->deleteProcess($request, $namespaceOrModel, $idOrModel, $id);
        });
    }

    protected function getProcess($request, $namespaceOrModel, $idOrModel, $id) {
        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel == 'columns') {
            $data = new $modelNameSpace();
            return $this->repositoryObj->getTableColumns($data);
        }
        if(is_numeric($idOrModel)) {
            $data = new $modelNameSpace();
            return $this->repositoryObj->findById($idOrModel, $data);
        }
        if($idOrModel) {
            $modelNameSpace .= '\\'.$idOrModel;
            $data = new $modelNameSpace();

            if($id == 'columns') {
                return $this->repositoryObj->getTableColumns($data);
            }
            if(is_numeric($id)) {
                return $this->repositoryObj->findById($idOrModel, $data);
            }
        } else {
            $data = new $modelNameSpace();
        }

        $input = $this->requestObj->requestAll($request);
        if(!$input) {
            return $this->repositoryObj->getAll($data);
        }

        // foreach($input as $key => $val) {
        //     if($key === 'paginate') {
        //         $paginate = $val;
        //     }
        //     if($key !== 'page') {
        //         $vals = [];

        //         if(is_array($val)) {
        //             $vals = $val;
        //         } else {
        //             array_push($vals, $val);
        //         }

        //         foreach($vals as $item) {
        //             // if(preg_match('/\[(.*?)\]/', $item, $match)) { // due to whereIn, the $val using [...] syntax
        //             //     $item = str_replace(','.$match[0], '', $item);
        //             //     $item = explode(',', trim($item));
        //             //     array_push($item, explode(',', trim($match[1])));
        //             // } else {
        //             //     $item = explode(',', item($item));
        //             // }

        //             // $data = call_user_func_array(array($data,$key), $item);

        //             $data = getQuery($data, $key, $item);//['data'];

        //         }

        //         if($key === 'paginate') {
        //             $data->appends(['paginate' => $paginate])->links();
        //         }
        //     }
        // }

        // return $data;

        return $this->serviceObj->getQuery($input, $data);
    }

    protected function postProcess($request, $namespaceOrModel, $model) {
        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if(!$model) {
            $data = new $modelNameSpace();
        } else {
            $modelNameSpace .= '\\'.$model;
            $data = new $modelNameSpace();
        }

        $input = $this->requestObj->requestAll($request);
        if($input) {
            return $this->repositoryObj->createData($input, $data);
        }

        return $this->responseObj->responsJson("error", "data input not valid", 400);
    }

    protected function putProcess($request, $namespaceOrModel, $idOrModel, $id) {
        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if(is_numeric($idOrModel)) {
            $data = new $modelNameSpace();
        } else {
            $modelNameSpace .= '\\'.$idOrModel;
            $data = new $modelNameSpace();
        }

        $input = $this->requestObj->requestAll($request);
        if($input) {
            if($id) {
                $data = $this->repositoryObj->findById($id, $data);
            } else {
                $data = $this->serviceObj->invoke()->first();
            }

            if($data) {
                return $this->repositoryObj->updateData($input, $data);
            }
        }

        return $this->responseObj->responsJson("error", "data input not valid", 400);
    }

    protected function patchProcess($request, $namespaceOrModel, $idOrModel, $id) {
        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if(is_numeric($idOrModel)) {
            $data = new $modelNameSpace();
        } else {
            $modelNameSpace .= '\\'.$idOrModel;
            $data = new $modelNameSpace();
        }

        $input = $this->requestObj->requestAll($request);
        if($input) {
            if($id) {
                $data = $this->repositoryObj->findById($id, $data);
            } else {
                $data = $this->serviceObj->invoke()->first();
            }

            if($data) {
                $this->repositoryObj->deleteData($data);

                return $this->repositoryObj->insertData($input, $data);
            }
        }

        return $this->responseObj->responsJson("error", "data input not valid", 400);
    }

    protected function deleteProcess($request, $namespaceOrModel, $idOrModel, $id) {
        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if(is_numeric($idOrModel)) {
            $data = new $modelNameSpace();
        } else {
            $modelNameSpace .= '\\'.$idOrModel;
            $data = new $modelNameSpace();
        }

        $input = $this->requestObj->requestAll($request);
        if($input) {
            if($id) {
                $data = $this->repositoryObj->findById($id, $data);
            } else {
                $data = $this->serviceObj->invoke()->first();
            }

            if($data) {
                return $this->repositoryObj->deleteData($data);
            }
        }

        return $this->responseObj->responsJson("error", "data input not valid", 400);
    }
}
