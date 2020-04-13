<?php

namespace Cajudev\Rest\Factories;

interface ClassFactory
{
    public static function make(string $name, $params = []): object;
    public static function namespace(string $name): string;
}
