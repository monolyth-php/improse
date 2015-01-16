# improse
PHP5 View and templating system for MVC projects

Surprisingly, most MVC frameworks out there get Controllers and Views utterly
wrong. If you've ever worked with one, you'll recognize the following pattern:

    <?php

    class SomeController extends BaseController
    {
        public function actionIndex()
        {
            $this->data = new SomeModel;
            $this->render('path/to/template.php', ['data' => $this->data]);
        }
    }

This is wrong for a number of reasons, all stemming from formal MVC theory:

1. The View (which in the above case is actually a template, which is something
   different) is responsible for its own data. Here it is not; the Controller is
   instantiating a model and passing it on to the template/view.
2. The template is acting as the View, which is wrong (they're separate
   concepts).
3. There is now tight coupling between SomeController and SomeModel.

Improse is an attempt to correct these errors.

Installation
------------

1. Clone the repository;
2. Make sure your autoloader (PSR-4) knows about `/path/to/improse/src`.

###Installation using Composer


1. Add a requirement to "monomelodies/improse" to your `composer.json`;
2. Add Reroute to the autoload:psr-4 hash:
    "autoload": {
        "psr-4": {
            "Improse": "src"
        }
    }
3. Run `composer install`

Usage
-----

###Basic views###

At its most basic, a View in Improse is an invokable class:

    <?php

    use Improse;

    class View extends Improse\View
    {
        public function __construct()
        {
            // ...Setup your data here...
        }

        public function __invoke()
        {
            // ...Render the view here...
        }
    }

In this extremely basic form, the call to `__invoke` would echo some stuff, e.g.
an HTML page. You can just call it wherever you want to display it:

    <!doctype html>
    <html>
        <head><title>Example template</title></head>
        <body>
            <h1>Example template</h1>
            <!-- Assuming $view is an instance of View: -->
            <?php $view() ?>
        </body>
    </html>

For convenience, Improse views support `__toString` which effectively invokes
the view:

    <?=$view?>

###Adding templates###

Echoing tons of HTML in the invoke method is of course impractical. The simplest
way of using one is simply including a HTML or PHP file in your invoke method:

    <?php

    use Improse;

    class View extends Improse\View
    {
        public function __invoke()
        {
            include '/path/to/page.php';
        }
    }

Obviously, views not requiring any additional data would be rather superfluous.
In fact, that's exactly the idea! If your page is _that_ static, you shouldn't
need a View at all (well, except for headers maybe).

###Using an external templating engine###

Let's say you like using Smarty. It's simple enough to integrate:

    <?php

    use Improse;

    class View extends Improse\View
    {
        public function __invoke()
        {
            $smarty = new Smarty;
            // ...Additional Smarty config...
            $marty->display('page.tpl');
        }
    }

###Using Improse PHP/HTML templates###

Improse templates are simple PHP files (PHP is of course itself a templating
engine at heart), but with some convenience methods:

    <?php

    use Improse;
    use Improse\Render\Html;

    class View extends Improse\View
    {
        public function __invoke()
        {
            $template = new Html('/path/to/file.php');
            $template($this);
        }
    }

The argument to the Php constructor is `include`d, so either use an absolute
path, or use something in your `include_path`.

###Using views in views###

Web sites rarely use a unique template for every page, but prefer to use a
"template-template" with stuff common to every page on the site. You could
easily do that by manually including something like `tpl/start.php` and
`tpl/end.php` in each view invocation, but naturally that would lead to
undesired code duplication.

In Improse, just inject your subtemplate into the master template on invocation:

    <?php

    use Improse\Render\Html;

    $view = new MyView;
    $template = new Html('/path/to/my/template.php');
    $template(['subview' => $view]);

Then, in `template.php`, you can do this:

    <h1>My title!</h1>

    <?=$subview?>

Often, you will also want to define "snippets" of HTML for the rendering of
recurring partials. Improse has you covered:

    <?php use Improse\Render\Html ?>
    <ul>
    <?php foreach ($list as $item) { ?>
        <?=(new Html('/path/to/my/list/item.php'))(compact('item'))?>
    <?php } ?>
    </ul>

Of course, you could also export prepared views from a View class:

    <?php

    use Improse\View;
    use Improse\Render\Html;

    class MyView extends View
    {
        public function __construct()
        {
            $this->listView = new Html('/path/to/my/list/item.php');
            $this->list = [1, 2, 3];
        }

        public function __invoke()
        {
            $page = new Html('/path/to/my/page.php');
            return $page([
                'list' => $this->list,
                'listView' => $this->listView,
            ]);
        }
    }

