<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('dxDatagrid/test-datagrid-service', function(Request $request) {
    $modelNS = \App\Models\Test::select('tests.*', 'categories.name as category')
        ->join('categories', 'categories.id', '=', 'tests.category_id');

    $dx = new \App\Services\DatagridService($modelNS);

    return $dx->invoke($request);
});

Route::get('dxDatagrid/{model}', function($model, Request $request) {
    $res['info'] = [];
    $res['info']['request'] = $request->all();
    
    $modelNS = '\App\Models\\'.$model;
    $data = new $modelNS();
    
    if($request->filter) {
        $filter = json_decode($request->filter);
        
        // $res['info']['filter'] = $filter;
        
        if($filter[1] == 'and' || $filter[1] == 'or') {
            if($filter[1] == 'and') {
                if($filter[2][1] == 'or') {
                    if($filter[0][1] == 'and') {
                        foreach($filter[0] as $k0 => $f0) {
                            if($k0 % 2 == 0) {
                                if($f0[1] == 'contains') {
                                    $f0[1] = 'like';
                                    $f0[2] = '%'.$f0[2].'%';
                                }
                                
                                $data = $data->where($f0[0], $f0[1], $f0[2]);
                            }
                        }
                    } else {
                        if($filter[0][1] == 'contains') {
                            $filter[0][1] = 'like';
                            $filter[0][2] = '%'.$filter[0][2].'%';
                        }
                        
                        $data = $data->where($filter[0][0], $filter[0][1], $filter[0][2]);
                    }
                    
                    $data = $data->where(function($query) use ($filter) {
                        foreach($filter[2] as $k2 => $f2) {
                            if($k2 % 2 == 0) {
                                if($f2[1] == 'contains') {
                                    $f2[1] = 'like';
                                    $f2[2] = '%'.$f2[2].'%';
                                }
                                
                                $query = $query->orWhere($f2[0], $f2[1], $f2[2]);
                            }
                        }
                    });
                } else {
                    foreach($filter as $k1 => $f1) {
                        if($k1 % 2 == 0) {
                            if($f1[1] == 'contains') {
                                $f1[1] = 'like';
                                $f1[2] = '%'.$f1[2].'%';
                            }
                            
                            $data = $data->where($f1[0], $f1[1], $f1[2]);
                        }
                    }
                }
            }
            if($filter[1] == 'or') {
                foreach($filter as $k1 => $f1) {
                    if($k1 % 2 == 0) {
                        if($f1[1] == 'contains') {
                            $f1[1] = 'like';
                            $f1[2] = '%'.$f1[2].'%';
                        }
                        
                        $data = $data->orWhere($f1[0], $f1[1], $f1[2]);
                    }
                }
            }
        } else {
            if($filter[1] == 'contains') {
                $filter[1] = 'like';
                $filter[2] = '%'.$filter[2].'%';
            }
            
            $data = $data->where($filter[0], $filter[1], $filter[2]);
        }
    }
    
    // TODO : cek logic untuk yang custom summary
    if($request->totalSummary) {
        $res['summary'] = [];
        $summaries = json_decode($request->totalSummary);
        
        foreach($summaries as $summary) {
            $cmd = $summary->summaryType;
            array_push($res['summary'], $data->$cmd($summary->selector));
        }
    }
    
    if($request->requireTotalCount == 'true') {
        $res['totalCount'] = $data->count();
    }
    
    if($request->group) {
        $resData = [];        
        $groups = json_decode($request->group); 
        
        // TODO : cek lagi apakah group selalu array count = 1 atau bisa lebih dari 1 sehingga butuh foreach
        $gData0 = $data->groupBy($groups[0]->selector);
        
        if($request->requireGroupCount == 'true') {
            $res['groupCount'] = count($gData0->get()->toArray());
        }
        
        if($groups[0]->desc) {
            $gData0 = $gData0->orderBy($groups[0]->selector, 'desc');
        } else {
            $gData0 = $gData0->orderBy($groups[0]->selector);
        }
        
        $res['info']['sql'] = $gData0->toSql();
        
        // group inner data
        $gData0 = $gData0->get();
        foreach($gData0 as $key => $item) {
            $resData[$key] = [];
            $resData[$key]['key'] = $item->{$groups[0]->selector};
            
            $inGroupData = $data->where($groups[0]->selector, $item->{$groups[0]->selector});
            
            if($request->sort) {
                $sorts = json_decode($request->sort);
                
                // data yang di group by kemudian di sort, array sort-nya bisa lebih dari 1 sehingga butuh foreach
                foreach($sorts as $sort) {
                    if(isset($sort->isExpanded)) {
                        // TODO : terjadi ketika kolom sevagai group by di sort
                        if($sort->isExpanded) {
                            //
                        }
                    }
                    
                    if($sort->desc) {
                        $inGroupData = $inGroupData->orderBy($sort->selector, 'desc');
                    } else {
                        $inGroupData = $inGroupData->orderBy($sort->selector);
                    }
                }
            }
            
            if($groups[0]->isExpanded == 'true') {
                $resData[$key]['items'] = $inGroupData->get();
            } else {
                $resData[$key]['items'] = null;
            }
            
            $resData[$key]['count'] =  $inGroupData->count();
            
            // TODO : cek hasilnya apakah sudah OK, dan cek logic untuk yang custom summary
            if($request->groupSummary) {
                $resData[$key]['summary'] = [];
                $gSummaries = json_decode($request->groupSummary);
                
                foreach($gSummaries as $gSummary) {
                    $gCmd = $gSummary->summaryType;
                    array_push($resData[$key]['summary'], $inGroupData->$gCmd($gSummary->selector));
                }
            }
        }
        
        // group header data
        $resData = collect($resData);
        if($request->skip) {
            $resData = $resData->slice($request->skip);
        }        
        $res['data'] = $resData->take($request->take);
    } else {
        if($request->sort) {
            $sorts = json_decode($request->sort);
            
            // TODO : cek apakah sort pada data yang tidak di grouping selalu array count = 1 atau bisa lebih dari 1 sehingga butuh foreach
            foreach($sorts as $sort) {
                if(isset($sort->isExpanded)) {
                    // TODO : terjadi ketika kolom sevagai group by di sort
                    if($sort->isExpanded) {
                        //
                    }
                }
                
                if($sort->desc) {
                    $data = $data->orderBy($sort->selector, 'desc');
                } else {
                    $data = $data->orderBy($sort->selector);
                }
            }
        }
        
        if($request->skip) {
            $data = $data->skip($request->skip);
        }
        if($request->take) {
            $data = $data->take($request->take);
        }
        
        $res['info']['sql'] = $data->toSql();
        
        $res['data'] = $data->get();
    }
    
    return $res;
    
    // return response(json_encode($res, 200))
    //     ->header('Content-Type', 'application/json');
});
