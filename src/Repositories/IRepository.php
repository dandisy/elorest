<?php

namespace Dandisy\Elorest\Repositories;

interface IRepository
{    
    function findById($id, $data);
    
    function getAll($data);
    
    function createData($requestAll, $data);
    
    function insertData($requestAll, $data);
    
    function updateData($requestAll, $data);
    
    function deleteData($data);

    function getTableColumns($data);
}
