<?php

namespace Cajudev\Rest\Factories;

use Cajudev\Rest\Interfaces\ClassFactory;

class ValidatorFactory implements ClassFactory
{
    public static function make(string $Validator, $params = [])
    {
        $class = "App\\Validator\\$Validator";
        return new $class($params);
    }
}
