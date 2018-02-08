<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Global API for direct model class under Models directory
|--------------------------------------------------------------------------
|
| URL encode :
| %3D for =
| %3E for >
| %3C for <
| %21 for !
| %25 for %
|
| Example :
| api/JSON/Page?query[a][command]=where&query[a][column]=name&query[a][operator]=%3D&query[a][value]=arrival&query[b][command]=get
|
| not yet included sub query support, next will be copy subquery support from FrontController
|
*/
Route::get('JSON/{model}/{id?}', function(Request $request, $model, $id = NULL) {
    $modelNameSpace = 'App\Models\\'.$model;
    $data = new $modelNameSpace();

    if($id) {
        return $data->find($id);
    }

    if(array_key_exists('query', $request->all())) {
        foreach($request->all()['query'] as $query) {
            $queryFunc = $query['command'];

            if($queryFunc === 'latest') {
                $data = $data->latest();
            } else if(
                    $queryFunc === 'select' || 
                    $queryFunc === 'addSelect' || 
                    $queryFunc === 'groupBy' || 
                    $queryFunc === 'whereNull' || 
                    $queryFunc === 'whereNotNull' || 
                    $queryFunc === 'avg' || 
                    $queryFunc === 'max'
                ) {
                if(count(explode(',', $query['column'])) > 1) {
                    $data = $data->$queryFunc(explode(',', $query['column']));
                } else {
                    $data = $data->$queryFunc($query['column']);
                }
            } else if(
                    $queryFunc === 'where' || 
                    $queryFunc === 'orWhere' ||  
                    $queryFunc === 'whereDate' ||  
                    $queryFunc === 'whereMonth' ||  
                    $queryFunc === 'whereDay' ||  
                    $queryFunc === 'whereYear' ||  
                    $queryFunc === 'whereTime' ||  
                    $queryFunc === 'whereColumn' || 
                    $queryFunc === 'having'
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
                $data = $data->$queryFunc($query['column'], explode(',', $query['value']));
            } else if($queryFunc === 'orderBy') {
                $data = $data->$queryFunc($query['column'], $query['value']);
            } else if(
                    $queryFunc === 'selectRaw' || 
                    $queryFunc === 'offset' || 
                    $queryFunc === 'limit' || 
                    $queryFunc === 'with' ||
                    $queryFunc === 'whereRaw' ||
                    $queryFunc === 'orWhereRaw' ||
                    $queryFunc === 'orderByRaw' ||
                    $queryFunc === 'havingRaw'
                ) {
                $data = $data->$queryFunc($query['value']);
            } else if(
                    $queryFunc === 'join' ||
                    $queryFunc === 'leftJoin'
                ) {
                $value = explode(',', $query['value']);
                $data = $data->$queryFunc($value[0], $value[1], '=', $value[2]);
            }
        }

        $lastQuery = end($request->all()['query'])['command'];

        if($lastQuery === 'first') {
            $data = $data->first();
        } else if($lastQuery === 'inRandomOrder') {
            $data = $data->inRandomOrder();
        } else if($lastQuery === 'count') {
            $data = $data->count();
        } else if($lastQuery === 'max') {
            $data = $data->max(explode(',', end($request->all()['query'])['column']));
        } else if($lastQuery === 'avg') {
            $data = $data->avg(explode(',', end($request->all()['query'])['column']));
        } else {
            $data = $data->get();
        }
    } else {
        $data = $data->get();
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