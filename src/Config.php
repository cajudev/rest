<?php

namespace Cajudev\RestfulApi;

use Cajudev\RestfulApi\Util\File;
use Cajudev\RestfulApi\Util\Parser\JsonParser;

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
