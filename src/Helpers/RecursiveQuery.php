<?php

namespace Dandisy\Elorest\Helpers;

use Dandisy\Elorest\Helpers\ParensParser;

class RecursiveQuery
{
    public function __construct()
    {
        //
    }

    /*
     * Invoke methods of object model
     *
     * @param Object Model $data
     * @param $key
     * @param $param
     * @param $matches
     * @param $arrayParam
     * @return Collection $data
     */
    public function invoke($data, $key, $param, $matches, $arrayParam) {
        if(
            $key == 'truncate' ||
            $key == 'delete' ||
            $key == 'destroy' ||
            $key == 'softDeletes' ||
            $key == 'restore' ||
            $key == 'forceDelete' ||
            $key == 'save' ||
            $key == 'create' ||
            $key == 'fill' ||
            $key == 'insert' ||
            $key == 'firstOrCreate' ||
            $key == 'firstOrNew' ||
            $key == 'insertOrIgnore' ||
            $key == 'insertGetId' ||
            $key == 'update' ||
            $key == 'updateOrInsert' ||
            $key == 'increment' ||
            $key == 'decrement' ||
            $key == 'sharedLock' ||
            $key == 'lockForUpdate' ||
            $key == 'dd' ||

            // relationship
            $key == 'push' ||
            $key == 'createMany' ||
            $key == 'attach' ||
            $key == 'detach' ||
            $key == 'sync' ||
            $key == 'syncWithoutDetaching' ||
            $key == 'toggle' ||
            $key == 'updateExistingPivot'
            ) {
            return 'method not allowed';
        }

        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - data', [
            'data' => $data
        ]);
        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - key', [
            'key' => $key
        ]);
        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - param', [
            'param' => $param
        ]);
        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - matches', [
            'matches' => $matches
        ]);
        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - arrayParam', [
            'arrayParam' => $arrayParam
        ]);

        // $t = true;
        // if($t) {
            $p = new ParensParser();
            $resultParams = $p->parse($param);
            
            if($resultParams) {
                foreach($resultParams as $result) {
                    if($key == 'whereHas' || $key == 'whereDoesntHave') {
                        $data = $data->$key($resultParams[0], function($query) use ($resultParams) {
                            foreach($resultParams as $k => $item) {
                                if($k != 0) {
                                    $params = explode('=', trim($item[0]), 2);
                
                                    \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave query', [
                                        'query' => $query
                                    ]);
                                    \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave params', [
                                        'params' => $params
                                    ]);
                                    if(preg_match('/\[(.*?)\]/', $params[1], $arrParamMatch)) { // handling whereIn / whereNotIn / others with same syntax, due to whereIn params using whereIn('field', ['val_1', 'val_2', 'val_n']) syntax
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave whereIn arrParamMatch', [
                                            'arrParamMatch' => $arrParamMatch
                                        ]);
                                        $params = str_replace(','.$arrParamMatch[0], '', $params[1]);
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave whereIn params', [
                                            'params' => $params
                                        ]);
                                        $params = explode(',', trim($params));
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave whereIn params', [
                                            'params' => $params
                                        ]);
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave whereIn arg #1', [
                                            'arg #1' => array($query,$params[0])
                                        ]);
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave whereIn arg #2', [
                                            'arg #2' => array_push($params, explode(',', trim($arrParamMatch[1])))
                                        ]);
                                        call_user_func_array(array($query,$params[0]), array_push($params, explode(',', trim($arrParamMatch[1]))));
                                    } else {
                                        call_user_func_array(array($query,$params[0]), explode(',', trim($params[1])));
                                    }
                                }
                            }
                        });
                    } else {
                        $data = $data->$key([$resultParams[0] => function($query) use ($resultParams) {
                            foreach($resultParams as $k => $item) {
                                if($k != 0) {
                                    $params = explode('=', trim($item[0]), 2);
                
                                    \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - query', [
                                        'query' => $query
                                    ]);
                                    \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - params', [
                                        'params' => $params
                                    ]);
                                    if(preg_match('/\[(.*?)\]/', $params[1], $arrParamMatch)) { // handling whereIn / whereNotIn / others with same syntax, due to whereIn params using whereIn('field', ['val_1', 'val_2', 'val_n']) syntax
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereIn arrParamMatch', [
                                            'arrParamMatch' => $arrParamMatch
                                        ]);
                                        $params = str_replace(','.$arrParamMatch[0], '', $params[1]);
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereIn params', [
                                            'params' => $params
                                        ]);
                                        $params = explode(',', trim($params));
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereIn params', [
                                            'params' => $params
                                        ]);
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave whereIn arg #1', [
                                            'arg #1' => array($query,$params[0])
                                        ]);
                                        \Illuminate\Support\Facades\Log::info('Elorest - Recursive Query - whereHas whereDoesntHave whereIn arg #2', [
                                            'arg #2' => array_push($params, explode(',', trim($arrParamMatch[1])))
                                        ]);
                                        call_user_func_array(array($query,$params[0]), array_push($params, explode(',', trim($arrParamMatch[1]))));
                                    } else {
                                        call_user_func_array(array($query,$params[0]), explode(',', trim($params[1])));
                                    }
                                }
                            }
                        }]);
                    }
                }
            } else {
                $data = call_user_func_array(array($data,$key), [$param]);
            }
        // } else {
        //     // $arr = [
        //     //     "with=phone(where=city_code,021),where=city_code,021",
        //     //     "where=first_name,like,%test%"
        //     // ]
        //     foreach($matches[1] as $item) {
        //         $param = str_replace('('.$item.')', '|', $param); // signing using '|' for closure
        //     }

        //     $params = explode(',', $param);
        //     foreach($params as $i => $param) {
        //         if (strpos($param, '|')) {
        //             $param = rtrim($param, '|');
        //             $items = explode('|', $arrayParam[$i]);

        //             if(count($items) > 1) {
        //                 $data = $data->$key([$param => function($query) use ($items) {
        //                     $this->recursiveClosure($query, $items);
        //                     // this, only support second nested closure deep
        //                     // foreach($items as $idx => $val) {
        //                     //     if($idx < count($items)-1) {
        //                     //         $closureParam = $items[$idx+1];
        //                     //         $closure = str_replace('('.$closureParam.')', '', $val);

        //                     //         $closureData = explode('=', trim($closure), 2);

        //                     //         $query = $query->$closureData[0]([$closureData[1] => function($query) use ($closureParam) {
        //                     //             $closureParams = explode('=', trim($closureParam), 2);

        //                     //             call_user_func_array(array($query,$closureParams[0]), explode(',', trim($closureParams[1])));
        //                     //         }]);
        //                     //     }
        //                     // }
        //                 }]);
        //             } else {
        //                 $item = $matches[1][$i];

        //                 if($key == 'whereHas' || $key == 'whereDoesntHave') {
        //                     $data = $data->$key($param, function($query) use ($item) {
        //                         $params = explode('=', trim($item), 2);
            
        //                         call_user_func_array(array($query,$params[0]), explode(',', trim($params[1])));
        //                     });
        //                 } else {
        //                     $data = $data->$key([$param => function($query) use ($item) {
        //                         $params = explode('=', trim($item), 2);
            
        //                         call_user_func_array(array($query,$params[0]), explode(',', trim($params[1])));
        //                     }]);
        //                 }
        //             }
        //         } else {
        //             if($arrayParam) {
        //                 foreach($arrayParam as $item) {
        //                     $param = rtrim($params[0], '|');
        //                     $data = $data->$key([$param => function($query) use ($item) {
        //                         $params = explode('=', trim($item), 2);
            
        //                         call_user_func_array(array($query,$params[0]), explode(',', trim($params[1])));
        //                     }]);
        //                 }
        //             } else {
        //                 $data = call_user_func_array(array($data,$key), [$param]);
        //             }
        //         }
        //     }
        // }
    
        // return [
        //     'param' => $param,
        //     'data' => $data
        // ];
        return $data;
    }

    /*
     * Handling nested query (closure) recursively in object model
     *
     * @param $query
     * @param $items
     * @return void
     */
    protected function recursiveClosure($query, $items) {
        foreach($items as $idx => $val) {
            if($idx < count($items)-2) {
                $closureParam = $items[$idx+1];
                $closure = str_replace('('.$closureParam.')', '', $val);
                $closureData = explode('=', trim($closure), 2);
    
                $query = $query->$closureData[0]([$closureData[1] => function($query) use ($items) {
                    $this->recursiveClosure($query, array_shift($items));
                }]);
            } else {
                if($idx < count($items)-1) {
                    $closureParam = $items[$idx+1];
                    $closure = str_replace('('.$closureParam.')', '', $val);
                    $closureData = explode('=', trim($closure), 2);
    
                    $query = $query->$closureData[0]([$closureData[1] => function($query) use ($closureParam) {
                        $closureParams = explode('=', trim($closureParam), 2);
    
                        call_user_func_array(array($query,$closureParams[0]), explode(',', trim($closureParams[1])));
                    }]);
                }
            }
        }
    }
}
