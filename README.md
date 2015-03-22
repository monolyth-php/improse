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

### Composer (recommended)

Add "monomelodies/improse" to your `composer.json` requirements:

    {
        "require": {
            "monomelodies/improse": ">=0.4"
        }
    }

...and run `$ composer update` from your project's root.

### Manual installation
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
An Improse view is simply an invokable class. The base class also provides a
`__toString` method that invokes-till-it-can-invoke-no-more:

    <?php

    use Improse;

    class View extends Improse\View
    {
        public function __invoke()
        {
            return '<h1>Hello world!</h1>';
        }
    }

Then, wherever you need it rendered, simply `__toString` it:

    <body>
        <!-- assuming $view is an object of the above View class: -->
        <?=$view?>
    </body>

### Adding templates
Echoing tons of HTML in the invoke method is of course impractical. What most
MVC frameworks erronously call "the view" is actually a _template_ (usually
HTML, but could be anything a browser groks).

Instead of extending the base `View` class (which in practice you'll amost never
do anyway), extend one of the `Improse\View\*` classes.

    <?php

    use Improse\Html;

    class View extends Html
    {
        protected $template = '/path/to/template.php';
    }

### Simplifying things
Obviously, views not requiring any additional data seem rather superfluous.
In fact, that's exactly the idea! If your page is _that_ static, you shouldn't
need a View at all (well, except for headers maybe, but a controller can set
those too).

Using the above example classes, the following three resolves for whatever
router you choose to use yield identical results:

    $page = new View;
    // or (assuming template.php contains that string of HTML)...
    $page = '<h1>Hello world!</h1>';
    // or...
    $page = call_user_func(function() {
        // Using a lambda here is slightly over the top, but it's there to
        // make the point that the view could be anything.
        ob_start();
        include '/path/to/template.php';
        return ob_get_clean();
    });
    // Output:
    echo $page;

As you can see, you can mix and match anything, as long as your rendering code
is clear on whether to expect a callable or a string. No need whatsoever to use
Improse views throughout your project!

### Using an external templating engine

Let's say you like using Smarty. It's simple enough to integrate:

    <?php

    use Improse;

    class View extends Improse\View
    {
        public function __invoke(array $__viewdata = [])
        {
            $smarty = new Smarty;

            // ...Additional Smarty config...
            // ...Add Smarty variables...

            // Finally, simply return the rendered string:
            return $smarty->fetch('page.tpl');
        }
    }

Similar setups can be used for other engines, e.g. Twig.

## Handling data
Since the whole idea of having a View object is to let it take care of its own
data, let's show an example of that, too:

    <?php

    use Improse\Html;

    class View extends Html
    {
        protected $template = '/path/to/template.php';

        public function __invoke()
        {
            // Obviously, in a real world example you'd be better off storing
            // this in some central config and dependency injecting it...
            $db = new PDO('dsn', 'user', 'pass');
            $stmt = $db->prepare('SELECT * FROM foo WHERE bar = ?');
            $stmt->execute(['value-for-bar']);
            return parent::__invoke([
                'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ]);
        }
    }

The idea is simple: whenever an Improse view is invoked, it optionally receives
a hash of key/value pairs with view data. Since the template file itself is
declared in a protected member, customizing and extending views is trivial.

### Global data
Some data should be global for reference in other, technically unrelated views.
The title of a page (rendered in a master template, set from a page view is a
good example, but something like the selected menu item also applies). I.e.,
data set in a child view but rendered in a master view. Improse views contain
a static property which is merged with your view-specific data for this:

    <?php

    class View
    {
        //...
        static::$globalViewdata['title'] = $mypage['title'];
    }

Templates can also return an array of key/value pairs which will be injected as
global view data. If you use this technique, make sure the child template is
`__toString()`'d before the parent template gets rendered, or obviously no data
will be available yet.

## Using views in views

### Master templates
Most of the time, a regular HTML page being rendered will use a master template
of sorts containg headers, menus and footers. To achieve this, simply define a
central view containing this template, decide on a variable name to use for your
injected page-specific view and have your controller/router inject it where
needed:

    <?php

    use Improse\Html;

    class MasterView extends Html
    {
        protected $template = '/path/to/template.php';
    }

...and in `template.php`:

    <html>
        <...other html...>
        <?=$content?>
    </html>

...and wherever you decide a page is being rendered:

    <?php

    $view = new View(['some' => 'data']);
    $template = new MasterView(['content' => $view]);

Depending on what other framework(s) you use, you should factor this away to a
central place. E.g., using `Reroute` for your routing, you could group all page
routes in an `html` group and inject the view into a master centrally. This way,
both the views as well as the templates are completely reusable as snippets (see
below), and the template injection is handled where it should (by the front
controller, which is after all where it is decided that the user is requesting
a full blown HTML page).

### Snippets
Often, you will also want to define "snippets" of HTML for the rendering of
recurring partials. Often you can do a simple `include` on the PHP file in
question, but if you need/like your data to be encapsulated, or need to use
some external templating engine, Improse has you covered:

    <?php use Improse\Html ?>
    <ul>
    <?php foreach ($list as $item) { ?>
        <?=(new Html('/path/to/my/list/item.php'))(compact('item'))?>
    <?php } ?>
    </ul>

A good strategy here is to load the item view in the page view and pass it in a
variable. This can define all 'global' variables that every instance of the
snippet needs. Then, when rendering, just pass the instance-specific variables
in when invoking.

