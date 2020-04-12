<?php

namespace Cajudev\Rest\Factories;

class EntityFactory implements ClassFactory
{
    public static function make(string $entity, $params = [])
    {
        $Entity = ucfirst($entity);
        $class = "App\\Entity\\$Entity";
        return new $class($params);
    }
}
