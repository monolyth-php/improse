<?php

namespace Improse\Render;

class Json
{
    public function __construct()
    {
        header("Content-type: application/json", true);
        header("Access-Control-Allow-Headers: X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }

    public function __invoke(array $exports)
    {
        echo json_encode($exports['data'], JSON_NUMERIC_CHECK);
    }
}

