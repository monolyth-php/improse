<?php

namespace Improse;

use DomainException;
use ReflectionClass;
use ReflectionProperty;
use Exception;
use Closure;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class View
{
    /**
     * @var string
     * The template to render for this view. It should be "resolvable" by a call
     * to `include $this->template`, so either pass the full path or setup your
     * include paths/templating engine accordingly.
     */
    protected $template;

    /**
     * @var Closure
     * Closure handling the actual rendering. Defaults to including the
     */template.
    public static $engine;

    /**
     * @var false|string
     * If false (the default) shows a Whoops error page on error. If set to a
     * string, displays that message instead. Extending views may define custom
     * settings for this.
     */
    public static $swallowError = false;
    
    /**
     * @var boolean
     * True if any view triggered an error. Accessible via static `whoops`
     * method. Use to decide e.g. if you want to terminate your script.
     */
    private static $didWhoops = false;

    /**
     * Constructor. Optionally specify the template file. If omitted or null,
     * the $template property (or the class's parent's) is used.
     *
     * @param string $template Resolvable path to template file. Default
     *  resolve is simply `include`, other template engines might let you
     *  define a base path (e.g. Twig).
     */
    public function __construct($template = null)
    {
        if (isset($template)) {
            $this->template = $template;
        }
        if (!isset($this->template)) {
            throw new DomainException(
                "Every view must define its \$template file."
            );
        }
        if (!isset(static::$engine)) {
            static::$engine = function (array $__variables) {
                extract($__variables);
                unset($__variables);
                ob_start();
                require $this->template;
                return ob_get_clean();
            };
        }
    }

    /**
     * Convert the view to a rendered string.
     *
     * @return string A string of HTML, e.g. to be `echo`'d.
     */
    public function __toString()
    {
        static $run, $handler;
        if (!isset($run, $handler)) {
            $run = new Run;
            $handler = new PrettyPageHandler;
            $handler->handleUnconditionally(true);
            $run->pushHandler($handler);
            $run->allowQuit(false);
            $run->writeToOutput(false);
            $run->register();
        }
        // __toString cannot throw an exception, so we need to handle those
        // manually to prevent PHP from barfing:
        try {
            return $this->render();
        } catch (Exception $e) {
            self::$didWhoops = true;
            if (!static::$swallowError) {
                return $run->handleException($e);
            } else {
                return static::$swallowError;
            }
        }
    }

    /**
     * Render the template as a string. Use this instead of `__toString` to
     * allow errors to be thrown instead of letting Whoops handle them.
     *
     * @return string A string of HTML, e.g. to be `echo`'d.
     */
    public function render()
    {
        if (!(static::$engine instanceof Closure)) {
            throw new DomainException(
                "\$engine must be an instance of Closure. "
               ."Wrap it in a lambda if you need some other callable instead."
            );
        }
        return call_user_func(
            static::$engine->bindTo($this, $this),
            $this->getVariables()
        );
    }

    /**
     * Internal method to get all template variables, i.e. the public properties
     * on the view class.
     *
     * @return array A hash of key/value pairs.
     */
    protected function getVariables()
    {
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties(
            ReflectionProperty::IS_PROTECTED |
            ReflectionProperty::IS_PRIVATE |
            ReflectionProperty::IS_STATIC
        ) as $property) {
            $ignore[] = $property->name;
        }
        $values = [];
        foreach ($this as $prop => $value) {
            if (!in_array($prop, $ignore)) {
                $values[$prop] = $value;
            }
        }
        return $values;
    }

    /**
     * Check if any view triggered an error, e.g. for premature script
     * termination.
     *
     * @return boolean True if there was at least one error, false if everything
     *  was hunky dory.
     */
    public static function whoops()
    {
        return self::$didWhoops;
    }
}

