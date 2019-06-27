<?php

namespace Webcore\Elorest\Http\Response;

interface IResponse
{
    function responsJson($status, $message, $code, $data);
}
