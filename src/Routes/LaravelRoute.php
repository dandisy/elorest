<?php

namespace Webcore\Elorest\Route;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Webcore\Elorest\Http\Request\IRequest;
use Webcore\Elorest\Http\Response\IResponse;
use Webcore\Elorest\Repository\IRepository;
// use Webcore\Elorest\Route\ARoute;
use Webcore\Elorest\Service\AService;
use Illuminate\Support\Facades\URL;

class LaravelRoute extends ARoute
{
    // public function __construct(IRequest $requestObj, IRepository $repositoryObj, IResponse $responseObj, AService $serviceObj)
    // {
    //     parent::__construct($requestObj, $repositoryObj, $responseObj, $serviceObj);
    // }

    public function get() {
        return Route::get('elorest/{namespaceOrModel}/{idOrModel?}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel = NULL, $id = NULL) {
            return $this->routeGet($request, $namespaceOrModel, $idOrModel, $id);
        });
    }

    public function post() {
        return Route::post('elorest/{namespaceOrModel}/{model?}', function(Request $request, $namespaceOrModel, $model = null) {
            return $this->routePost($request, $namespaceOrModel, $model);
        });
    }

    public function put() {
        return Route::put('elorest/{namespaceOrModel}/{idOrModel?}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel = null, $id = null) {
            return $this->routePut($request, $namespaceOrModel, $idOrModel, $id);
        });
    }

    public function patch() {
        return Route::patch('elorest/{namespaceOrModel}/{idOrModel?}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel = null, $id = null) {
            return $this->routePatch($request, $namespaceOrModel, $idOrModel, $id);
        });
    }

    public function delete() {
        return Route::delete('elorest/{namespaceOrModel}/{idOrModel?}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel = null, $id = null) {
            return $this->routeDelete($request, $namespaceOrModel, $idOrModel, $id);
        });
    }

    protected function routeGet($request, $namespaceOrModel, $idOrModel, $id) {
        $user = $request->user();

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel == 'columns') {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }

            if(method_exists($user, 'can')) {
                if(!$user->can('viewAny', $modelNameSpace)) {
                    return $this->responseObj->response([
                        "code" => 403,
                        "status" => false,
                        "message" => "Not authorized",
                        "error" => [
                            "code" => 102403,
                            "detail" => "You do not have permission to access this resource"
                        ],
                        "params" => $this->requestObj->requestAll($request),
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 403);
                }
            }

            return $this->repositoryObj->getTableColumns($data);
        }
        if(is_numeric($idOrModel)) {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }

            $data = $this->repositoryObj->findById($idOrModel, $data);

            if(!$data) {
                return $this->responseObj->response([
                    "code" => 410,
                    "status" => false,
                    "message" => "Not found",
                    "error" => [
                        "code" => 102410,
                        "detail" => "Data not available"
                    ],
                    "params" => $this->requestObj->requestAll($request),
                    "links" => [
                        "self" => URL::current()
                    ]
                ], 410);
            }

            if(method_exists($user, 'can')) {
                if(!$user->can('view', $data)) {
                    return $this->responseObj->response([
                        "code" => 403,
                        "status" => false,
                        "message" => "Not authorized",
                        "error" => [
                            "code" => 102403,
                            "detail" => "You do not have permission to access this resource"
                        ],
                        "params" => $this->requestObj->requestAll($request),
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 403);
                }
            }

            return $data;
        }
        if($idOrModel) {
            $modelNameSpace .= '\\'.$idOrModel;
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }

            if($id == 'columns') {
                if(method_exists($user, 'can')) {
                    if(!$user->can('viewAny', $modelNameSpace)) {
                        return $this->responseObj->response([
                            "code" => 403,
                            "status" => false,
                            "message" => "Not authorized",
                            "error" => [
                                "code" => 102403,
                                "detail" => "You do not have permission to access this resource"
                            ],
                            "params" => $this->requestObj->requestAll($request),
                            "links" => [
                                "self" => URL::current()
                            ]
                        ], 403);
                    }
                }

                return $this->repositoryObj->getTableColumns($data);
            }
            if(is_numeric($id)) {
                $data = $this->repositoryObj->findById($id, $data);

                if(!$data) {
                    return $this->responseObj->response([
                        "code" => 410,
                        "status" => false,
                        "message" => "Not found",
                        "error" => [
                            "code" => 102410,
                            "detail" => "Data not available"
                        ],
                        "params" => $this->requestObj->requestAll($request),
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 410);
                }

                if(method_exists($user, 'can')) {
                    if(!$user->can('view', $data)) {
                        return $this->responseObj->response([
                            "code" => 403,
                            "status" => false,
                            "message" => "Not authorized",
                            "error" => [
                                "code" => 102403,
                                "detail" => "You do not have permission to access this resource"
                            ],
                            "params" => $this->requestObj->requestAll($request),
                            "links" => [
                                "self" => URL::current()
                            ]
                        ], 403);
                    }
                }

                return $data;
            }
        } else {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     // throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Resource not found');
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }
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

        $data = $this->serviceObj->getQuery($input, $data);

        if(!$data) {
            return $this->responseObj->response([
                "code" => 410,
                "status" => false,
                "message" => "Not found",
                "error" => [
                    "code" => 102410,
                    "detail" => "Data not available"
                ],
                "params" => $input,
                "links" => [
                    "self" => URL::current()
                ]
            ], 410);
        }

        if(method_exists($user, 'can')) {
            if(isset($data->id)) {
                if(!$user->can('view', $data)) {
                    return $this->responseObj->response([
                        "code" => 403,
                        "status" => false,
                        "message" => "Not authorized",
                        "error" => [
                            "code" => 102403,
                            "detail" => "You do not have permission to access this resource"
                        ],
                        "params" => $input,
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 403);
                }
            } else {
                if(!$user->can('viewAny', $modelNameSpace)) {
                    return $this->responseObj->response([
                        "code" => 403,
                        "status" => false,
                        "message" => "Not authorized",
                        "error" => [
                            "code" => 102403,
                            "detail" => "You do not have permission to access this resource"
                        ],
                        "params" => $input,
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 403);
                }
            }
        }

        return $data;
    }

    // route post hanya punya 1 atau 2 url segment saja (namesapace dan tau model), sedangkan ruote lain bs 3 url segment
    protected function routePost($request, $namespaceOrModel, $model) {
        $user = $request->user();
        $userId = isset($user->id) ? $user->id : ($request->created_by ? : 0);

        if($namespaceOrModel == 'upload') {
            // $mainDir = env('SAVE_PATH'); // SAVE_PATH=./app/public/uploads/
            $mainDir = './app/public/uploads/';
            // $dir = str_replace('./','',$mainDir).$user->id;
            $dir = str_replace('./','',$mainDir).$userId;

            // if (!realpath('..'.DIRECTORY_SEPARATOR.$dir)) {
            //     mkdir('..'.DIRECTORY_SEPARATOR.$dir, 0777, true);
            // }
            if (!storage_path($dir)) {
                mkdir(storage_path($dir), 0777, true);
            }

            $dir = str_replace('/',DIRECTORY_SEPARATOR,$dir);
            // $name = $user->id.'_'.$request->model.'_'.time().'.'.$request->extention;
            $name = $userId.'_'.$request->model.'_'.time().'.'.$request->extention;
            $path = $dir.DIRECTORY_SEPARATOR.$name;
            // $path = $dir.$name;
            // $destinationPath = '..'.DIRECTORY_SEPARATOR.$dir;
            $destinationPath = storage_path($dir);

            if($request->hasFile('file')) {
                // if (realpath('..'.DIRECTORY_SEPARATOR.$path)) {
                if (realpath(storage_path($path))) {
                    return response(json_encode([
                        "code" => 200,
                        "status" => false,
                        "message" => "file already exist"
                    ], 200))
                        ->header('Content-Type', 'application/json');
                }

                $file = $request->file('file');
                $file->move($destinationPath, $name);

                // if (realpath('..'.DIRECTORY_SEPARATOR.$path)) {
                if (realpath(storage_path($path))) {
                    return response([
                        "code" => 201,
                        "status" => true,
                        "message" => "file saved successfully",
                        // "data" => str_replace(DIRECTORY_SEPARATOR,'/',str_replace('public'.DIRECTORY_SEPARATOR,'',$path))
                        "data" => url('/').str_replace('storage/app/public','/storage',str_replace(DIRECTORY_SEPARATOR,'/',$path))
                    ], 201)
                        ->header('Content-Type', 'application/json');
                }
            } else {
                if($request->file) {
                    $data = base64_decode($request->file);
                    file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),$data);
                }

                // if (realpath('..'.DIRECTORY_SEPARATOR.$path)) {
                if (realpath(storage_path($path))) {
                    return response([
                        "code" => 201,
                        "status" => true,
                        "message" => "file saved successfully",
                        // "data" => str_replace(DIRECTORY_SEPARATOR,'/',str_replace('public'.DIRECTORY_SEPARATOR,'',$path))
                        "data" => url('/').str_replace('storage/app/public','/storage',str_replace(DIRECTORY_SEPARATOR,'/',$path))
                    ], 201)
                        ->header('Content-Type', 'application/json');
                }
            }

            return response(json_encode([
                "code" => 200,
                "status" => false,
                "message" => "data input not valid"
            ], 200))
                ->header('Content-Type', 'application/json');
        }

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if(!$model) {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }
        } else {
            $modelNameSpace .= '\\'.$model;
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }
        }

        $request->validate($modelNameSpace::$rules);

        $input = $this->requestObj->requestAll($request);

        if(method_exists($user, 'can')) {
            if(!$user->can('create', $modelNameSpace)) {
                return $this->responseObj->response([
                    "code" => 403,
                    "status" => false,
                    "message" => "Not authorized",
                    "error" => [
                        "code" => 102403,
                        "detail" => "You do not have permission to access this resource"
                    ],
                    "params" => $this->requestObj->requestAll($request),
                    "links" => [
                        "self" => URL::current()
                    ]
                ], 403);
            }
        }
        // $modelName = explode('\\', $modelNameSpace);
        // $checkPolicy = class_exists('App\Policies\\'.(isset($modelName[2]) ? $modelName[2] : $modelName[1]).'Policy');
        // if($checkPolicy) {
        //     if($user->can('create', $modelNameSpace)) {
        //         return $this->responseObj->response([
        //             "code" => 201,
        //             "status" => true,
        //             "message" => "Data saved successfully",
        //             "data" => $this->repositoryObj->createData($input, $data)
        //         ], 201);
        //     } else {
        //         return $this->responseObj->response([
        //             "code" => 403,
        //             "status" => false,
        //             "message" => "Not authorized",
        //             "error" => [
        //                 "code" => 102403,
        //                 "detail" => "You do not have permission to save data"
        //             ],
        //             "params" => $input,
        //             "links" => [
        //                 "self" => URL::current()
        //             ]
        //         ], 403);
        //     }
        // } else {
            return $this->responseObj->response([
                "code" => 201,
                "status" => true,
                "message" => "Data saved successfully",
                "data" => $this->repositoryObj->createData($input, $data)
            ], 201);
        // }
    }

    protected function routePut($request, $namespaceOrModel, $idOrModel, $id) {
        $user = $request->user();

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel) {
            if(is_numeric($idOrModel)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }

                // in put, error if run validate if reuired fields not available
                // $request->validate($modelNameSpace::$rules);
                $input = $this->requestObj->requestAll($request);

                $data = $this->repositoryObj->findById($idOrModel, $data);
            } else {
                $modelNameSpace .= '\\'.$idOrModel;
                // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
                // if(class_exists($modelNameSpace)) {
                    $data = new $modelNameSpace();

                    if(isset($data->elorest)) {
                        if($data->elorest == false) {
                            return 'restricted';
                        }
                    }
                // } else {
                //     return $this->responseObj->response([
                //         "message" => "Not found",
                //         "error" => [
                //             "code" => 102404,
                //             "detail" => "The resource was not found"
                //         ],
                //         "status" => 404,
                //         "params" => $input,
                //         "links" => [
                //             "self" => URL::current()
                //         ]
                //     ], 404);
                // }

                // in put, error if run validate if reuired fields not available
                // $request->validate($modelNameSpace::$rules);
                $input = $this->requestObj->requestAll($request);

                if($id && is_numeric($id)) {
                    $data = $this->repositoryObj->findById($id, $data);
                } else {
                    $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first();
                }
            }
        } else {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }

            // in put, error if run validate if reuired fields not available
            // $request->validate($modelNameSpace::$rules);
            $input = $this->requestObj->requestAll($request);

            $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first();
        }

        if($data) {
            $input['updated_by'] = $user->id;

            if(method_exists($user, 'can')) {
                if(!$user->can('update', $data)) {
                    return $this->responseObj->response([
                        "code" => 403,
                        "status" => false,
                        "message" => "Not authorized",
                        "error" => [
                            "code" => 102403,
                            "detail" => "You do not have permission to access this resource"
                        ],
                        "params" => $this->requestObj->requestAll($request),
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 403);
                }
            }
            // $modelName = explode('\\', $modelNameSpace);
            // $checkPolicy = class_exists('App\Policies\\'.(isset($modelName[2]) ? $modelName[2] : $modelName[1]).'Policy');
            // if($checkPolicy) {
            //     if($user->can('update', $data)) {
            //         // TODO: use $this->serviceObj->getFormData() instead $input for responseFormatable REST API
            //         $data = $this->repositoryObj->updateData($input, $data);
            //         return $this->responseObj->response([
            //             "code" => 200,
            //             "status" => true,
            //             "message" => "Data updated successfully",
            //             "data" => $data
            //         ]);
            //     } else {
            //         return $this->responseObj->response([
            //             "code" => 403,
            //             "status" => false,
            //             "message" => "Not authorized",
            //             "error" => [
            //                 "code" => 102403,
            //                 "detail" => "You do not have permission to update data"
            //             ],
            //             "params" => $input,
            //             "links" => [
            //                 "self" => URL::current()
            //             ]
            //         ], 403);
            //     }
            // } else {
                // TODO: use $this->serviceObj->getFormData() instead $input for responseFormatable REST API
                $data = $this->repositoryObj->updateData($input, $data);
                return $this->responseObj->response([
                    "code" => 200,
                    "status" => true,
                    "message" => "Data updated successfully",
                    "data" => $data
                ]);
            // }
        }

        return $this->responseObj->response([
            "code" => 410,
            "status" => false,
            "message" => "Not found",
            "error" => [
                "code" => 102410,
                "detail" => "Data not available"
            ],
            "params" => $input,
            "links" => [
                "self" => URL::current()
            ]
        ], 410);
    }

    protected function routePatch($request, $namespaceOrModel, $idOrModel, $id) {
        $user = $request->user();

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel) {
            if(is_numeric($idOrModel)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }

                // in patch, error if run validate if reuired fields not available
                // $request->validate($modelNameSpace::$rules);
                $input = $this->requestObj->requestAll($request);

                $data = $this->repositoryObj->findById($idOrModel, $data);
            } else {
                $modelNameSpace .= '\\'.$idOrModel;
                // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
                // if(class_exists($modelNameSpace)) {
                    $data = new $modelNameSpace();

                    if(isset($data->elorest)) {
                        if($data->elorest == false) {
                            return 'restricted';
                        }
                    }
                // } else {
                //     return $this->responseObj->response([
                //         "message" => "Not found",
                //         "error" => [
                //             "code" => 102404,
                //             "detail" => "The resource was not found"
                //         ],
                //         "status" => 404,
                //         "params" => $input,
                //         "links" => [
                //             "self" => URL::current()
                //         ]
                //     ], 404);
                // }

                // in patch, error if run validate if reuired fields not available
                // $request->validate($modelNameSpace::$rules);
                $input = $this->requestObj->requestAll($request);

                if($id && is_numeric($id)) {
                    $data = $this->repositoryObj->findById($id, $data);
                } else {
                    $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first();
                }
            }
        } else {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }

            // in patch, error if run validate if reuired fields not available
            // $request->validate($modelNameSpace::$rules);
            $input = $this->requestObj->requestAll($request);

            $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first();
        }

        if($data) {
            $input['updated_by'] = $user->id;

            if(method_exists($user, 'can')) {
                if(!$user->can('update', $data)) {
                    return $this->responseObj->response([
                        "code" => 403,
                        "status" => false,
                        "message" => "Not authorized",
                        "error" => [
                            "code" => 102403,
                            "detail" => "You do not have permission to access this resource"
                        ],
                        "params" => $this->requestObj->requestAll($request),
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 403);
                }
            }
            // $modelName = explode('\\', $modelNameSpace);
            // $checkPolicy = class_exists('App\Policies\\'.(isset($modelName[2]) ? $modelName[2] : $modelName[1]).'Policy');
            // if($checkPolicy) {
            //     if ($user->can('update', $data)) {
            //         $this->repositoryObj->deleteData($data);

            //         // TODO: use $this->serviceObj->getFormData() instead $input for responseFormatable REST API
            //         $data = $this->repositoryObj->insertData($input, $data);
            //         return $this->responseObj->response([
            //             "code" => 200,
            //             "status" => true,
            //             "message" => "Data updated successfully",
            //             "data" => $data
            //         ]);
            //     } else {
            //         return $this->responseObj->response([
            //             "code" => 403,
            //             "status" => false,
            //             "message" => "Not authorized",
            //             "error" => [
            //                 "code" => 102403,
            //                 "detail" => "You do not have permission to update data"
            //             ],
            //             "params" => $input,
            //             "links" => [
            //                 "self" => URL::current()
            //             ]
            //         ], 403);
            //     }
            // } else {
                $this->repositoryObj->deleteData($data);

                // TODO: use $this->serviceObj->getFormData() instead $input for responseFormatable REST API
                $data = $this->repositoryObj->insertData($input, $data);
                return $this->responseObj->response([
                    "code" => 200,
                    "status" => true,
                    "message" => "Data updated successfully",
                    "data" => $data
                ]);
            // }
        }

        return $this->responseObj->response([
            "code" => 410,
            "status" => false,
            "message" => "Not found",
            "error" => [
                "code" => 102410,
                "detail" => "Data not available"
            ],
            "params" => $input,
            "links" => [
                "self" => URL::current()
            ]
        ], 410);
    }

    protected function routeDelete($request, $namespaceOrModel, $idOrModel, $id) {
        $user = $request->user();

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel) {
            if(is_numeric($idOrModel)) {
                // TODO: check if $id exist and numeric
                // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
                // if(class_exists($modelNameSpace)) {
                    $data = new $modelNameSpace();

                    if(isset($data->elorest)) {
                        if($data->elorest == false) {
                            return 'restricted';
                        }
                    }
                // } else {
                //     // abort(404); // todo : custom message
                //     return $this->responseObj->response([
                //         "message" => "Not found",
                //         "error" => [
                //             "code" => 102404,
                //             "detail" => "The resource was not found"
                //         ],
                //         "status" => 404,
                //         "params" => $input,
                //         "links" => [
                //             "self" => URL::current()
                //         ]
                //     ], 404);
                // }

                // tidak ada request body utk route delete
                $data = $this->repositoryObj->findById($idOrModel, $data);
            } else {
                $modelNameSpace .= '\\'.$idOrModel;
                // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
                // if(class_exists($modelNameSpace)) {
                    $data = new $modelNameSpace();

                    if(isset($data->elorest)) {
                        if($data->elorest == false) {
                            return 'restricted';
                        }
                    }
                // } else {
                //     // abort(404); // todo : custom message
                //     return $this->responseObj->response([
                //         "message" => "Not found",
                //         "error" => [
                //             "code" => 102404,
                //             "detail" => "The resource was not found"
                //         ],
                //         "status" => 404,
                //         "params" => $input,
                //         "links" => [
                //             "self" => URL::current()
                //         ]
                //     ], 404);
                // }

                // tidak ada request body utk route delete

                // $modelName = explode('\\', $modelNameSpace);
                // $checkPolicy = class_exists('App\Policies\\'.(isset($modelName[2]) ? $modelName[2] : $modelName[1]).'Policy');
                // if($checkPolicy) {
                //     if($user->can('delete', $data)) {
                //         if($id && is_numeric($id)) {
                //             $data = $this->repositoryObj->findById($id, $data);
                //         } else {
                //             $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first();
                //         }
                //     } else {
                //         return $this->responseObj->response([
                //             "code" => 403,
                //             "status" => false,
                //             "message" => "Not authorized",
                //             "error" => [
                //                 "code" => 102403,
                //                 "detail" => "You do not have permission to delete data"
                //             ],
                //             "params" => $input,
                //             "links" => [
                //                 "self" => URL::current()
                //             ]
                //         ], 403);
                //     }
                // } else {
                    if($id && is_numeric($id)) {
                        $data = $this->repositoryObj->findById($id, $data);
                    } else {
                        $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first();
                    }
                // }
            }
        } else {
            // TODO: check if $id exist and numeric
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }
            // } else {
            //     // abort(404); // todo : custom message
            //     return $this->responseObj->response([
            //         "message" => "Not found",
            //         "error" => [
            //             "code" => 102404,
            //             "detail" => "The resource was not found"
            //         ],
            //         "status" => 404,
            //         "params" => $input,
            //         "links" => [
            //             "self" => URL::current()
            //         ]
            //     ], 404);
            // }

            // tidak ada request body utk route delete

            $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first();
        }

        if($data) {
            if(method_exists($user, 'can')) {
                if(!$user->can('delete', $data)) {
                    return $this->responseObj->response([
                        "code" => 403,
                        "status" => false,
                        "message" => "Not authorized",
                        "error" => [
                            "code" => 102403,
                            "detail" => "You do not have permission to access this resource"
                        ],
                        "params" => $this->requestObj->requestAll($request),
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 403);
                }
            }
            // $modelName = explode('\\', $modelNameSpace);
            // $checkPolicy = class_exists('App\Policies\\'.(isset($modelName[2]) ? $modelName[2] : $modelName[1]).'Policy');
            // if($checkPolicy) {
            //     if ($user->can('delete', $data)) {
            //         $data = $this->repositoryObj->deleteData($data);
            //         return $this->responseObj->response([
            //             "code" => 200,
            //             "status" => true,
            //             "message" => "Data deleted successfully",
            //             "data" => $data
            //         ]);
            //     } else {
            //         return $this->responseObj->response([
            //             "code" => 403,
            //             "status" => false,
            //             "message" => "Not authorized",
            //             "error" => [
            //                 "code" => 102403,
            //                 "detail" => "You do not have permission to delete data"
            //             ],
            //             "params" => $input,
            //             "links" => [
            //                 "self" => URL::current()
            //             ]
            //         ], 403);
            //     }
            // } else {
                $data = $this->repositoryObj->deleteData($data);
                return $this->responseObj->response([
                    "code" => 200,
                    "status" => true,
                    "message" => "Data deleted successfully",
                    "data" => $data
                ]);
            // }
        }

        // abort(404);
        return $this->responseObj->response([
            "code" => 410,
            "status" => false,
            "message" => "Not found",
            "error" => [
                "code" => 102410,
                "detail" => "Data not available"
            ],
            "params" => $input,
            "links" => [
                "self" => URL::current()
            ]
        ], 410);
    }
}
