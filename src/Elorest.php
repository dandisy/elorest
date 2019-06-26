<?php

namespace Webcore\Elorest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Webcore\Elorest\ElorestService;

class Elorest
{
    protected static $routes = [
        'get',
        'post',
        'put',
        'patch',
        'delete'
    ];

    protected function register($routes) {
        if(is_array($routes)) {
            array_merge(self::$routes, $routes);
        } else {
            array_push(self::$routes, $routes);
        }
    }

    static function routes(array $middleware = null) {
        $routes = self::$routes;

        if($middleware) {
            if(isset($middleware['only']))
            {
                foreach($middleware['only'] as $route) {
                    if(in_array($route, $routes)) {
                        // self::$route()->middleware($middleware['middleware']); // for laravel only
                        self::middleware($route, $middleware['middleware']);
                    }
                }

                $except = array_diff($routes, $middleware['only']);
                foreach($except as $route) {
                    self::$route();
                }
            } 
            else if(isset($middleware['except'])) 
            {
                $only = array_diff($routes, $middleware['except']);
                foreach($only as $route) {
                    self::middleware($route, $middleware['middleware']);
                }
                foreach($middleware['except'] as $route) {
                    self::$route();
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
                self::$route();
            }
        }
    }

    protected static function middleware($route, $middleware) {
        self::$route()->middleware($middleware);
    }

    protected static function get() {
        /*
        |--------------------------------------------------------------------------
        | EloREST
        |--------------------------------------------------------------------------
        |
        | Borrowing laravel eloquent commands syntax (methodes name & params),
        | including laravel pagination.
        |
        | Please, check again laravel documentation
        |
        | Example API query :
        | https://your-domain-name/api/elorest/Models/Post?leftJoin=comments,posts.id,comments.post_id&whereIn=category_id,[2,4,5]&select=*&get=
        | https://your-domain-name/api/elorest/Models/Post?join[]=authors,posts.id,authors.author_id&join[]=comments,posts.id,comments.post_id&whereIn=category_id,[2,4,5]&select=posts.*,authors.name as author_name,comments.title as comment_title&get=
        | https://your-domain-name/api/elorest/Models/Post?&with=author,comment&get=*
        | https://your-domain-name/api/elorest/Models/Post?&with=author(where=name,like,%dandisy%),comment&get=*
        | multi first nested closure deep
        | https://your-domain-name/api/elorest/Models/Post?&with=author(where=name,like,%dandisy%)(where=nick,like,%dandisy%),comment&get=*
        | second nested closure deep
        | https://your-domain-name/api/elorest/Models/Post?&with=author(with=city(where=name,like,%jakarta%)),comment&get=*
        | https://your-domain-name/api/elorest/Models/Post?&with[]=author(where=name,like,%dandisy%)&with[]=comment(where=title,like,%test%)&get=*
        | https://your-domain-name/api/elorest/Models/Post?paginate=10&page=1
        | class at App namespace
        | https://your-domain-name/api/elorest/User?paginate=10&page=1
        |
        */
        return Route::get('elorest/{namespaceOrModel}/{idOrModel?}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel = NULL, $id = NULL) {
            $modelNameSpace = 'App\\'.$namespaceOrModel;

            if($idOrModel == 'columns') {
                $data = new $modelNameSpace();
                return $data->getTableColumns();
            }
            if(is_numeric($idOrModel)) {
                $data = new $modelNameSpace();
                return $data->find($idOrModel);
            }
            if($idOrModel) {
                $modelNameSpace .= '\\'.$idOrModel;
                $data = new $modelNameSpace();

                if($id == 'columns') {
                    return $data->getTableColumns();
                }
                if(is_numeric($id)) {
                    return $data->find($id);
                }
            } else {
                $data = new $modelNameSpace();
            }

            // $input = $request->all();
            $input = self::requestAll($request);
            if(!$input) {
                // return $data->get();
                return self::getAll($data);
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

            // $elorestQuery = new ElorestService($input, $data);
            $elorestQuery = new ElorestService($input, $data);
            return $elorestQuery->invoke();
        });//->middleware('auth:api', 'throttle:60,1');
    }

    protected static function post() {
        return Route::post('elorest/{namespaceOrModel}/{model?}', function(Request $request, $namespaceOrModel, $model = null) {
            $modelNameSpace = 'App\\'.$namespaceOrModel;

            if(!$model) {
                $data = new $modelNameSpace();
            } else {
                $modelNameSpace .= '\\'.$model;
                $data = new $modelNameSpace();
            }

            // if($request->all()) {
            if(self::requestAll($request)) {
                // return $data->insert($request->all());
                // return $data->create($request->all());
                return self::createData(self::requestAll($request), $data);
            }

            // return response(json_encode([
            //     "status" => "error",
            //     "message" => "data input not valid"
            // ], 200))
            //     ->header('Content-Type', 'application/json');
            return self::responsJson("error", "data input not valid", 400);
        });//->middleware('auth:api', 'throttle:60,1');
    }

    protected static function put() {
        return Route::put('elorest/{namespaceOrModel}/{idOrModel}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel, $id = null) {
            $modelNameSpace = 'App\\'.$namespaceOrModel;

            if(is_numeric($idOrModel)) {
                $data = new $modelNameSpace();
            } else {
                $modelNameSpace .= '\\'.$idOrModel;
                $data = new $modelNameSpace();
            }

            // if($request->all()) {
            if(self::requestAll($request)) {
                if($id) {
                    // $data = $data->find($id);
                    $data = self::findById($id, $data);
                } else {
                    // $elorestQuery = new ElorestService($request->all(), $data);
                    $elorestQuery = new ElorestService(self::requestAll($request), $data);
                    $data = $elorestQuery->invoke()->first();
                }

                if($data) {
                    // return $data->update($request->all());
                    return self::updateData(self::requestAll($request), $data);
                }
            }

            // return response(json_encode([
            //     "status" => "error",
            //     "message" => "data input not valid"
            // ], 200))
            //     ->header('Content-Type', 'application/json');
            return self::responsJson("error", "data input not valid", 400);
        });//->middleware('auth:api', 'throttle:60,1');
    }

    protected static function patch() {
        return Route::patch('elorest/{namespaceOrModel}/{idOrModel}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel, $id = null) {
            $modelNameSpace = 'App\\'.$namespaceOrModel;

            if(is_numeric($idOrModel)) {
                $data = new $modelNameSpace();
            } else {
                $modelNameSpace .= '\\'.$idOrModel;
                $data = new $modelNameSpace();
            }

            // if($request->all()) {
            if(self::requestAll($request)) {
                if($id) {
                    // $data = $data->find($id);
                    $data = self::findById($id, $data);
                } else {
                    // $elorestQuery = new ElorestService($request->all(), $data);
                    $elorestQuery = new ElorestService(self::requestAll($request), $data);
                    $data = $elorestQuery->invoke()->first();
                }

                if($data) {
                    // return $data->delete();
                    self::deleteData($data);

                    // return $data->insert($request->all());
                    return self::insertData(self::requestAll($request), $data);
                }
            }

            // return response(json_encode([
            //     "status" => "error",
            //     "message" => "data input not valid"
            // ], 200))
            //     ->header('Content-Type', 'application/json');
            return self::responsJson("error", "data input not valid", 400);
        });//->middleware('auth:api', 'throttle:60,1');
    }

    protected static function delete() {
        return Route::delete('elorest/{namespaceOrModel}/{idOrModel}/{id?}', function(Request $request, $namespaceOrModel, $idOrModel, $id = null) {
            $modelNameSpace = 'App\\'.$namespaceOrModel;

            if(is_numeric($idOrModel)) {
                $data = new $modelNameSpace();
            } else {
                $modelNameSpace .= '\\'.$idOrModel;
                $data = new $modelNameSpace();
            }

            if($request->all()) {
                if($id) {
                    // $data = $data->find($id);
                    $data = self::findById($id, $data);
                } else {
                    // $elorestQuery = new ElorestService($request->all(), $data);
                    $elorestQuery = new ElorestService(self::requestAll($request), $data);
                    $data = $elorestQuery->invoke()->first();
                }

                if($data) {
                    // return $data->delete();
                    return self::deleteData($data);
                }
            }

            // return response(json_encode([
            //     "status" => "error",
            //     "message" => "data input not valid"
            // ], 200))
            //     ->header('Content-Type', 'application/json');
            return self::responsJson("error", "data input not valid", 400);
        });//->middleware('auth:api', 'throttle:60,1');
    }

    protected static function requestAll($request) {
        return $request->all();
    }

    protected static function findById($id, $data) {
        return $data->find($id);
    }

    protected static function getAll($data) {
        return $data->get();
    }

    protected static function createData($requestAll, $data) {
        return $data->create($requestAll);
    }

    protected static function insertData($requestAll, $data) {
        return $data->insert($requestAll);
    }

    protected static function updateData($requestAll, $data) {
        return $data->update($requestAll);
    }

    protected static function deleteData($data) {
        return $data->delete();
    }

    protected static function responsJson($status, $message, $code = 200, $data = null) {
        return response(json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ], $code))
            ->header('Content-Type', 'application/json');
    }
}
