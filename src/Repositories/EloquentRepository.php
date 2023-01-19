<?php

namespace Dandisy\Elorest\Repositories;

// use Dandisy\Elorest\Repositories\IRepository;

class EloquentRepository implements IRepository
{
    public function __construct()
    {
        //
    }

    public function findById($id, $data) {
        return $data->find($id);
    }

    public function getAll($data) {
        $filterResult = $data;
        if(method_exists($filterResult, 'elorestViewAllFilter')) {
            $data = $filterResult->elorestViewAllFilter($data);
        };
        
        return $data->get();
    }

    public function createData($requestAll, $data) {
        return $data->create($requestAll);
    }

    public function insertData($requestAll, $data) {
        return $data->insert($requestAll);
    }

    public function updateData($requestAll, $data) {
        return $data->update($requestAll);
    }

    public function deleteData($data) {
        return $data->delete();
    }

    public function getTableColumns($data) {
        return $data->getTableColumns();
    }
}
