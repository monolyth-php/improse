# Views
In formal MVC theory, the View the component responsible for an application's
presentation towards the user. Note that this is _not_ the same as "a templating
system" - the view is simply the object gathering the required data.

> As a rule of thumb, a View object does `SELECT` queries. A controller is only
> needed when interaction is taking place, and it should delegate the required
> `INSERT`, `UPDATE` and `DELETE` actions to the relevant Model(s).
>
> If you only need `SELECT`s, no need to use a controller - just the view. If
> you don't need `SELECT`s either (e.g. extremely static pages), you're not
> even going to need the view!

Let's say we have a table called `cats` with the following schema:

```sql
CREATE TABLE cats (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(32) NOT NULL
) ENGINE='InnoDB' DEFAULT CHARSET='UTF8';
```

> This example is in MySQL, but should be trivial to adapt!

It will contain the name of all cats I own or have owned in the past, i.e.
`Frodo`, `Moby`, `Zsazsa` and `Haroun`. We want to display them in an
ordered list, so we'll need a view to handle the `SELECT`:

```php
<?php

use Improse\View;

class CatView extends View
{
    public function __invoke()
    {
        $db = new PDO(/* connection details */);
        echo '<ol>';
        foreach ($db->query("SELECT * FROM cats ORDER BY id") as $cat) {
            echo '<li>'.$cat['name'].'</li>';
        }
        echo '</ol>';
    }
}

```

Often, views will be more complex than this. For instance, as time passes the
collection of cats will grow (poor buggers don't live that long...) so we might
want to paginate. Typically, the page number will come from the URL and we pass
it to the view on construction:

```php
// "Front controller". In the real world you want to check $_GET parameters
// for validity of course.
$view = new CatView($_GET['page']);
```

```php
<?php

use Improse\View;

class CatView extends View
{
    public function __construct($page = 1)
    {
        $this->page = $page;
    }

    public function __invoke()
    {
        $db = new PDO(/* connection details */);
        echo '<ol>';
        $offset = ($this->page - 1) * 5;
        foreach ($db->query("SELECT * FROM cats
            ORDER BY id
            LIMIT 5
            OFFSET $offset"
        ) as $cat) {
            echo '<li>'.$cat['name'].'</li>';
        }
        echo '</ol>';
    }
}

```

Note that now have a constructor als well as an invocation method. A best
practice would actually be to move all queries to the constructor, and let the
invocation deal with outputting only:

```php
<?php

use Improse\View;

class CatView extends View
{
    public function __construct($page = 1)
    {
        $db = new PDO(/* connection details */);
        $offset = ($page - 1) * 5;
        $this->cats = $db->query("SELECT * FROM cats
            ORDER BY id
            LIMIT 5
            OFFSET $offset"
        );
    }

    public function __invoke()
    {
        echo '<ol>';
        foreach ($this->cats as $cat) {
            echo '<li>'.$cat['name'].'</li>';
        }
        echo '</ol>';
    }
}

```

> In a more complex real world system, this would ensure all queries are run
> before outputting starts. This allows you to do caching, proper error handling
> etc. before sending any data to the user.

