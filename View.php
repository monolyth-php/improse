<?php

namespace improse;

abstract class View
{
    protected $_template;
    protected $_exports = [];

    public function __invoke()
    {
        if ($this->_exports) {
            foreach ($this->_exports as $__var) {
                $$__var = $this->$__var;
            }
        }
        if (isset($this->_template)) {
            include $this->_template;
        }
    }
}

