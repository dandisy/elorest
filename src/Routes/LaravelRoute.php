<?php

namespace Dandisy\Elorest\Routes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Dandisy\Elorest\Http\Request\IRequest;
use Dandisy\Elorest\Http\Response\IResponse;
use Dandisy\Elorest\Repositories\IRepository;
// use Dandisy\Elorest\Routes\ARoute;
use Dandisy\Elorest\Services\AService;
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
        \Illuminate\Support\Facades\Log::info('Elorest - query url - ' . $request->fullUrl());
        \Illuminate\Support\Facades\Log::info('Elorest - query params', $request->all());
        $user = $request->user();
        $input = $this->requestObj->requestAll($request);

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel == 'columns') {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();
                $data->setConnection(env('ELOREST_DB_GET', 'mysql'));

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforeGetQuery')) {
                    $elorestBeforeGetQuery = $modelInstance->elorestBeforeGetQuery($request);
                    if($elorestBeforeGetQuery || $elorestBeforeGetQuery === false) {
                        return $elorestBeforeGetQuery;
                    }
                }

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

            // if($user) {
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
                            "params" => $input,
                            "links" => [
                                "self" => URL::current()
                            ]
                        ], 403);
                    }
                }
            // }

            return $this->repositoryObj->getTableColumns($data);
        }
        if(is_numeric($idOrModel)) {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();
                $data->setConnection(env('ELOREST_DB_GET', 'mysql'));

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforeGetQuery')) {
                    $elorestBeforeGetQuery = $modelInstance->elorestBeforeGetQuery($request);
                    if($elorestBeforeGetQuery || $elorestBeforeGetQuery === false) {
                        return $elorestBeforeGetQuery;
                    }
                }

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
                    "params" => $input,
                    "links" => [
                        "self" => URL::current()
                    ]
                ], 410);
            }

            // if($user) {
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
                            "params" => $input,
                            "links" => [
                                "self" => URL::current()
                            ]
                        ], 403);
                    }
                }
            // }

            return $data;
        }
        if($idOrModel) {
            $modelNameSpace .= '\\'.$idOrModel;

            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();
                $data->setConnection(env('ELOREST_DB_GET', 'mysql'));

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforeGetQuery')) {
                    $elorestBeforeGetQuery = $modelInstance->elorestBeforeGetQuery($request);
                    if($elorestBeforeGetQuery || $elorestBeforeGetQuery === false) {
                        return $elorestBeforeGetQuery;
                    }
                }

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
                // if($user) {
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
                                "params" => $input,
                                "links" => [
                                    "self" => URL::current()
                                ]
                            ], 403);
                        }
                    }
                // }

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
                        "params" => $input,
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 410);
                }

                // if($user) {
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
                                "params" => $input,
                                "links" => [
                                    "self" => URL::current()
                                ]
                            ], 403);
                        }
                    }
                // }

                return $data;
            }
        } else {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();
                $data->setConnection(env('ELOREST_DB_GET', 'mysql'));

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforeGetQuery')) {
                    $elorestBeforeGetQuery = $modelInstance->elorestBeforeGetQuery($request);
                    if($elorestBeforeGetQuery || $elorestBeforeGetQuery === false) {
                        return $elorestBeforeGetQuery;
                    }
                }

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
        
        // if($user) {
            if(method_exists($user, 'can')) {
                if(!$user->can('viewAny', [$modelNameSpace, $data])) {
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
        // }

        $modelInstance = $data;
        // $input = $this->requestObj->requestAll($request);
        if(!$input) {
            // because laravel has weird behaviour, https://github.com/laravel/framework/issues/12894
            if(isset($data->elorestDisableHiddenProperty)) {
                if($data->elorestDisableHiddenProperty) {
                    $data = $this->repositoryObj->getAll($data)->makeVisible($data->hidden);
                }
            } else {
                $data = $this->repositoryObj->getAll($data);
            }

            if(method_exists($modelInstance, 'elorestAfterGetQuery')) {
                $elorestAfterGetQuery = $modelInstance->elorestAfterGetQuery($request, $data);
                if($elorestAfterGetQuery || $elorestAfterGetQuery === false) {
                    return $elorestAfterGetQuery;
                }
            }
            
            return $data;
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
        
        $elorestDisableHiddenProperty = false;
        $elorestDisableRelationHiddenProperty = null;
        // because laravel has weird behaviour, https://github.com/laravel/framework/issues/12894
        if(isset($data->elorestDisableHiddenProperty)) {
            if($data->elorestDisableHiddenProperty) {
                $elorestDisableHiddenProperty = $data->elorestDisableHiddenProperty;
            }
        }
        if(isset($data->elorestDisableRelationHiddenProperty)) {
            if($data->elorestDisableRelationHiddenProperty) {
                $elorestDisableRelationHiddenProperty = $data->elorestDisableRelationHiddenProperty;
            }
        }

        $data = $this->serviceObj->getQuery($input, $data);
        if(is_object($data)) {
            if($elorestDisableHiddenProperty) {
                if(method_exists($data, 'each')) {
                    $data->each(function($item) use($elorestDisableHiddenProperty) {
                        if(isset($item->hidden)) {
                            $item->makeVisible($item->hidden);
                        }
                    });
                } else {
                    if(isset($data->hidden)) {
                        $data->makeVisible($data->hidden);
                    }
                }
            }

            if($elorestDisableRelationHiddenProperty) {
                if(method_exists($data, 'each')) {
                    $data->each(function($value) use($elorestDisableRelationHiddenProperty) {
                        foreach($elorestDisableRelationHiddenProperty as $item) {
                            if($value->$item) {
                                if(isset($value->$item[0])) {
                                    $value->$item->makeVisible($value->$item[0]->hidden);
                                } else {
                                    if(isset($value->$item->hidden)) {
                                        $value->$item->makeVisible($value->$item->hidden);
                                    }
                                }
                            }
                        }
                    });
                } else {
                    if(method_exists($data, 'items')) {
                        $data->each(function($value) use($elorestDisableRelationHiddenProperty) {
                            foreach($elorestDisableRelationHiddenProperty as $item) {
                                if($value->$item) {
                                    if(isset($value->$item[0])) {
                                        $value->$item->makeVisible($value->$item[0]->hidden);
                                    } else {
                                        if(isset($value->$item->hidden)) {
                                            $value->$item->makeVisible($value->$item->hidden);
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        foreach($elorestDisableRelationHiddenProperty as $item) {
                            if($data->$item) {
                                if(isset($data->$item[0])) {
                                    $data->$item->makeVisible($data->$item[0]->hidden);
                                } else {
                                    if(isset($data->$item->hidden)) {
                                        $data->$item->makeVisible($data->$item->hidden);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if(!$data && $data !== 0) {
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

        if(method_exists($modelInstance, 'elorestAfterGetQuery')) {
            $elorestAfterGetQuery = $modelInstance->elorestAfterGetQuery($request, $data);
            if($elorestAfterGetQuery || $elorestAfterGetQuery === false) {
                return $elorestAfterGetQuery;
            }
        }

        return $data;
    }

    // route post hanya punya 1 atau 2 url segment saja (namesapace dan tau model), sedangkan ruote lain bs 3 url segment
    protected function routePost($request, $namespaceOrModel, $model) {
        $user = $request->user();
        $input = $this->requestObj->requestAll($request);
        $userId = isset($user->id) ? $user->id : ($request->created_by ? : 0);

        if($namespaceOrModel == 'upload') {
            if(config('elorest.storage') == 'minio') {
                $dir = $userId;

                $timestamp = time();
                $extension = $request->file('file')->getClientOriginalExtension();
                $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";

                $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                $path = $disk->putFileAs($dir, $request->file('file'), $name);

                return response([
                    "code" => 201,
                    "status" => true,
                    "message" => "file saved successfully",
                    "data" => $disk->url($path)
                ], 201)
                    ->header('Content-Type', 'application/json');
            } else {
                // $savePath = env('SAVE_PATH'); // SAVE_PATH=./app/public/uploads/
                $savePath = './app/public/uploads/';
                // $dir = str_replace('./','',$savePath).$user->id;
                $dir = str_replace('./','',$savePath).$userId;
                $dir = str_replace('/',DIRECTORY_SEPARATOR,$dir);

                if (!storage_path($dir)) {
                    mkdir(storage_path($dir), 0777, true);
                }

                if($request->hasFile('file')) {
                    $extension = $request->file('file')->extension();
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;

                    if (realpath(storage_path($path))) {
                        return response(json_encode([
                            "code" => 200,
                            "status" => false,
                            "message" => "file already exist"
                        ], 200))
                            ->header('Content-Type', 'application/json');
                    }

                    $file = $request->file('file');
                    $file->move(storage_path($dir), $name);

                    if(realpath(storage_path($path))) {
                        return response([
                            "code" => 201,
                            "status" => true,
                            "message" => "file saved successfully",
                            // "data" => str_replace(DIRECTORY_SEPARATOR,'/',str_replace('public'.DIRECTORY_SEPARATOR,'',$path))
                            "data" => url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path))
                        ], 201)
                            ->header('Content-Type', 'application/json');
                    }
                } else {
                    if(base64_decode($request->file, true) !== false) {
                        $extension = explode('/', mime_content_type($request->file))[1];
                        $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                        $path = $dir.DIRECTORY_SEPARATOR.$name;
                        file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->file));

                        if(realpath(storage_path($path))) {
                            return response([
                                "code" => 201,
                                "status" => true,
                                "message" => "file saved successfully",
                                "data" => url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path))
                            ], 201)
                                ->header('Content-Type', 'application/json');
                        }
                    }
                }
            }

            return response(json_encode([
                "code" => 200,
                "status" => false,
                "message" => "request data not valid"
            ], 200))
                ->header('Content-Type', 'application/json');
        }

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if(!$model) {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforePostQuery')) {
                    $elorestBeforePostQuery = $modelInstance->elorestBeforePostQuery($request);
                    if($elorestBeforePostQuery || $elorestBeforePostQuery === false) {
                        return $elorestBeforePostQuery;
                    }
                }

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

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforePostQuery')) {
                    $elorestBeforePostQuery = $modelInstance->elorestBeforePostQuery($request);
                    if($elorestBeforePostQuery || $elorestBeforePostQuery === false) {
                        return $elorestBeforePostQuery;
                    }
                }

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

        if(property_exists($modelNameSpace, 'rules')) {
            $request->validate($modelNameSpace::$rules);
        }

        // $input = $this->requestObj->requestAll($request);
        if(property_exists($modelNameSpace,'elorestPreventSetOnCreate')) {
            foreach($modelNameSpace::$elorestPreventSetOnCreate as $key) {
                if(!empty($input[$key])) {
                    unset($input[$key]);
                }
            }
        }
        $input['created_by'] = $userId;
        $input['updated_by'] = $userId;

        // if($user) {
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
                        "params" => $input,
                        "links" => [
                            "self" => URL::current()
                        ]
                    ], 403);
                }
            }
        // }

        // $savePath = env('SAVE_PATH'); // SAVE_PATH=./app/public/uploads/
        $savePath = './app/public/uploads/';
        // $dir = str_replace('./','',$savePath).$user->id;
        $dir = str_replace('./','',$savePath).$userId;
        $dir = str_replace('/',DIRECTORY_SEPARATOR,$dir);
        
        if (!storage_path($dir)) {
            mkdir(storage_path($dir), 0777, true);
        }

        if($request->hasFile('file')) {
            if(config('elorest.storage') == 'minio') {
                $originName = $request->file('file')->getClientOriginalName();
                $extension = $request->file('file')->extension();
                $size = $request->file('file')->getSize();
                $mimeType = $request->file('file')->getMimeType();
                $type = explode('/', $mimeType)[0];

                $dir = $userId;

                $timestamp = time();
                $extension = $request->file('file')->getClientOriginalExtension();
                $namespace = config('elorest.namespace','elorest');
                $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";

                $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                $path = $disk->putFileAs($dir, $request->file('file'), $name);

                $input['file'] = $disk->url($path);
                $input['origin_name'] = $originName;
                $input['file_size'] = $size/1000;
                $input['file_type'] = $mimeType;
                $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                $input['file_value'] = $name;
                $input['type'] = $type;
            } else {
                $originName = $request->file('file')->getClientOriginalName();
                $extension = $request->file('file')->extension();
                $size = $request->file('file')->getSize();
                $mimeType = $request->file('file')->getMimeType();
                $type = explode('/', $mimeType)[0];
                // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                $path = $dir.DIRECTORY_SEPARATOR.$name;

                if (realpath(storage_path($path))) {
                    return response(json_encode([
                        "code" => 200,
                        "status" => false,
                        "message" => "file already exist"
                    ], 200))
                        ->header('Content-Type', 'application/json');
                }

                $file = $request->file('file');
                $file->move(storage_path($dir), $name);

                if(realpath(storage_path($path))) {
                    $input['file'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                    $input['origin_name'] = $originName;
                    $input['file_size'] = $size/1000;
                    $input['file_type'] = $mimeType;
                    $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                    $input['file_value'] = $name;
                    $input['type'] = $type;
                }
            }
        } else {
            if($request->file) {
                if(base64_decode($request->file, true) !== false) {
                    $mimeType = mime_content_type($request->file);
                    $extension = explode('/', $mimeType)[1];
                    $type = explode('/', $mimeType)[0];
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
                    file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->file));

                    if(realpath(storage_path($path))) {
                        $input['file'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                        $input['file_type'] = $mimeType;
                        $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                        $input['file_value'] = $name;
                        $input['type'] = $type;
                    }
                }
            }
        }

        if($request->hasFile('image')) {
            if(config('elorest.storage') == 'minio') {
                $dir = $userId;

                $timestamp = time();
                $extension = $request->file('image')->getClientOriginalExtension();
                $namespace = config('elorest.namespace','elorest');
                $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";

                $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                $path = $disk->putFileAs($dir, $request->file('image'), $name);

                $input['image'] = $disk->url($path);
            } else {
                $extension = $request->file('image')->extension();
                // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                $path = $dir.DIRECTORY_SEPARATOR.$name;

                if (realpath(storage_path($path))) {
                    return response(json_encode([
                        "code" => 200,
                        "status" => false,
                        "message" => "file already exist"
                    ], 200))
                        ->header('Content-Type', 'application/json');
                }

                $file = $request->file('image');
                $file->move(storage_path($dir), $name);

                if(realpath(storage_path($path))) {
                    $input['image'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                }
            }
        } else {
            if($request->image) {
                if(base64_decode($request->image, true) !== false) {
                    $extension = explode('/', mime_content_type($request->image))[1];
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
                    file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->image));

                    if(realpath(storage_path($path))) {
                        $input['image'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                    }
                }
            }
        }

        if($request->hasFile('video')) {
            if(config('elorest.storage') == 'minio') {
                $dir = $userId;

                $timestamp = time();
                $extension = $request->file('video')->getClientOriginalExtension();
                $namespace = config('elorest.namespace','elorest');
                $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";

                $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                $path = $disk->putFileAs($dir, $request->file('video'), $name);

                $input['video'] = $disk->url($path);
            } else {
                $extension = $request->file('video')->extension();
                // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                $path = $dir.DIRECTORY_SEPARATOR.$name;

                if (realpath(storage_path($path))) {
                    return response(json_encode([
                        "code" => 200,
                        "status" => false,
                        "message" => "file already exist"
                    ], 200))
                        ->header('Content-Type', 'application/json');
                }

                $file = $request->file('video');
                $file->move(storage_path($dir), $name);

                if(realpath(storage_path($path))) {
                    $input['video'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                }
            }
        } else {
            if($request->video) {
                if(base64_decode($request->video, true) !== false) {
                    $extension = explode('/', mime_content_type($request->video))[1];
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
                    file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->video));

                    if(realpath(storage_path($path))) {
                        $input['video'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                    }
                }
            }
        }

        if(property_exists($modelNameSpace, 'elorestFileFields')) {
            foreach($modelNameSpace::$elorestFileFields as $file) {
                if($request->hasFile($file)) {
                    if(config('elorest.storage') == 'minio') {
                        $originName = $request->file($file)->getClientOriginalName();
                        $extension = $request->file($file)->extension();
                        $size = $request->file($file)->getSize();
                        $mimeType = $request->file($file)->getMimeType();
                        $type = explode('/', $mimeType)[0];
        
                        $dir = $userId;
        
                        $timestamp = time();
                        $extension = $request->file($file)->getClientOriginalExtension();
                        $namespace = config('elorest.namespace','elorest');
                        $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
        
                        $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                        $path = $disk->putFileAs($dir, $request->file($file), $name);
        
                        $input[$file] = $disk->url($path);
                        $input['origin_name'] = $originName;
                        $input['file_size'] = $size/1000;
                        $input['file_type'] = $mimeType;
                        $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                        $input['file_value'] = $name;
                        $input['type'] = $type;
                    } else {
                        $originName = $request->file($file)->getClientOriginalName();
                        $extension = $request->file($file)->extension();
                        $size = $request->file($file)->getSize();
                        $mimeType = $request->file($file)->getMimeType();
                        $type = explode('/', $mimeType)[0];
                        // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                        $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                        $path = $dir.DIRECTORY_SEPARATOR.$name;

                        if (realpath(storage_path($path))) {
                            return response(json_encode([
                                "code" => 200,
                                "status" => false,
                                "message" => "file already exist"
                            ], 200))
                                ->header('Content-Type', 'application/json');
                        }

                        $file = $request->file($file);
                        $file->move(storage_path($dir), $name);

                        if(realpath(storage_path($path))) {
                            $input[$file] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                            $input['origin_name'] = $originName;
                            $input['file_size'] = $size/1000;
                            $input['file_type'] = $mimeType;
                            $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                            $input['file_value'] = $name;
                            $input['type'] = $type;
                        }
                    }
                } else {
                    if($request->$file) {
                        if(base64_decode($request->$file, true) !== false) {
                            $mimeType = mime_content_type($request->$file);
                            $extension = explode('/', $mimeType)[1];
                            $type = explode('/', $mimeType)[0];
                            // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                            $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                            $path = $dir.DIRECTORY_SEPARATOR.$name;
                            file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->$file));

                            if(realpath(storage_path($path))) {
                                $input[$file] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                                $input['file_type'] = $mimeType;
                                $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                                $input['file_value'] = $name;
                                $input['type'] = $type;
                            }
                        }
                    }
                }
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

            $modelInstance = $data;
            if(method_exists($modelInstance, 'elorestAfterPostQuery')) {
                $elorestAfterPostQuery = $modelInstance->elorestAfterPostQuery($request, $data);
                if($elorestAfterPostQuery || $elorestAfterPostQuery === false) {
                    return $elorestAfterPostQuery;
                }
            }

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
        $input = $this->requestObj->requestAll($request);
        $userId = isset($user->id) ? $user->id : ($request->created_by ? : 0);

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel) {
            if(is_numeric($idOrModel)) {
                $data = new $modelNameSpace();

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforePutQuery')) {
                    $elorestBeforePutQuery = $modelInstance->elorestBeforePutQuery($request);
                    if($elorestBeforePutQuery || $elorestBeforePutQuery === false) {
                        return $elorestBeforePutQuery;
                    }
                }

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }

                // in put, error if run validate if reuired fields not available
                // $request->validate($modelNameSpace::$rules);
                // $input = $this->requestObj->requestAll($request);

                $data = $this->repositoryObj->findById($idOrModel, $data);
            } else {
                $modelNameSpace .= '\\'.$idOrModel;
                // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
                // if(class_exists($modelNameSpace)) {
                    $data = new $modelNameSpace();

                    $modelInstance = $data;
                    if(method_exists($modelInstance, 'elorestBeforePutQuery')) {
                        $elorestBeforePutQuery = $modelInstance->elorestBeforePutQuery($request);
                        if($elorestBeforePutQuery || $elorestBeforePutQuery === false) {
                            return $elorestBeforePutQuery;
                        }
                    }

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
                // $input = $this->requestObj->requestAll($request);

                if($id && is_numeric($id)) {
                    $data = $this->repositoryObj->findById($id, $data);
                } else {
                    // $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first(); // laravel 10 will update many rows
                    $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data);
                }
            }
        } else {
            // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
            // if(class_exists($modelNameSpace)) {
                $data = new $modelNameSpace();

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforePutQuery')) {
                    $elorestBeforePutQuery = $modelInstance->elorestBeforePutQuery($request);
                    if($elorestBeforePutQuery || $elorestBeforePutQuery === false) {
                        return $elorestBeforePutQuery;
                    }
                }

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
            // $input = $this->requestObj->requestAll($request);

            // $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first(); // laravel 10 will update many rows
            $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data);
        }

        if($data) {
            // if($user) {
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
                            "params" => $input,
                            "links" => [
                                "self" => URL::current()
                            ]
                        ], 403);
                    }
                }
            // }

            // $savePath = env('SAVE_PATH'); // SAVE_PATH=./app/public/uploads/
            $savePath = './app/public/uploads/';
            $dir = str_replace('./','',$savePath).$userId;
            $dir = str_replace('/',DIRECTORY_SEPARATOR,$dir);
            
            if (!storage_path($dir)) {
                mkdir(storage_path($dir), 0777, true);
            }

            if($request->hasFile('file')) {
                if(config('elorest.storage') == 'minio') {
                    $originName = $request->file('file')->getClientOriginalName();
                    $extension = $request->file('file')->extension();
                    $size = $request->file('file')->getSize();
                    $mimeType = $request->file('file')->getMimeType();
                    $type = explode('/', $mimeType)[0];
    
                    $dir = $userId;
    
                    $timestamp = time();
                    $extension = $request->file('file')->getClientOriginalExtension();
                    $namespace = config('elorest.namespace','elorest');
                    $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
    
                    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                    $path = $disk->putFileAs($dir, $request->file('file'), $name);
    
                    $input['file'] = $disk->url($path);
                    $input['origin_name'] = $originName;
                    $input['file_size'] = $size/1000;
                    $input['file_type'] = $mimeType;
                    $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                    $input['file_value'] = $name;
                    $input['type'] = $type;
                } else {
                    $originName = $request->file('file')->getClientOriginalName();
                    $extension = $request->file('file')->extension();
                    $size = $request->file('file')->getSize();
                    $mimeType = $request->file('file')->getMimeType();
                    $type = explode('/', $mimeType)[0];
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
        
                    if (realpath(storage_path($path))) {
                        return response(json_encode([
                            "code" => 200,
                            "status" => false,
                            "message" => "file already exist"
                        ], 200))
                            ->header('Content-Type', 'application/json');
                    }
        
                    $file = $request->file('file');
                    $file->move(storage_path($dir), $name);
        
                    if(realpath(storage_path($path))) {
                        $input['file'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                        $input['origin_name'] = $originName;
                        $input['file_size'] = $size/1000;
                        $input['file_type'] = $mimeType;
                        $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                        $input['file_value'] = $name;
                        $input['type'] = $type;
                    }
                }
            } else {
                if($request->file) {
                    if(base64_decode($request->file, true) !== false) {
                        $mimeType = mime_content_type($request->file);
                        $extension = explode('/', $mimeType)[1];
                        $type = explode('/', $mimeType)[0];
                        // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                        $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                        $path = $dir.DIRECTORY_SEPARATOR.$name;
                        file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->file));
    
                        if(realpath(storage_path($path))) {
                            $input['file'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                            $input['file_type'] = $mimeType;
                            $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                            $input['file_value'] = $name;
                            $input['type'] = $type;
                        }
                    }
                }
            }
    
            if($request->hasFile('image')) {
                if(config('elorest.storage') == 'minio') {
                    $dir = $userId;
    
                    $timestamp = time();
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $namespace = config('elorest.namespace','elorest');
                    $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
    
                    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                    $path = $disk->putFileAs($dir, $request->file('image'), $name);
    
                    $input['image'] = $disk->url($path);
                } else {
                    $extension = $request->file('image')->extension();
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
        
                    if (realpath(storage_path($path))) {
                        return response(json_encode([
                            "code" => 200,
                            "status" => false,
                            "message" => "file already exist"
                        ], 200))
                            ->header('Content-Type', 'application/json');
                    }
        
                    $file = $request->file('image');
                    $file->move(storage_path($dir), $name);
        
                    if(realpath(storage_path($path))) {
                        $input['image'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                    }
                }
            } else {
                if($request->image) {
                    if(base64_decode($request->image, true) !== false) {
                        $extension = explode('/', mime_content_type($request->image))[1];
                        // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                        $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                        $path = $dir.DIRECTORY_SEPARATOR.$name;
                        file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->image));
    
                        if(realpath(storage_path($path))) {
                            $input['image'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                        }
                    }
                }
            }
    
            if($request->hasFile('video')) {
                if(config('elorest.storage') == 'minio') {
                    $dir = $userId;
    
                    $timestamp = time();
                    $extension = $request->file('video')->getClientOriginalExtension();
                    $namespace = config('elorest.namespace','elorest');
                    $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
    
                    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                    $path = $disk->putFileAs($dir, $request->file('video'), $name);
    
                    $input['video'] = $disk->url($path);
                } else {
                    $extension = $request->file('video')->extension();
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
        
                    if (realpath(storage_path($path))) {
                        return response(json_encode([
                            "code" => 200,
                            "status" => false,
                            "message" => "file already exist"
                        ], 200))
                            ->header('Content-Type', 'application/json');
                    }
        
                    $file = $request->file('video');
                    $file->move(storage_path($dir), $name);
        
                    if(realpath(storage_path($path))) {
                        $input['video'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                    }
                }
            } else {
                if($request->video) {
                    if(base64_decode($request->video, true) !== false) {
                        $extension = explode('/', mime_content_type($request->video))[1];
                        // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                        $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                        $path = $dir.DIRECTORY_SEPARATOR.$name;
                        file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->video));
    
                        if(realpath(storage_path($path))) {
                            $input['video'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                        }
                    }
                }
            }
    
            if(property_exists($modelNameSpace, 'elorestFileFields')) {
                foreach($modelNameSpace::$elorestFileFields as $file) {
                    if($request->hasFile($file)) {
                        if(config('elorest.storage') == 'minio') {
                            $originName = $request->file($file)->getClientOriginalName();
                            $extension = $request->file($file)->extension();
                            $size = $request->file($file)->getSize();
                            $mimeType = $request->file($file)->getMimeType();
                            $type = explode('/', $mimeType)[0];
            
                            $dir = $userId;
            
                            $timestamp = time();
                            $extension = $request->file($file)->getClientOriginalExtension();
                            $namespace = config('elorest.namespace','elorest');
                            $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
            
                            $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                            $path = $disk->putFileAs($dir, $request->file($file), $name);
            
                            $input[$file] = $disk->url($path);
                            $input['origin_name'] = $originName;
                            $input['file_size'] = $size/1000;
                            $input['file_type'] = $mimeType;
                            $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                            $input['file_value'] = $name;
                            $input['type'] = $type;
                        } else {
                            $originName = $request->file($file)->getClientOriginalName();
                            $extension = $request->file($file)->extension();
                            $size = $request->file($file)->getSize();
                            $mimeType = $request->file($file)->getMimeType();
                            $type = explode('/', $mimeType)[0];
                            // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                            $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                            $path = $dir.DIRECTORY_SEPARATOR.$name;
            
                            if (realpath(storage_path($path))) {
                                return response(json_encode([
                                    "code" => 200,
                                    "status" => false,
                                    "message" => "file already exist"
                                ], 200))
                                    ->header('Content-Type', 'application/json');
                            }
            
                            $file = $request->file($file);
                            $file->move(storage_path($dir), $name);
            
                            if(realpath(storage_path($path))) {
                                $input[$file] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                                $input['origin_name'] = $originName;
                                $input['file_size'] = $size/1000;
                                $input['file_type'] = $mimeType;
                                $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                                $input['file_value'] = $name;
                                $input['type'] = $type;
                            }
                        }
                    } else {
                        if($request->$file) {
                            if(base64_decode($request->$file, true) !== false) {
                                $mimeType = mime_content_type($request->$file);
                                $extension = explode('/', $mimeType)[1];
                                $type = explode('/', $mimeType)[0];
                                // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                                $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                                $path = $dir.DIRECTORY_SEPARATOR.$name;
                                file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->$file));
        
                                if(realpath(storage_path($path))) {
                                    $input[$file] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                                    $input['file_type'] = $mimeType;
                                    $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                                    $input['file_value'] = $name;
                                    $input['type'] = $type;
                                }
                            }
                        }
                    }
                }
            }

            if(!empty($input['created_by'])) {
                unset($input['created_by']);
            }
            if(!empty($input['deleted_at'])) {
                unset($input['deleted_at']);
            }
            if(property_exists($modelNameSpace, 'elorestPreventSetOnUpdate')) {
                foreach($modelNameSpace::$elorestPreventSetOnUpdate as $key) {
                    if(!empty($input[$key])) {
                        unset($input[$key]);
                    }
                }
            }
            $input['updated_by'] = $userId;

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
                $fillableFields = (new $modelNameSpace())->fillable;  
                if(is_array($fillableFields)) {
                    foreach($input as $k => $v) {
                        if(!in_array($k, $fillableFields)) {
                            unset($input[$k]);
                        }
                    }
                }

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestAfterPutQuery')) {
                    $elorestAfterPutQuery = $modelInstance->elorestAfterPutQuery($request, $data);
                    if($elorestAfterPutQuery || $elorestAfterPutQuery === false) {
                        return $elorestAfterPutQuery;
                    }
                }

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
        $input = $this->requestObj->requestAll($request);
        $userId = isset($user->id) ? $user->id : ($request->created_by ? : 0);

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel) {
            if(is_numeric($idOrModel)) {
                $data = new $modelNameSpace();

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforePatchQuery')) {
                    $elorestBeforePatchQuery = $modelInstance->elorestBeforePatchQuery($request);
                    if($elorestBeforePatchQuery || $elorestBeforePatchQuery === false) {
                        return $elorestBeforePatchQuery;
                    }
                }

                if(isset($data->elorest)) {
                    if($data->elorest == false) {
                        return 'restricted';
                    }
                }

                // in patch, error if run validate if reuired fields not available
                // $request->validate($modelNameSpace::$rules);
                // $input = $this->requestObj->requestAll($request);

                $data = $this->repositoryObj->findById($idOrModel, $data);
            } else {
                $modelNameSpace .= '\\'.$idOrModel;
                // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
                // if(class_exists($modelNameSpace)) {
                    $data = new $modelNameSpace();

                    $modelInstance = $data;
                    if(method_exists($modelInstance, 'elorestBeforePatchQuery')) {
                        $elorestBeforePatchQuery = $modelInstance->elorestBeforePatchQuery($request);
                        if($elorestBeforePatchQuery || $elorestBeforePatchQuery === false) {
                            return $elorestBeforePatchQuery;
                        }
                    }

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
                // $input = $this->requestObj->requestAll($request);

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

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforePatchQuery')) {
                    $elorestBeforePatchQuery = $modelInstance->elorestBeforePatchQuery($request);
                    if($elorestBeforePatchQuery || $elorestBeforePatchQuery === false) {
                        return $elorestBeforePatchQuery;
                    }
                }

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
            // $input = $this->requestObj->requestAll($request);

            $data = $this->serviceObj->getQuery($this->requestObj->requestParamAll($request), $data)->first();
        }

        if($data) {
            // if($user) {
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
                            "params" => $input,
                            "links" => [
                                "self" => URL::current()
                            ]
                        ], 403);
                    }
                }
            // }

            // $savePath = env('SAVE_PATH'); // SAVE_PATH=./app/public/uploads/
            $savePath = './app/public/uploads/';
            $dir = str_replace('./','',$savePath).$userId;
            $dir = str_replace('/',DIRECTORY_SEPARATOR,$dir);
            
            if (!storage_path($dir)) {
                mkdir(storage_path($dir), 0777, true);
            }

            if($request->hasFile('file')) {
                if(config('elorest.storage') == 'minio') {
                    $originName = $request->file('file')->getClientOriginalName();
                    $extension = $request->file('file')->extension();
                    $size = $request->file('file')->getSize();
                    $mimeType = $request->file('file')->getMimeType();
                    $type = explode('/', $mimeType)[0];
    
                    $dir = $userId;
    
                    $timestamp = time();
                    $extension = $request->file('file')->getClientOriginalExtension();
                    $namespace = config('elorest.namespace','elorest');
                    $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
    
                    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                    $path = $disk->putFileAs($dir, $request->file('file'), $name);
    
                    $input['file'] = $disk->url($path);
                    $input['origin_name'] = $originName;
                    $input['file_size'] = $size/1000;
                    $input['file_type'] = $mimeType;
                    $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                    $input['file_value'] = $name;
                    $input['type'] = $type;
                } else {
                    $originName = $request->file('file')->getClientOriginalName();
                    $extension = $request->file('file')->extension();
                    $size = $request->file('file')->getSize();
                    $mimeType = $request->file('file')->getMimeType();
                    $type = explode('/', $mimeType)[0];
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
        
                    if (realpath(storage_path($path))) {
                        return response(json_encode([
                            "code" => 200,
                            "status" => false,
                            "message" => "file already exist"
                        ], 200))
                            ->header('Content-Type', 'application/json');
                    }
        
                    $file = $request->file('file');
                    $file->move(storage_path($dir), $name);
        
                    if(realpath(storage_path($path))) {
                        $input['file'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                        $input['origin_name'] = $originName;
                        $input['file_size'] = $size/1000;
                        $input['file_type'] = $mimeType;
                        $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                        $input['file_value'] = $name;
                        $input['type'] = $type;
                    }
                }
            } else {
                if($request->file) {
                    if(base64_decode($request->file, true) !== false) {
                        $mimeType = mime_content_type($request->file);
                        $extension = explode('/', $mimeType)[1];
                        $type = explode('/', $mimeType)[0];
                        // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                        $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                        $path = $dir.DIRECTORY_SEPARATOR.$name;
                        file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->file));
    
                        if(realpath(storage_path($path))) {
                            $input['file'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                            $input['file_type'] = $mimeType;
                            $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                            $input['file_value'] = $name;
                            $input['type'] = $type;
                        }
                    }
                }
            }
    
            if($request->hasFile('image')) {
                if(config('elorest.storage') == 'minio') {
                    $dir = $userId;
    
                    $timestamp = time();
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $namespace = config('elorest.namespace','elorest');
                    $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
    
                    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                    $path = $disk->putFileAs($dir, $request->file('image'), $name);
    
                    $input['image'] = $disk->url($path);
                } else {
                    $extension = $request->file('image')->extension();
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
        
                    if (realpath(storage_path($path))) {
                        return response(json_encode([
                            "code" => 200,
                            "status" => false,
                            "message" => "file already exist"
                        ], 200))
                            ->header('Content-Type', 'application/json');
                    }
        
                    $file = $request->file('image');
                    $file->move(storage_path($dir), $name);
        
                    if(realpath(storage_path($path))) {
                        $input['image'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                    }
                }
            } else {
                if($request->image) {
                    if(base64_decode($request->image, true) !== false) {
                        $extension = explode('/', mime_content_type($request->image))[1];
                        // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                        $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                        $path = $dir.DIRECTORY_SEPARATOR.$name;
                        file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->image));
    
                        if(realpath(storage_path($path))) {
                            $input['image'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                        }
                    }
                }
            }
    
            if($request->hasFile('video')) {
                if(config('elorest.storage') == 'minio') {
                    $dir = $userId;
    
                    $timestamp = time();
                    $extension = $request->file('video')->getClientOriginalExtension();
                    $namespace = config('elorest.namespace','elorest');
                    $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
    
                    $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                    $path = $disk->putFileAs($dir, $request->file('video'), $name);
    
                    $input['video'] = $disk->url($path);
                } else {
                    $extension = $request->file('video')->extension();
                    // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                    $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                    $path = $dir.DIRECTORY_SEPARATOR.$name;
        
                    if (realpath(storage_path($path))) {
                        return response(json_encode([
                            "code" => 200,
                            "status" => false,
                            "message" => "file already exist"
                        ], 200))
                            ->header('Content-Type', 'application/json');
                    }
        
                    $file = $request->file('video');
                    $file->move(storage_path($dir), $name);
        
                    if(realpath(storage_path($path))) {
                        $input['video'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                    }
                }
            } else {
                if($request->video) {
                    if(base64_decode($request->video, true) !== false) {
                        $extension = explode('/', mime_content_type($request->video))[1];
                        // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                        $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                        $path = $dir.DIRECTORY_SEPARATOR.$name;
                        file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->video));
    
                        if(realpath(storage_path($path))) {
                            $input['video'] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                        }
                    }
                }
            }

            if(property_exists($modelNameSpace, 'elorestFileFields')) {
                foreach($modelNameSpace::$elorestFileFields as $file) {
                    if($request->hasFile($file)) {
                        if(config('elorest.storage') == 'minio') {
                            $originName = $request->file($file)->getClientOriginalName();
                            $extension = $request->file($file)->extension();
                            $size = $request->file($file)->getSize();
                            $mimeType = $request->file($file)->getMimeType();
                            $type = explode('/', $mimeType)[0];
            
                            $dir = $userId;
            
                            $timestamp = time();
                            $extension = $request->file($file)->getClientOriginalExtension();
                            $namespace = config('elorest.namespace','elorest');
                            $name = "{$userId}_{$request->model}_{$timestamp}.{$extension}";
            
                            $disk = \Illuminate\Support\Facades\Storage::disk('minio');
                            $path = $disk->putFileAs($dir, $request->file($file), $name);
            
                            $input[$file] = $disk->url($path);
                            $input['origin_name'] = $originName;
                            $input['file_size'] = $size/1000;
                            $input['file_type'] = $mimeType;
                            $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                            $input['file_value'] = $name;
                            $input['type'] = $type;
                        } else {
                            $originName = $request->file($file)->getClientOriginalName();
                            $extension = $request->file($file)->extension();
                            $size = $request->file($file)->getSize();
                            $mimeType = $request->file($file)->getMimeType();
                            $type = explode('/', $mimeType)[0];
                            // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                            $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                            $path = $dir.DIRECTORY_SEPARATOR.$name;
            
                            if (realpath(storage_path($path))) {
                                return response(json_encode([
                                    "code" => 200,
                                    "status" => false,
                                    "message" => "file already exist"
                                ], 200))
                                    ->header('Content-Type', 'application/json');
                            }
            
                            $file = $request->file($file);
                            $file->move(storage_path($dir), $name);
            
                            if(realpath(storage_path($path))) {
                                $input[$file] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                                $input['origin_name'] = $originName;
                                $input['file_size'] = $size/1000;
                                $input['file_type'] = $mimeType;
                                $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                                $input['file_value'] = $name;
                                $input['type'] = $type;
                            }
                        }
                    } else {
                        if($request->$file) {
                            if(base64_decode($request->$file, true) !== false) {
                                $mimeType = mime_content_type($request->$file);
                                $extension = explode('/', $mimeType)[1];
                                $type = explode('/', $mimeType)[0];
                                // $name = $user->id.'_'.$request->model.'_'.time().'.'.$extension;
                                $name = $userId.'_'.$request->model.'_'.preg_replace("/(\W)+/", '', microtime()).'.'.$extension;
                                $path = $dir.DIRECTORY_SEPARATOR.$name;
                                file_put_contents(str_replace('public'.DIRECTORY_SEPARATOR,'',$path),base64_decode($request->$file));
        
                                if(realpath(storage_path($path))) {
                                    $input[$file] = url('/storage').str_replace('app/public','',str_replace(DIRECTORY_SEPARATOR,'/',$path));
                                    $input['file_type'] = $mimeType;
                                    $input['file_path'] = $dir.DIRECTORY_SEPARATOR;
                                    $input['file_value'] = $name;
                                    $input['type'] = $type;
                                }
                            }
                        }
                    }
                }
            }

            if(!empty($input['deleted_at'])) {
                unset($input['deleted_at']);
            }
            if(property_exists($modelNameSpace, 'elorestPreventSetOnUpdate')) {
                foreach($modelNameSpace::$elorestPreventSetOnUpdate as $key) {
                    if(!empty($input[$key])) {
                        unset($input[$key]);
                    }
                }
            }
            $input['created_by'] = $userId;
            $input['updated_by'] = $userId;

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

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestAfterPatchQuery')) {
                    $elorestAfterPatchQuery = $modelInstance->elorestAfterPatchQuery($request, $data);
                    if($elorestAfterPatchQuery || $elorestAfterPatchQuery === false) {
                        return $elorestAfterPatchQuery;
                    }
                }

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
        $input = $this->requestObj->requestAll($request);

        $modelNameSpace = 'App\\'.$namespaceOrModel;

        if($idOrModel) {
            if(is_numeric($idOrModel)) {
                // TODO: check if $id exist and numeric
                // TODO: error handling ini di-comment supaya digunakan default error dr framework-nya
                // if(class_exists($modelNameSpace)) {
                    $data = new $modelNameSpace();

                    $modelInstance = $data;
                    if(method_exists($modelInstance, 'elorestBeforeDeleteQuery')) {
                        $elorestBeforeDeleteQuery = $modelInstance->elorestBeforeDeleteQuery($request);
                        if($elorestBeforeDeleteQuery || $elorestBeforeDeleteQuery === false) {
                            return $elorestBeforeDeleteQuery;
                        }
                    }

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

                    $modelInstance = $data;
                    if(method_exists($modelInstance, 'elorestBeforeDeleteQuery')) {
                        $elorestBeforeDeleteQuery = $modelInstance->elorestBeforeDeleteQuery($request);
                        if($elorestBeforeDeleteQuery || $elorestBeforeDeleteQuery === false) {
                            return $elorestBeforeDeleteQuery;
                        }
                    }

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

                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestBeforeDeleteQuery')) {
                    $elorestBeforeDeleteQuery = $modelInstance->elorestBeforeDeleteQuery($request);
                    if($elorestBeforeDeleteQuery || $elorestBeforeDeleteQuery === false) {
                        return $elorestBeforeDeleteQuery;
                    }
                }

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
            // if($user) {
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
                            "params" => $input,
                            "links" => [
                                "self" => URL::current()
                            ]
                        ], 403);
                    }
                }
            // }
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
                $modelInstance = $data;
                if(method_exists($modelInstance, 'elorestAfterDeleteQuery')) {
                    $elorestAfterDeleteQuery = $modelInstance->elorestAfterDeleteQuery($request, $data);
                    if($elorestAfterDeleteQuery || $elorestAfterDeleteQuery === false) {
                        return $elorestAfterDeleteQuery;
                    }
                }

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
