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

    public abstract function __invoke();
}

