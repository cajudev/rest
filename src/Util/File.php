<?php

namespace Cajudev\RestfulApi\Util;

use Cajudev\RestfulApi\Util\Parser\Parser;
use Cajudev\RestfulApi\Exception\MissingConfigurationException;

class File
{
    private $content;

    public function read(string $path)
    {
        $filepath = __ROOT__ . $path;
        if (!($this->content = @file_get_contents($filepath))) {
            throw new MissingConfigurationException("Arquivo [$filepath] não encontrado");
        }
    }

    public function parse(Parser $parser)
    {
        return $parser->parse($this->content);
    }

    public function raw()
    {
        return $this->content;
    }
}
