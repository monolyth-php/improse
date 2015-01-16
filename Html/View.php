<?php

namespace improse\Html;
use improse;

class View extends improse\View
{
    public function __invoke()
    {
        header("Content-type: text/html; charset=utf-8");
        parent::__invoke();
    }
}

