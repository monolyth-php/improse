<?php

namespace Improse;

class Html extends View
{
    protected $template;
    protected $viewdata;

    public function __invoke(array $viewdata = [])
    {
        if (!isset($this->template)) {
            $class = get_class($this);
            if ($class{0} == '\\') {
                $class = substr($class, 1);
            }
            $parts = explode('\\', $class);
            $last = array_pop($parts);
            if ($last == 'View') {
                $last = 'template';
            } else {
                $last = strtolower(preg_replace('@View$@', '', $last));
            }
            $parts[] = $last;
            $this->template = implode('/', $parts).'.php';
        }
        $this->viewdata = $viewdata;
        return call_user_func(function () {
            extract($this->viewdata);
            require $this->template;
            return '';
        });
    }
}

