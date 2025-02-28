<?php

namespace Monolyth\Improse\Test;

use Monolyth\Improse\View;

/**
 * Testing various views
 */
class Test
{
    /**
     * A bare view returns simple HTML with nothing interpolated {?}.
     */
    public function html(?View &$view = null)
    {
        $view = new View(__DIR__.'/_files/html.html');
        $view->foo = 'bar';
        ob_start();
        echo $view;
        yield assert(trim(ob_get_clean()) == '<h1>Hello world!</h1>');
    }

    /**
     * A PHP file renders a view with PHP in it {?}.
     */
    public function php(?View &$view = null)
    {
        $view = new View(__DIR__.'/_files/php.php');
        $view->foo = 'bar';
        ob_start();
        echo $view;
        yield assert(trim(ob_get_clean()) == '<h1>bar</h1>');
    }

    /**
     * If a view throws an exception, it is caught by Whoops {?}. Also, `whoops`
     * now returns true when called statically {?}.
     */
    public function whoops(?View &$view = null)
    {
        $view = new View(__DIR__.'/_files/exception.php');
        $view->bar = 'foo';
        ob_start();
        echo $view;
        yield assert(strpos(ob_get_clean(), '<div class="Whoops container">') !== false);
        yield assert(View::whoops());
    }

    /**
     * If a view throws an exception, we can also swallow it {?}. `whoops` will
     * still return true in this case {?}.
     */
    public function swallowErrors(?View &$view = null)
    {
        $view = new View(__DIR__.'/_files/exception.php');
        $view::$swallowError = 'This is an error';
        $view->bar = 'foo';
        yield assert("$view" == 'This is an error');
        yield assert(View::whoops());
    }
}

