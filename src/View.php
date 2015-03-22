<?php

namespace Improse;

abstract class View
{
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

