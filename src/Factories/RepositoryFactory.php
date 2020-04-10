<?php

namespace Cajudev\Rest\Factories;

use Cajudev\Rest\EntityManager;
use Cajudev\Rest\Interfaces\ClassFactory;

class RepositoryFactory implements ClassFactory
{
    public static function make(string $Repository, $params = [])
    {
        $class = "App\\Entity\\$Repository";
        return EntityManager::getInstance()->getRepository($class);
    }
}
