# Templates
Improse is _not_ a templating system; luckily, it was designed to be easy to
hook into one.

## HTML views
Improse ships with an extremely basic `Html` view class. Extend this base class
and set your own protected `$template` property to point to the HTML file you
want to render.

> The HTML file is included verbatim, with `$viewdata` extracted as local
> variables.

### Example
```php
<?php

use Improse\Html;

class SomeView extends Html
{
    protected $template = '/path/to/foo.php';

    public function __invoke()
    {
        return parent::__invoke(['place' => 'Jupiter']);
    }
}

```

```php
// /path/to/foo.php
<h1>Hello, <?=$place?>!</h1>

```

This is fine for very simple projects.

## Json views
Also included is a simple `Json` view class. Basically this `json_encode`s the
`$viewdata` supplied on invocation.

### Example
```php
<?php

use Improse\Json;

$json = new Json;
echo $json(['place' => 'Jupiter']);
// Output: {"place":"Jupiter"}

```

## Third-party templating systems
We made the design choice not to force or include any third-party templating
system. However, hooking one up is trivial. Here are some examples for common
systems (note: make sure you install the system first!).

### Twig example
```php
<?php

use Improse\View;

class TwigView extends View
{
    protected $template;

    public function __construct()
    {
        require_once 'path/to/twig/lib/Twig/Autoloader.php';
        Twig_Autoloader::register();

        $loader = new Twig_Loader_Filesystem('/path/to/templates');
        $twig = new Twig_Environment($loader, ['cache' => '/path/to/cache']);
        $this->twig = $twig;
    }

    public function __invoke(array $viewdata = [])
    {
        return $this->twig->render($this->template, $viewdata);
    }
}

```

### Smarty example
```php
<?php

use Improse\View;

class SmartyView extends View
{
    protected $template;

    public function __construct()
    {
        require_once 'path/to/Smarty.class.php';
        $this->smarty = new Smarty;
    }

    public function __invoke(array $viewdata = [])
    {
        foreach ($viewdata as $key => $value) {
            $this->smarty->assign($key, $value);
        }
        return $this->smarty->display($this->template);
    }
}

```

### Mustache example
```php
<?php

use Improse\View;

class MustacheView extends View
{
    protected $template;

    public function __construct()
    {
        require_once 'path/to/Mustache_Engine';
        $this->mustache = new Mustace_Engine;
    }

    public function __invoke(array $viewdata = [])
    {
        return $this->mustache->render(
            file_get_contents($this->template),
            $viewdata
        );
    }
}

```

## Using templates
As you can see from the examples, virtually any templating system can be
integrated easily. Just write your own `__invoke` implementation that
delegates output to the templating system.

In your own views, extend the "templating view" you want to use:
```php
<?php

class MyPageView extends TwigView
{
    protected $template = 'path/to/mypage.html.twig';

    public function __invoke()
    {
        return parent::__invoke(['title' => 'Hey, this is my page!']);
    }
}

```

That's it, really. Note that you can combine and match different systems in
sub-views, as long as `__toString` gets called correctly (not all templating
systems might do this when encountering a PHP object - if that is the case,
just make sure you do it manually before delegating to `__invoke`).

