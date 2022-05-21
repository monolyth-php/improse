<?php

namespace Monolyth\Improse;

use DomainException;
use ReflectionClass;
use ReflectionProperty;
use Exception;
use Closure;

class View
{
    /**
     * The template to render for this view. It should be "resolvable" by a call
     * to `include $this->template`, so either pass the full path or setup your
     * include paths/templating engine accordingly.
     *
     * @var string
     */
    protected ?string $template;
    
    /**
     * True if any view triggered an error. Accessible via static `whoops`
     * method. Use to decide e.g. if you want to terminate your script.
     *
     * @var bool
     */
    private static bool $didWhoops = false;

    /**
     * Callable to handle errors.
     *
     * @var callable
     */
    private static $errorHandler;

    /**
     * Constructor. Optionally specify the template file. If omitted or null,
     * the $template property (or the class's parent's) is used.
     *
     * @param string|null $template Resolvable path to template file. Default
     *  resolve is simply `include`, other template engines might let you
     *  define a base path (e.g. Twig).
     * @throws DomainException if a template wasn't defined either as a property
     *  or during construction.
     */
    public function __construct(string $template = null)
    {
        if (isset($template)) {
            $this->template = $template;
        }
        if (!isset($this->template)) {
            throw new DomainException(
                "Every view must define its \$template file."
            );
        }
        if (!isset(self::$errorHandler)) {
            self::setErrorHandler(function (Throwable $e) : string {
                return 'Oopsie, we did a boo-boo :-(';
            });
        }
    }

    /**
     * Convert the view to a rendered string.
     *
     * @return string A string of HTML, e.g. to be `echo`'d.
     */
    public function __toString() : string
    {
        // __toString cannot throw an exception, so we need to handle those
        // manually to prevent PHP from barfing:
        try {
            return $this->render();
        } catch (Exception $e) {
            self::$didWhoops = true;
            return call_user_func(self::$errorHandler, $e);
        }
    }

    /**
     * Render the template as a string. Use this instead of `__toString` to
     * allow errors to be thrown instead of letting the errorHandler handle
     * them.
     *
     * @return string A string of HTML, e.g. to be `echo`'d.
     * @throws Throwable Whatever error rendering the template might trigger.
     */
    public function render() : string
    {
        extract($this->getVariables());
        unset($__variables);
        ob_start();
        require $this->template;
        return ob_get_clean();
    }

    /**
     * Set your preferred error handler for this project.
     *
     * @param callable $errorHandler Receives the thrown exception as its only
     *  argument.
     * @return void
     */
    public static function setErrorHandler(callable $errorHandler) : void
    {
        self::$errorHandler = $errorHandler;
    }

    /**
     * Internal method to get all template variables, i.e. the public properties
     * on the view class.
     *
     * @return array A hash of key/value pairs.
     */
    protected function getVariables() : array
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
                if (is_object($value)) {
                    if (method_exists($value, 'jsonSerialize')) {
                        $value = $value->jsonSerialize();
                    } elseif (method_exists($value, 'getArrayCopy')) {
                        $value = $value->getArrayCopy();
                    }
                }
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
    public static function whoops() : bool
    {
        return self::$didWhoops;
    }
}

