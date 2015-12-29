# Improse
PHP5 View and templating front for MVC projects

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

* [Homepage](http://improse.monomelodies.nl/)
* [Full documentation](http://improse.monomelodies.nl/docs/)

## Installation
[See the corresponding section in the documentation.](basic/installation.md)

## Basic usage
[For more detailed explanation and examples, see the documentation.](basic/views.md)

An Improse view is simply an invokable class. The base class also provides a
`__toString` method that invokes-till-it-can-invoke-no-more:

```php
<?php

use Improse;

class View extends Improse\View
{
    public function __invoke()
    {
        return '<h1>Hello world!</h1>';
    }
}
```

Then, wherever you need it rendered, simply `__toString` it:

```html
<body>
    <!-- assuming $view is an object of the above View class: -->
    <?=$view?>
</body>
```

## Adding templates
Echoing tons of HTML in the invoke method is of course impractical. What most
MVC frameworks erroneously call "the view" is actually a _template_ (usually
`HTML`, but could be anything a browser groks like `XML` or `Json`, or - for
completeness sake - some other output alltogether, like an `ODF` document).

Improse is _not_ a templating engine. Other projects exist for that, e.g.
[Twig](http://twig.sensiolabs.org) or [Smarty](http://www.smarty.net/).
[See the example on template integration in the docs.](basic/templates.md)

If you're comfortable with using PHP-as-a-templating-engine, Improse offers an
extermely basic `Html` view. This simply `include`s the template as defined on
the procted property `$template` in your view, exposing local variables as
passed to the `__invoke` method:

```php
<?php

use Improse\Html;

class View extends Html
{
    protected $template = '/path/to/template.php';

    public function __invoke()
    {
        return parent::__invoke(['place' => 'world']);
    }
```

```html
<h1>Hello <?=$place?>!</h1>
```

## Handling data
Nest views extending the data array passed to each `__invoke` parent call if
you need to "build on top" of a view. Keep it DRY!

An example where this could be useful is e.g. a blog where reading a post is a
dedicated page with a view, but showing comments is that same view, only with
an opened comment secion. The view `PostWithCommentsView` could then extend the
`PostView` and just add the comments.

> Of course this trivial problem could also be solved by passing a parameter
> to the View's constructor, e.g. `$showComments = false`, but you get the idea.
> In complex setups, extend views is super-duper handy.

## Simplifying things
Obviously, views not requiring any additional data seem rather superfluous.
In fact, that's exactly the idea! If your page is _that_ static, you shouldn't
need a View (or a Controller for that matter at all (well, except for headers
maybe, but a front controller can set those too).

We'd recommend sticking to the following workflow:

- The _front controller_ parsers the request and decides which piece of logic
  needs to kick in;
- For _static pages_, just output a template;
- For _read-only page with data_, setup the corresponding view and render it
  (or usually its template);
- For _dynamic pages handling user interaction_, first setup the corresponding
  controller and let it do its stuff. Next, setup the corresonding view and
  render it (or usually its template) depending on controller success.

## Snippets
"Snippets" are subtemplates that appear in recurring places (e.g. a sidebar
with "recent posts" on a blog that's shown on every page). Ideally, such a
snippet would use its own view taking care of its own data.

Improse views are designed to be "`__toString`able", so this is easy:

```php
<?php

use Improse\View;

class MyblogpageView extends View
{
    public function __construct()
    {
        parent::__construct();
        // ...other stuff needed...

        $this->recents = new RecentpostView;
    }

    // Example invocation:
    public function __invoke()
    {
        echo '<h1>my blog page!</h1>';
        // Sidebar:
        echo '<aside>';
        echo $this->recents;
        echo '</aside>';
}
```
...and the RecentpostView:
```
<?php

use Improse\View;

class RecentpostView extends View
{
    public function __construct()
    {
        // ...
        // Wherever you get them from:
        $this->posts = getRecentPosts();
    }

    public function __invoke()
    {
        echo '<ul>';
        foreach ($this->posts as $post) {
            echo '<li>'.$post['title'].'</li>';
        }
        echo '</ul>';
}
```
When using templates, simply rely on the `__toString` method being called and
let the subview load its own template. E.g., using Twig:
```html
<h1>my blog page!</h1>
<aside>
    {{ recents }}
</aside>
```

Using subviews it's also perfectly possible to mix and match templating systems,
since every view defines its own. This comes in handy when building reusable
modules: the parent project needn't care about the engine your module uses, so
there's no need to supply various templates in different formats for consumers.

