<?php

namespace Cajudev\Rest\Factories;

class ValidatorFactory implements ClassFactory
{
    public static function make(string $validator, $params = [])
    {   
        $Validator = ucfirst($validator);
        $class = "App\\Validator\\$Validator";
        return new $class($params);
    }
}
