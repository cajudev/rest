<?php

namespace Cajudev\Rest\Utils;

use Cajudev\Rest\Utils\Parser\Parser;
use Cajudev\Rest\Exceptions\MissingConfigurationException;

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
