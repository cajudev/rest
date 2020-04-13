<?php

namespace Cajudev\Rest\Factories;

class ValidatorFactory implements ClassFactory
{
    public static function make(string $name, $params = []): object
    {
        $class = static::namespace($name);
        return EntityManager::getInstance()->getRepository($class);
    }

    public static function namespace(string $name): string {
        return sprintf('App\Validator\%s', ucfirst($name));
    }
}
