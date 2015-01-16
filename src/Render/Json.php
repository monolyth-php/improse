<?php

namespace Improse\Render;

class Json
{
    public function __invoke(array $exports)
    {
        header("Content-type: application/json", true);
        /*
        if (isset($_SERVER['HTTP_ORIGIN'])
            && in_array($_SERVER['HTTP_ORIGIN'], $project['origins'])
        ) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        } else {
            header("Access-Control-Allow-Origin: {$project['http']}");
        }
        */
        header("Access-Control-Allow-Headers: X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        echo json_encode($exports['data'], JSON_NUMERIC_CHECK);
    }
}

