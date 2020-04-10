<?php

namespace Cajudev\Rest\Interfaces;

interface ClassFactory
{
    public static function make(string $name, $params = []);
}
