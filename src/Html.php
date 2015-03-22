<?php

namespace Improse;

class Html extends View
{
    protected $template;
    private $viewdata = [];

    public function __construct(array $data = [])
    {
        $this->viewdata = $data;
    }

    public function __invoke(array $__viewdata = [])
    {
        $__viewdata += $this->viewdata;
        return call_user_func(function() use ($__viewdata) {
            extract($__viewdata);
            unset($__viewdata);
            ob_start();
            require $this->template;
            return ob_get_clean();
        });
    }

    protected function headers()
    {
        static $called = false;
        if (!$called) {
            @header("Content-type: text/html; charset=utf-8");
            $called = true;
        }
    }
}
