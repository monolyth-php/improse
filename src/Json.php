<?php

namespace Improse;

class Json extends View
{
    private $viewdata = [];

    /*
    public function __construct()
    {
        //header("Content-type: application/json", true);
        //header("Access-Control-Allow-Headers: X-Requested-With");
        //header("Access-Control-Allow-Credentials: true");
        //header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
    */

    public function __invoke(array $__viewdata = [])
    {
        $__viewdata += $this->viewdata;
        return json_encode($__viewdata, JSON_NUMERIC_CHECK);
    }

    protected function headers()
    {
        static $called = false;
        if (!$called) {
            header("Content-type: application/json", true);
            $called = true;
        }
    }
}

