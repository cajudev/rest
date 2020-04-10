<?php

namespace Cajudev\Rest\Factories;

use Cajudev\Rest\Interfaces\ClassFactory;

class EntityFactory implements ClassFactory
{
    public static function make(string $Entity, $params = [])
    {
        $class = "App\\Entity\\$Entity";
        return new $class($params);
    }
}
