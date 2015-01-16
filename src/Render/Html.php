<?php

namespace Improse\Render;

class Html
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function __invoke(array $datamap)
    {
        foreach ($datamap as $__var => $__value) {
            $$__var = $__value;
        }
        $this->headers();
        require $this->file;
    }

    protected function headers()
    {
        static $called = false;
        if (!$called) {
            header("Content-type: text/html; charset=utf-8");
            $called = true;
        }
    }
}

