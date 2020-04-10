<?php

namespace Cajudev\Rest\Factories;

class EntityFactory implements ClassFactory
{
    public static function make(string $Entity, $params = [])
    {
        $class = "App\\Entity\\$Entity";
        return new $class($params);
    }
}
