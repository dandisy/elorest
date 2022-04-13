<?php

namespace Dandisy\Elorest\Http\Response;

interface IResponse
{
    function response($data, $code, $type);
}
