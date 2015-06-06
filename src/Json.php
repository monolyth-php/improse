<?php

namespace Improse;

class Json extends View
{
    public function __invoke(array $viewdata = [])
    {
        @header('Content-type: application/json', true, 200);
        return json_encode($viewdata, JSON_NUMERIC_CHECK);
    }
}

