<?php

namespace Improse;

use DomainException;
use ReflectionClass;
use ReflectionProperty;
use Exception;
use Closure;

class View
{
    protected $template;
    public static $engine;

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
        self::$engine = function (array $__variables) {
            extract($__variables);
            unset($__variables);
            ob_start();
            require $this->template;
            return ob_get_clean();
        };
    }

    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            // __toString cannot throw an exception, so we need to handle those
            // manually to prevent PHP from barfing:
        }
    }

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
}

