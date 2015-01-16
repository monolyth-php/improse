<?php

namespace Improse\Html;
use Improse;

class View extends Improse\View
{
    public function __invoke()
    {
        header("Content-type: text/html; charset=utf-8");
        parent::__invoke();
    }
}

