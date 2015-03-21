# Improse
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
3. There is now tight coupling between SomeController::indexAction and
   SomeModel, which would only be relevant if the action changes something
   (normally, a `POST` handler).

Improse is a simple view layer correcting these errors.

* Homepage: http://monomelodies.github.io/improse/
* Full documentation: http://improse.readthedocs.org/en/latest/

## Installation

## Composer (recommended)

Add "monomelodies/improse" to your `composer.json` requirements:

    {
        "require": {
            "monomelodies/improse": ">=0.4"
        }
    }

...and run `$ composer update` from your project's root.

## Manual installation
1. Get the code;
  1. Clone the repository, e.g. from GitHub;
  2. Download the ZIP (e.g. from Github) and extract.
2. Make your project recognize Improse:
  1. Register `/path/to/improse/src` for the namespace `Improse\\` in your
     PSR-4 autoloader (recommended);
  2. Alternatively, manually `include` the files you need.

## Basic usage
For examples and full explanation, see the documentation.

### Basic views
An Improse view is simply an invokable class:

    <?php

    use Improse;

    class View extends Improse\View
    {
        public function __invoke()
        {
            return '<h1>Hello world!</h1>';
        }
    }

Then, wherever you need it rendered, simply call it:

    <body>
        <!-- assuming $view is an object of the above View class: -->
        <?=$view()?>
    </body>

Improse views support `__toString` which effectively invokes the view. The
result keeps getting invoked until it is no longer invokable, allowing you to
"chain" views.

    <?=$view?>

### Adding templates
Echoing tons of HTML in the invoke method is of course impractical. What most
MVC frameworks erronously call "the view" is actually a _template_ (usually
HTML, but could be anything a browser groks).

The easiest way of using a template is having your `__invoke` method return an
instance of `Improse\Render\Html` (or one of the other supplied classes
depending on what your URL is supposed to render). All `Render` classes take two
constructor arguments: the path to the template (must be in your `include_path`)
and an array of data for the template to use.

    <?php

    use Improse;
    use Improse\Render\Html;

    class View extends Improse\View
    {
        public function __invoke()
        {
            return new Html('/path/to/template.php');
        }
    }

All `Render` classes are callable in their own right.

### Simplifying things

Obviously, views not requiring any additional data seem rather superfluous.
In fact, that's exactly the idea! If your page is _that_ static, you shouldn't
need a View at all (well, except for headers maybe, but a controller can set
those too).

Using the above example classes, the following three resolves for whatever
router you choose to use yield identical results:

    $page = new View;
    // or...
    $page = new Html('/path/to/template.php');
    // or...
    $page = function() {
        header("Content-type: text/html");
        include '/path/to/template.php';
    };
    // Output:
    echo $page();

As you can see, you can mix and match anything, as long as your rendering code
is clear on whether to expect a callable or a string. No need whatsoever to use
Improse views throughout your project!

### Using an external templating engine

Let's say you like using Smarty. It's simple enough to integrate:

    <?php

    use Improse;

    class View extends Improse\View
    {
        public function __invoke()
        {
            $smarty = new Smarty;
            // ...Additional Smarty config...
            return $smarty->fetch('page.tpl');
        }
    }

Similar setups can be used for other engines, e.g. Twig.

## Handling data
Since the whole idea of having a View object is to let it take care of its own
data, let's show an example of that, too:

    <?php

    use Improse;
    use Improse\Render\Html;

    class View extends Improse\View
    {
        public function __invoke()
        {
            // Obviously, in a real world example you'd be better off storing
            // this in some central config and dependency injecting it...
            $db = new PDO('dsn', 'user', 'pass');
            $stmt = $db->prepare('SELECT * FROM foo WHERE bar = ?');
            $stmt->execute(['value-for-bar']);
            return new Html(
                '/path/to/template.php',
                ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC)]
            );
        }
    }

## Using views in views

Websites rarely use a unique template for every page, but prefer to use a
"template-template" with stuff common to every page on the site. You could
easily do that by manually including something like `tpl/start.php` and
`tpl/end.php` in each view invocation, but naturally that would lead to
undesired code duplication.

In Improse, just inject your subtemplate into the master template on invocation:

    <?php

    use Improse\Render\Html;

    $view = new MyView;
    $template = new Html('/path/to/my/template.php', ['subview' => $view]);
    echo $template;

Then, in `template.php`, you can do this:

    <h1>My title!</h1>

    <?=$subview?>

Often, you will also want to define "snippets" of HTML for the rendering of
recurring partials. Improse has you covered:

    <?php use Improse\Render\Html ?>
    <ul>
    <?php foreach ($list as $item) { ?>
        <?=(new Html('/path/to/my/list/item.php', compact('item')))?>
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
            return new Html(
                '/path/to/my/page.php',
                [
                    'list' => $this->list,
                    'listView' => $this->listView,
                ]
            );
        }
    }

