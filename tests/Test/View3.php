<?php

namespace Test;

use Improse\Html;

class View3 extends Html
{
    protected $template = '_files/hellowut.php';

    public function __invoke()
    {
        return parent::__invoke(['wut' => 'Mars']);
    }
}

