# Improse
PHP5 View and templating front for MVC projects

Surprisingly, most MVC frameworks out there get Controllers and Views utterly
wrong. If you've ever worked with one, you'll recognize the following pattern:

```php
<?php

class SomeController extends BaseController
{
    public function actionIndex()
    {
        $this->data = new SomeModel;
        $this->render('path/to/template.php', ['data' => $this->data]);
    }
}
```

This is wrong for a number of reasons, all stemming from formal MVC theory:

1. The View (which in the above case is actually a template, which is something
   different) is responsible for its own data. Here it is not; the Controller is
   instantiating a model and passing it on to the template/view.
2. The template is acting as the View, which is wrong (they're separate
   concepts).
3. There is now tight coupling between `SomeController::indexAction` and
   `SomeModel`, which would only be relevant if the action changes something
   (normally, a `POST` handler).

Improse is a simple view layer correcting these errors.

* [Homepage](http://improse.monomelodies.nl/)
* [Full documentation](http://improse.monomelodies.nl/docs/)

## Installation

### Composer (recommended)
```bash
$ cd /path/to/project
$ composer require monomelodies/improse
```

### Manual
1. Download or clone the repository;
2. Add `/path/to/improse/src` in your PSR-4 autoloader for the namespace
   `Improse`.

## Basic usage
```php
<?php

use Improse\View;
$view = new View('/path/to/template/file');
echo $view->render();
```

The base view defines a `render` method which renders the requested file. So
you can also pass the rendered view to some other handler (e.g. an emitter):

```php
<?php

return emit($view->render());
```

Views also have a `__toString` method which simply returns the result of
`render`, so the following two are actually equivalent:

```php
<?php

echo $view->render();
echo $view;
```

> The main difference is that since `__toString` cannot throw exceptions in PHP,
> it catches them and uses `filp\whoops` to display them instead. `render` on
> the other hand would just `throw` it.

## Defining view data
All _public_ members of the view are considered "view data" by the `render`
method:

```php
<?php

$view->foo = 'bar';
echo $view; // The template now has $foo with value "bar"
```

In the real world, your views will need to collect data to render. Hence you'll
mostly use the `View` class as a base to extend off of:

```php
<?php

class MyView extends Improse\View
{
    // Either define the template on the class...
    protected $template = '/path/to/template';

    public function __construct(PDO $db)
    {
        // ...or pass it to the parent constructor.
        parent::__construct('/path/to/template');
        $stmt = $db->prepare('SELECT foo FROM bar');
        $stmt->execute();
        $this->foo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
```

## Making templates useful
The basic behaviour for Improse views is to `include` the template file. Thus,
you can pass HTML (or other static formats) to it, _or_ a PHP file:

```php
<?php

$view = new View('/path/to/my/file.php');
$view->foo = 'bar';
echo $view;
```

```php
<html>
    <body><?=$foo?></body>
</html>
```

## Nesting views
Views can also contain other views:

```php
<?php

$template = new View('/path/to/main/template');
$view = new View('/path/to/some/page');
$template->page = $view;
echo $view;
```

The template can now simply `echo $page` somewhere.

> Most templating engines (see below) also support this, but it could come in
> _very_ handy when you're mixing multiple templating systems, e.g. `Twig`,
> `Blade` and `Moustache` in different sections of your application. Improse is
> "templating-system-neutral" in that respect.

## Tying templates to views
In any but the most trivial application, views will contain logic so you'll find
yourself extending the base view for custom per-page or per-section views:

```php
<?php

class MyView extends Improse\View
{
    // Define the template on the class...
    protected $template = '/path/to/template';

    public function __construct($template = null)
    {
        parent::__construct($template);
        // [...snip all logic for MyView...]
    }
}
```

Note that you can still override the template to use on a per-case basis since
we're calling the parent `__construct`or with an argument:

```php
<?php

$view = new MyView('/some/custom/template');
```

## Just-in-time logic
You can also choose to place all your logic in an overridden `render` method.
Just forward to `parent::render()` and return its result when you're done:

```php
<?php

class MyView extends Improse\View
{
    protected $template = '/path/to/template';

    public function render()
    {
        // [...snip all logic for MyView...]
        return parent::render();
    }
}
```

This strategy allows you to define all views up front, but only make them do
heavy lifting (e.g. retrieving data from a RMDBS) when they actually get
rendered (based on your application's logic).

## Templating engines
Even though PHP is itself a templating engine, many people (including us...)
prefer to use a separate templating engine, e.g.
[Twig](http://twig.sensiolabs.org) or [Smarty](http://www.smarty.net/).

Integrating a templating engine is a matter of _overriding_ the `render` method
in an extending class, and let it use its own logic. An example using Twig could
look like this:

```php
<?php

class TwigView extends Improse\View
{
    // ...[snip custom logic]...

    public function render()
    {
        $loader = new Twig_Loader_Filesystem('/path/to/templates');
        $twig = new Twig_Environment($loader, [
            'cache' => '/path/to/cache/dir',
            'auto_reload' => true, // or false
            'debug' => true, // or false
        ]);
        return $this->twig->render($this->template, $this->getVariables());
    }
}
```

> The default `render` implementation does nothing else, so your override only
> needs to handle the actual outputting (though of course you could let it do
> more, like logging or such).

## Handling errors
Improse uses `filp\whoops` to pretty-print errors caused by exceptions in its
`__toString` calls (since they cannot throw exceptions in PHP).

Since a "whoops" may be triggered in any sub-view, if you're really bothered
about your error HTML being valid (since it might contain duplicate `<head>`
tags for instance if they were already outputted), you should handle this in
your application logic (e.g. using output buffering). Improse views offer a
static `whoops` method which returns `true` if any error occured:

```php
<?php

if (Improse\View::whoops()) {
    // error...
} else {
    // ok, display page
}
```

Of course, since you'll want to fix the error anyway you might as well dump it
to screen anyway. But you could also be a bit more friendly depending on whether
your app is in development or in production mode. Views also have a static
`$swallowErrors` property. It defaults to false, but set it to any non-false
value and a view with an error will render that instead (so ideally you'd put it
to a string message like `"Error! We're flogging the programmer!"`).

## This is all so basic...
So you've read the above and looked at the Improse source code (which admittedly
is extremely small). Maybe you find yourself wondering why you'd use Improse _at
all_; it's only a few lines of code, after all.

But, it saves you some boilerplate code, and by extending the base view the
template, variable en rendering logic are already in place. It also forces you
to implement views in the _correct_ MVC way :)

