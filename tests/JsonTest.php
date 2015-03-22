<?php

use Improse\Json;

set_include_path(dirname(__FILE__).PATH_SEPARATOR.get_include_path());

class JsonTest extends PHPUnit_Framework_TestCase
{
    public function testViewWithJson()
    {
        $view = new Json([
            'foo' => 'bar',
            'baz' => [1, 2],
            'barf' => ['fizz', 'bizz'],
        ]);
        $out = "$view";
        $this->assertEquals("<h1>Hello world!</h1>\n", $out);
    }
}

