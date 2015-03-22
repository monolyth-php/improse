<?php

namespace Improse;

class Html extends View
{
    protected $template;

    public function __invoke(array $viewdata = [])
    {
        $this->viewdata = $viewdata + $this->viewdata + self::$globalViewdata;
        return call_user_func(function () {
            extract($this->viewdata);
            ob_start();
            $return = require $this->template;
            if ($return && is_array($return)) {
                self::$globalViewdata = $return + self::$globalViewdata;
            }
            return ob_get_clean();
        });
    }
}

