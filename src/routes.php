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
| https://your-domain-name/JSON/Post?&with=author,comment&select=*&get=
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
                if(preg_match('/\[(.*?)\]/', $item, $match)) { // due to whereIn, the $val using [...] syntax
                    $item = str_replace(','.$match[0], '', $item);
                    $item = explode(',', $item);
                    array_push($item, explode(',',$match[1]));
                } else {
                    $item = explode(',', $item);
                }

                $data = call_user_func_array(array($data,$key), $item);
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
// End global API for direct model class under Models directory
