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
                foreach ($return as $key => $value) {
                    if (array_key_exists($key, self::$globalViewdata)
                        && is_null(self::$globalViewdata[$key])
                    ) {
                        unset(self::$globalViewdata[$key]);
                    }
                }
                self::$globalViewdata = array_merge_recursive(
                    self::$globalViewdata,
                    $return
                );
            }
            return ob_get_clean();
        });
    }
}

