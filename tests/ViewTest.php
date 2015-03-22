<?php

set_include_path(dirname(__FILE__).PATH_SEPARATOR.get_include_path());
require_once dirname(__FILE__).'/Test/View1.php';

class ViewTest extends PHPUnit_Framework_TestCase
{
    public function testView()
    {
        $view = new Test\View1;
        $out = "$view";
        $this->assertEquals('Hello world!', $out);
    }
}

