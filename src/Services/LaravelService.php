<?php

namespace Webcore\Elorest\Service;

use Webcore\Elorest\Service\AService;

class LaravelService extends AService
{
    public function __construct()
    {
        //
    }

    public function getQuery($input, $data) {
        foreach($input as $key => $val) {
            if($key !== 'page') {
                $vals = [];
    
                if(is_array($val)) {
                    $vals = $val;
                } else {
                    array_push($vals, $val);
                }
    
                $data = $this->invokeQuery($data, $key, $vals);
            }

            if($key === 'paginate') {
                $this->appendsPaginateLinks($val, $data);
            }
        }
    
        return $data;
    }

    protected function appendsPaginateLinks($input, $data) {
        return $data->appends(['paginate' => $input])->links();
    }

    protected function paginate($data, $key, $param) {
        return $this->callUserFuncArray($data, $key, $param);
    }

    protected function processQuery($data, $key, $param) {
        if($key === 'paginate') {
            $data = $this->paginate($data, $key, $param);
        } else {
            $data = $this->callUserFuncArray($data, $key, count($param) == 1 ? [$param] : $param);
        }

        return $data;
    }
}
