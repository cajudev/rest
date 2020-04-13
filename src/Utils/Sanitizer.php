<?php

namespace Cajudev\Rest\Utils;

use Cajudev\Rest\Utils\Parser\Parser;
use Cajudev\Rest\Exceptions\MissingConfigurationException;

class Sanitizer
{
    public static function boolean($boolean): bool {
        return filter_var($boolean, FILTER_VALIDATE_BOOLEAN);
    }
}
