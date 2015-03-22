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
            require $this->template;
            return ob_get_clean();
        });
    }
}

