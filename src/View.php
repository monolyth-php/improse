<?php

namespace Improse;

abstract class View
{
    protected $viewdata = [];

    public function __construct(array $data = [])
    {
        $this->viewdata = $data;
    }

    public function __toString()
    {
        $that = $this;
        while (is_callable($that)) {
            $that = $that();
        }
        return $that;
    }

    public function export()
    {
        return $this->viewdata;
    }

    public abstract function __invoke();
}

