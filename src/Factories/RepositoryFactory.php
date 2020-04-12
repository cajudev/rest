<?php

namespace Cajudev\Rest\Factories;

use Cajudev\Rest\EntityManager;

class RepositoryFactory implements ClassFactory
{
    public static function make(string $repository, $params = [])
    {
        $Repository = ucfirst($repository);
        $class = "App\\Entity\\$Repository";
        return EntityManager::getInstance()->getRepository($class);
    }
}
