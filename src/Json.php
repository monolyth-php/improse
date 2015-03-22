<?php

namespace Improse;

class Json extends View
{
    public function __invoke(array $viewdata = [])
    {
        $this->viewdata = $viewdata + $this->viewdata;
        return json_encode($this->viewdata, JSON_NUMERIC_CHECK);
    }
}

