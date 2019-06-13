<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| EloREST - Using Password Grant
|--------------------------------------------------------------------------
|
| Borrowing laravel eloquent commands syntax (methodes name & params),
| including laravel pagination.
|
| Please, check again laravel documentation
|
| Example API query :
| https://your-domain-name/JSON/Post?leftJoin=comments,posts.id,comments.post_id&whereIn=category_id,[2,4,5]&select=*&get=
| https://your-domain-name/JSON/Post?join[]=authors,posts.id,authors.author_id&join[]=comments,posts.id,comments.post_id&whereIn=category_id,[2,4,5]&select=posts.*,authors.name as author_name,comments.title as comment_title&get=
| https://your-domain-name/JSON/Post?&with=author,comment&get=*
| https://your-domain-name/JSON/Post?&with=author(where=name,like,%dandisy%),comment&get=*
| multi first nested closure deep
| https://your-domain-name/JSON/Post?&with=author(where=name,like,%dandisy%)(where=nick,like,%dandisy%),comment&get=*
| second nested closure deep
| https://your-domain-name/JSON/Post?&with=author(with=city(where=name,like,%jakarta%)),comment&get=*
| https://your-domain-name/JSON/Post?&with[]=author(where=name,like,%dandisy%)&with[]=comment(where=title,like,%test%)&get=*
| https://your-domain-name/JSON/Post?paginate=10&page=1
|
*/
Route::get('JSON/{model}/{id?}', function(Request $request, $model, $id = NULL) {
    $paginate = null;
    $query = $request->all();
    $modelNameSpace = 'App\Models\\'.$model;
    $data = new $modelNameSpace();

    if($id == 'columns') {
        return $data->getTableColumns();
    }

    if($id) {
        return $data->find($id);
    }
    if(!$query) {
        return $data->get();
    }

    foreach($query as $key => $val) {
        if($key === 'paginate') {
            $paginate = $val;
        }
        if($key !== 'page') {
            $vals = [];

            if(is_array($val)) {
                $vals = $val;
            } else {
                array_push($vals, $val);
            }

            foreach($vals as $item) {
                // if(preg_match('/\[(.*?)\]/', $item, $match)) { // due to whereIn, the $val using [...] syntax
                //     $item = str_replace(','.$match[0], '', $item);
                //     $item = explode(',', trim($item));
                //     array_push($item, explode(',', trim($match[1])));
                // } else {
                //     $item = explode(',', item($item));
                // }

                // $data = call_user_func_array(array($data,$key), $item);

                $data = getData($data, $key, $item);//['data'];

            }

            if($key === 'paginate') {
                $data->appends(['paginate' => $paginate])->links();
            }
        }
    }

    return $data;
})->middleware('auth:api');

Route::post('JSON/{model}', function(Request $request, $model) {
    $modelNameSpace = 'App\Models\\'.$model;
    $data = new $modelNameSpace();

    if($request->all()) {
        //return $data->insert($request->all());
        return $data->create($request->all());
    }
})->middleware('auth:api');

Route::put('JSON/{model}/{id?}', function(Request $request, $model, $id = NULL) {
    $data = NULL;
    $modelNameSpace = 'App\Models\\'.$model;
    $data = new $modelNameSpace();

    if($id) {
        $data = $data->where('id', $id); 
    } else if(array_key_exists('query', $request->all())) {
        foreach($request->all()['query'] as $query) {
            $queryFunc = $query['command'];

            if(
                    $queryFunc === 'whereNull' || 
                    $queryFunc === 'whereNotNull'
                ) {
                $data = $data->$queryFunc(explode(',', $query['column']));
            } else if(
                    $queryFunc === 'where' || 
                    $queryFunc === 'orWhere' ||  
                    $queryFunc === 'whereDate' ||  
                    $queryFunc === 'whereMonth' ||  
                    $queryFunc === 'whereDay' ||  
                    $queryFunc === 'whereYear' ||  
                    $queryFunc === 'whereTime' ||  
                    $queryFunc === 'whereColumn'
                ) {
                $data = $data->$queryFunc(
                    $query['column'], 
                    $query['operator'], 
                    $query['value']
                );
            } else if(
                    $queryFunc === 'whereIn' || 
                    $queryFunc === 'whereNotIn' || 
                    $queryFunc === 'whereBetween' || 
                    $queryFunc === 'whereNotBetween'
                ) {
                $data = $data->$queryFunc($query['column'], $query['value']);
            } else if(
                    $queryFunc === 'whereRaw' ||
                    $queryFunc === 'orWhereRaw'
                ) {
                $data = $data->$queryFunc($query['value']);
            }
        }
    }

    if($data) {
        if($request->all()) {
            $req = $request->all();
            
            if(array_key_exists('query', $request->all())) {
                unset($req['query']);
            }

            $getData = $data;

            $updated = $data->update($req);

            if($updated) {
                return $getData->get();
            }
        }
            
        return "No data updated in table!";
    }

    return "No data available in table!";
})->middleware('auth:api');

Route::patch('JSON/{model}/{id}', function(Request $request, $model, $id) {
    $data = NULL;
    $modelNameSpace = 'App\Models\\'.$model;
    $data = new $modelNameSpace();

    if($id) {
        $data = $data->where('id', $id); 
    } else if(array_key_exists('query', $request->all())) {
        foreach($request->all()['query'] as $query) {
            $queryFunc = $query['command'];

            if(
                    $queryFunc === 'whereNull' || 
                    $queryFunc === 'whereNotNull'
                ) {
                $data = $data->$queryFunc(explode(',', $query['column']));
            } else if(
                    $queryFunc === 'where' || 
                    $queryFunc === 'orWhere' ||  
                    $queryFunc === 'whereDate' ||  
                    $queryFunc === 'whereMonth' ||  
                    $queryFunc === 'whereDay' ||  
                    $queryFunc === 'whereYear' ||  
                    $queryFunc === 'whereTime' ||  
                    $queryFunc === 'whereColumn'
                ) {
                $data = $data->$queryFunc(
                    $query['column'], 
                    $query['operator'], 
                    $query['value']
                );
            } else if(
                    $queryFunc === 'whereIn' || 
                    $queryFunc === 'whereNotIn' || 
                    $queryFunc === 'whereBetween' || 
                    $queryFunc === 'whereNotBetween'
                ) {
                $data = $data->$queryFunc($query['column'], $query['value']);
            } else if(
                    $queryFunc === 'whereRaw' ||
                    $queryFunc === 'orWhereRaw'
                ) {
                $data = $data->$queryFunc($query['value']);
            }
        }
    }

    if($data) {
        if($request->all()) {
            $req = $request->all();
            
            if(array_key_exists('query', $request->all())) {
                unset($req['query']);
            }

            $data->delete();

            // $updated = $data->update($req);

            // if($updated) {
            //     return $getData->get();
            // }
    
            return $data->insert($request->all());
        }
            
        return "No data updated in table!";
    }

    return "No data available in table!";
})->middleware('auth:api');

Route::delete('JSON/{model}/{id}', function(Request $request, $model, $id) {
    $modelNameSpace = 'App\Models\\'.$model;
    $data = new $modelNameSpace();

    $data = $data->find($id);

    if($data) {
        return $data->delete();
    }
})->middleware('auth:api');


/*
|--------------------------------------------------------------------------
| EloREST - Using client credentials
|--------------------------------------------------------------------------
|
*/
Route::group(['middleware' => 'client_credentials'], function () {
    // EloREST script here!
});


/*
|--------------------------------------------------------------------------
| EloREST - Halpers
|--------------------------------------------------------------------------
|
| getData
|
*/
function getData($data, $key, $param) {
    // if(preg_match_all('/\((.*?)\)/', $request->test, $match)) { // multi occurence
    //     return $match;
    // }

    // if(preg_match('/(.*?)\((.*?)\)/', $param, $closureMatch)) { // handling closure, this only support once nested closure
    //     $param = str_replace('('.$closureMatch[2].')', '', $param);
    //     $param = explode(',', $param);

    //     foreach($param as $par) {
    //         if($par == $closureMatch[1]) {
    //             $data = $data->$key([$closureMatch[1] => function($closureQuery) use ($closureMatch) {
    //                 $closureParams = explode('=', trim($closureMatch[2]));
    //                 $closureParam = getData($closureQuery, $closureParams[0], $closureParams[1])['param'];

    //                 call_user_func_array(array($closureQuery,$closureParams[0]), $closureParam);
    //             }]);
    //         } else {
    //             $data = $data->$key($par);
    //         }
    //     }
    if(preg_match_all("/\((([^()]*|(?R))*)\)/", $param, $closureMatch)) { // handling closure, support multiple nested closure deep
        // $closureMatch[1] = [
        //     "contactPerson(with=phone(where=city_code,021))(where=first_name,like,%test%)",
        //     "organization(where=name,like,%test%)",
        //     "product"
        // ]
        $arrayParam = recursiveParam($param);
        if(count($arrayParam) > 0) {
            $data = recursiveQuery($data, $key, $param, $closureMatch, $arrayParam);//['data'];
        }
    } else {
        if(preg_match('/\[(.*?)\]/', $param, $arrParamMatch)) { // handling whereIn, due to whereIn params using whereIn('field', ['val_1', 'val_2', 'val_n']) syntax
            $param = str_replace(','.$arrParamMatch[0], '', $param);
            $param = explode(',', trim($param));
            array_push($param, explode(',', trim($arrParamMatch[1])));
        } else {
            $param = explode(',', trim($param));
        }

        $data = call_user_func_array(array($data,$key), $param);
    }

    // return [
    //     'param' => $param,
    //     'data' => $data
    // ];
    return $data;
}

/*
|--------------------------------------------------------------------------
| EloREST - Halpers
|--------------------------------------------------------------------------
|
| recursiveQuery
|
*/
function recursiveQuery($data, $key, $param, $matches, $arrayParam) {
    // $arr = [
    //     "with=phone(where=city_code,021),where=city_code,021",
    //     "where=first_name,like,%test%"
    // ]
    foreach($matches[1] as $item) {
        $param = str_replace('('.$item.')', '|', $param); // signing using '|' for closure
    }

    $params = explode(',', $param);
    foreach($params as $i => $param) {
        if (strpos($param, '|')) {
            $param = rtrim($param, '|');
            $items = explode('|', $arrayParam[$i]);

            if(count($items) > 1) {
                $data = $data->$key([$param => function($query) use ($items) {
                    recursiveClosure($items);
                    // this, only support second nested closure deep
                    // foreach($items as $idx => $val) {
                    //     if($idx < count($items)-1) {
                    //         $closureParam = $items[$idx+1];
                    //         $closure = str_replace('('.$closureParam.')', '', $val);

                    //         $closureData = explode('=', trim($closure));

                    //         $query = $query->$closureData[0]([$closureData[1] => function($query) use ($closureParam) {
                    //             $closureParams = explode('=', trim($closureParam));

                    //             call_user_func_array(array($query,$closureParams[0]), explode(',', trim($closureParams[1])));
                    //         }]);
                    //     }
                    // }
                }]);
            } else {
                $item = $matches[1][$i];

                $data = $data->$key([$param => function($query) use ($item) {
                    $params = explode('=', trim($item));

                    call_user_func_array(array($query,$params[0]), explode(',', trim($params[1])));
                }]);
            }
        } else {
            $data = call_user_func_array(array($data,$key), [$param]);
        }
    }

    // return [
    //     'param' => $param,
    //     'data' => $data
    // ];
    return $data;
}

/*
|--------------------------------------------------------------------------
| EloREST - Halpers
|--------------------------------------------------------------------------
|
| recursiveClosure
|
*/
function recursiveClosure($items) {
    foreach($items as $idx => $val) {
        if($idx < count($items)-2) {
            $closureParam = $items[$idx+1];
            $closure = str_replace('('.$closureParam.')', '', $val);
            $closureData = explode('=', trim($closure));

            $query = $query->$closureData[0]([$closureData[1] => function($query) use ($items) {
                recursiveClosure(array_shift($items));
            }]);
        } else {
            if($idx < count($items)-1) {
                $closureParam = $items[$idx+1];
                $closure = str_replace('('.$closureParam.')', '', $val);
                $closureData = explode('=', trim($closure));

                $query = $query->$closureData[0]([$closureData[1] => function($query) use ($closureParam) {
                    $closureParams = explode('=', trim($closureParam));

                    call_user_func_array(array($query,$closureParams[0]), explode(',', trim($closureParams[1])));
                }]);
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| EloREST - Halpers
|--------------------------------------------------------------------------
|
| recursiveParam
|
*/
function recursiveParam($param) {
    $layer = 0;
    $arrayParam = [];

    preg_match_all("/\((([^()]*|(?R))*)\)/", $param, $matches);
    if (count($matches) > 1) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            if (is_string($matches[1][$i])) {
                if (strlen($matches[1][$i]) > 0) {
                    array_push($arrayParam, $matches[1][$i]);

                    $res = recursiveParam($matches[1][$i], $layer + 1);

                    if(count($res) > 0) {
                        $arrayParam[$i] = $arrayParam[$i].'|'.$res[0];
                    }
                }
            }
        }
    } else {
        array_push($arrayParam, $param);
    }

    return $arrayParam;
}
