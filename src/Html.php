<?php

namespace Improse;

class Html extends View
{
    protected $template;

    public function __invoke(array $viewdata = [])
    {
        $this->viewdata = $viewdata + $this->viewdata + static::$globalViewdata;
        return call_user_func(function () {
            extract($this->viewdata);
            ob_start();
            $return = require $this->template;
            if ($return && is_array($return)) {
                foreach ($return as $key => $value) {
                    if (array_key_exists($key, static::$globalViewdata)
                        && is_null(static::$globalViewdata[$key])
                    ) {
                        unset(static::$globalViewdata[$key]);
                    }
                }
                static::$globalViewdata = array_merge_recursive(
                    static::$globalViewdata,
                    $return
                );
            }
            return ob_get_clean();
        });
    }
}

