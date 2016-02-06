<?php

namespace Improse\Test;

use Improse\View;

/**
 * Testing various views
 */
class View
{
    /**
     * {0} returns simple HTML with nothing interpolated.
     */
    public function html(View &$view = null)
    {
        $view = new View(__DIR__.'/_files/html.html');
        $view->foo = 'bar';
        yield '__toString' => function () {
            yield 'trim' => '<h1>Hello world!</h1>';
        };
    }

    /**
     * {0} renders a view with PHP in it.
     */
    public function php(View &$view = null)
    {
        $view = new View(__DIR__.'/_files/php.php');
        $view->foo = 'bar';
        yield '__toString' => function () {
            yield 'trim' => '<h1>bar</h1>';
        };
    }

    /**
     * If {0} throws an exception, it is caught by Whoops. Also, {1} is true
     * when called statically.
     */
    public function whoops(View &$view = null, View &$static = null)
    {
        $view = new View(__DIR__.'/_files/exception.php');
        $view->bar = 'foo';
        yield '__toString' => function() {
            yield function ($result) {
                return strpos(trim($result), '<div class="Whoops container">') !== false;
            };
        };
        yield 'whoops' => function () {
            yield true;
        };
    }

    /**
     * If {0} throws an exception, we can also swallow it. {1} will still be
     * true in this case.
     */
    public function swallowErrors(View &$view = null, View &$static = null)
    {
        $view = new View(__DIR__.'/_files/exception.php');
        $view::$swallowError = 'This is an error';
        $view->bar = 'foo';
        yield '__toString' => function () {
            yield 'This is an error';
        };
        yield 'whoops' => function () {
            yield true;
        };
    }
}

