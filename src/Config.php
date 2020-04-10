<?php

namespace Cajudev\Rest;

use Cajudev\Rest\Utils\File;
use Cajudev\Rest\Utils\Parser\JsonParser;

class Config
{
    private static $instance;
    private $info;

    private function __construct()
    {
        $file = new File();
        $file->read('/config.json');
        $this->info = $file->parse(new JsonParser());
    }

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key)
    {
        return $this->info->get($key);
    }
}
