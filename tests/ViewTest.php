<?php

namespace Improse\Test;

use PHPUnit_Framework_TestCase;
use Improse\View;

class ViewTest extends PHPUnit_Framework_TestCase
{
    public function testHtmlView()
    {
        $view = new View(__DIR__.'/_files/html.html');
        $view->foo = 'bar';
        $this->assertEquals('<h1>Hello world!</h1>', trim($view));
    }

    public function testPhpView()
    {
        $view = new View(__DIR__.'/_files/php.php');
        $view->foo = 'bar';
        $this->assertEquals('<h1>bar</h1>', trim($view));
    }

    public function testExceptionTriggersWhoops()
    {
        $view = new View(__DIR__.'/_files/exception.php');
        $view->bar = 'foo';
        $this->assertTrue(strpos(trim($view), '<div class="Whoops container">') !== false);
        $this->assertTrue(View::whoops());
    }

    public function testSwallowErrors()
    {
        $view = new View(__DIR__.'/_files/exception.php');
        $view::$swallowError = 'This is an error';
        $view->bar = 'foo';
        $this->assertEquals('This is an error', trim($view));
        $this->assertTrue(View::whoops());
    }
}

