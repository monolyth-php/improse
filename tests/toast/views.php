<?php

use Monolyth\Improse;

class View extends Improse\View
{
    public string $foo;

    public string $bar;
}

/**
 * Testing various views
 */
return function () : Generator {
    /** A bare view returns simple HTML with nothing interpolated. */
    yield function () {
        $view = new View(__DIR__.'/../_files/html.html');
        $view->foo = 'bar';
        ob_start();
        echo $view;
        assert(trim(ob_get_clean()) == '<h1>Hello world!</h1>');
    };

    /** A PHP file renders a view with PHP in it. */
    yield function () {
        $view = new View(__DIR__.'/../_files/php.php');
        $view->foo = 'bar';
        ob_start();
        echo $view;
        assert(trim(ob_get_clean()) == '<h1>bar</h1>');
    };

    /** If a view throws an exception, it is caught by Whoops. */
    yield function () {
        $view = new View(__DIR__.'/_files/exception.php');
        $view->bar = 'foo';
        ob_start();
        echo $view;
        yield assert(strpos(ob_get_clean(), '<div class="Whoops container">') !== false);
        assert(View::whoops());
    };

    /** If a view throws an exception, we can also swallow it. */
    yield function () {
        $view = new View(__DIR__.'/_files/exception.php');
        $view::$swallowError = 'This is an error';
        $view->bar = 'foo';
        yield assert("$view" == 'This is an error');
        assert(View::whoops());
    };
};

