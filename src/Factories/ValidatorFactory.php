<?php

namespace Cajudev\Rest\Factories;

class ValidatorFactory implements ClassFactory
{
    public static function make(string $Validator, $params = [])
    {
        $class = "App\\Validator\\$Validator";
        return new $class($params);
    }
}
