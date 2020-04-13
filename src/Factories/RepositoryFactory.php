<?php

namespace Cajudev\Rest\Factories;

use Cajudev\Rest\EntityManager;

class RepositoryFactory implements ClassFactory
{
    public static function make(string $name, $params = []): object
    {
        $class = static::namespace($name);
        return EntityManager::getInstance()->getRepository($class);
    }

    public static function namespace(string $name): string {
        return sprintf('App\Entity\%s', ucfirst($name));
    }
}
