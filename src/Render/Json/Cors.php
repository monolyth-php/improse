<?php

namespace Improse\Render\Json;

use Improse\Render\Json;

class Cors extends Json
{
    public function __construct(
        $origin = null,
        array $allowed = [],
        $fallback = null
    ) {
        parent::__construct();
        if (isset($origin)
            && in_array($origin, $allowed)
        ) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: $fallback");
        }
    }
}

