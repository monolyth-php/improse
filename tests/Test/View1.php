<?php

namespace Test;

use Improse\View;

class View1 extends View
{
    public function __invoke()
    {
        return 'Hello world!';
    }
}

